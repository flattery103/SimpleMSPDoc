<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class ReleaseBuilder
{
 public function __construct(private string $root){}
 public function build(string $version,string $output): void {
  if(!preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.]+)?$/',$version))throw new \InvalidArgumentException('Invalid release version.');$stage=sys_get_temp_dir().'/simplemspdoc-build-'.bin2hex(random_bytes(8));mkdir($stage.'/app',0700,true);
  try {
   foreach(['src','templates','database','composer.json','composer.lock'] as $name)$this->copy($this->root.'/'.$name,$stage.'/app/'.$name);
   foreach(['assets','LICENSE','README.md','index.php','.htaccess'] as $name)$this->copy($this->root.'/'.$name,$stage.'/'.$name);
   mkdir($stage.'/docs',0700,true);foreach(['installation.md','upgrading.md','release-notes.md'] as $name)$this->copy($this->root.'/docs/'.$name,$stage.'/docs/'.$name);
   mkdir($stage.'/data',0750,true);file_put_contents($stage.'/data/index.php',ProtectedFile::HEADER);file_put_contents($stage.'/data/.htaccess',"Require all denied\n");file_put_contents($stage.'/app/.htaccess',"Require all denied\n");
   file_put_contents($stage.'/manifest.json',json_encode(['name'=>'SimpleMSPDoc','version'=>$version,'php_minimum'=>'8.3','mysql_minimum'=>'8.0','mariadb_minimum'=>'10.11','legacy_compatible'=>false],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL);
   $composer=getenv('COMPOSER_BINARY')?:'composer';$process=proc_open([$composer,'install','--no-dev','--prefer-dist','--no-interaction','--optimize-autoloader','--no-scripts','--working-dir='.$stage.'/app'],[0=>['pipe','r'],1=>['file',$stage.'/build.log','w'],2=>['file',$stage.'/build.log','a']],$pipes);
   if(!is_resource($process)||proc_close($process)!==0)throw new \RuntimeException('Production dependency installation failed. '.(is_file($stage.'/build.log')?file_get_contents($stage.'/build.log'):''));unlink($stage.'/build.log');
   if(!is_dir(dirname($output)))mkdir(dirname($output),0700,true);$zip=new \ZipArchive();if($zip->open($output,\ZipArchive::CREATE|\ZipArchive::OVERWRITE)!==true)throw new \RuntimeException('Cannot create release ZIP.');$zip->addEmptyDir('data');$zip->setExternalAttributesName('data/',\ZipArchive::OPSYS_UNIX,040750<<16);
   $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($stage,\FilesystemIterator::SKIP_DOTS));foreach($it as $file){$relative=substr($file->getPathname(),strlen($stage)+1);$zip->addFile($file->getPathname(),$relative);$zip->setMtimeName($relative,1704067200);}$zip->close();
  }finally{if(is_dir($stage)){$it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($stage,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $p)$p->isDir()?rmdir($p->getPathname()):unlink($p->getPathname());rmdir($stage);}}
 }
 private function copy(string $from,string $to): void {if(is_link($from))throw new \RuntimeException('Symlinks are not allowed in release sources.');if(is_dir($from)){mkdir($to,0700,true);foreach(scandir($from) as $item)if($item!=='.'&&$item!=='..')$this->copy($from.'/'.$item,$to.'/'.$item);}else copy($from,$to);}
}
