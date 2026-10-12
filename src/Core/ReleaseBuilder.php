<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class ReleaseBuilder
{
 public function __construct(private string $root){}
 public function build(string $version,string $output): void {
  if(!preg_match('/^\d+\.\d+\.\d+(?:-[a-zA-Z0-9.]+)?$/',$version))throw new \InvalidArgumentException('Invalid release version.');
  $stage=sys_get_temp_dir().'/simplemspdoc-build-'.bin2hex(random_bytes(8));mkdir($stage,0700,true);
  try {
   foreach(['src','public','templates','database','scripts','docs','LICENSE','README.md','composer.json','composer.lock'] as $name)$this->copy($this->root.'/'.$name,$stage.'/'.$name);
   foreach(['config','var'] as $d)mkdir($stage.'/'.$d,0700,true);
   file_put_contents($stage.'/.htaccess',"Require all denied\n");
   file_put_contents($stage.'/manifest.json',json_encode(['name'=>'SimpleMSPDoc','version'=>$version,'php_minimum'=>'8.3','mysql_minimum'=>'8.0','mariadb_minimum'=>'10.11','legacy_compatible'=>false],JSON_PRETTY_PRINT|JSON_THROW_ON_ERROR).PHP_EOL);
   $composer=getenv('COMPOSER_BINARY')?:'composer';$process=proc_open([$composer,'install','--no-dev','--prefer-dist','--no-interaction','--optimize-autoloader','--no-scripts','--working-dir='.$stage],[0=>['pipe','r'],1=>['file',$stage.'/build.log','w'],2=>['file',$stage.'/build.log','a']],$pipes);
   if(!is_resource($process)||proc_close($process)!==0)throw new \RuntimeException('Production dependency installation failed. '.(is_file($stage.'/build.log')?file_get_contents($stage.'/build.log'):''));unlink($stage.'/build.log');
   if(!is_dir(dirname($output)))mkdir(dirname($output),0700,true);$zip=new \ZipArchive();if($zip->open($output,\ZipArchive::CREATE|\ZipArchive::OVERWRITE)!==true)throw new \RuntimeException('Cannot create release ZIP.');$prefix='SimpleMSPDoc-'.$version.'/';
   $zip->addEmptyDir($prefix.'config');$zip->addEmptyDir($prefix.'var');$it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($stage,\FilesystemIterator::SKIP_DOTS));foreach($it as $file){$relative=substr($file->getPathname(),strlen($stage)+1);$zip->addFile($file->getPathname(),$prefix.$relative);$zip->setMtimeName($prefix.$relative,1704067200);}$zip->close();
  }finally{if(is_dir($stage)){$it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($stage,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $p)$p->isDir()?rmdir($p->getPathname()):unlink($p->getPathname());rmdir($stage);}}
 }
 private function copy(string $from,string $to): void {if(is_link($from))throw new \RuntimeException('Symlinks are not allowed in release sources.');if(is_dir($from)){mkdir($to,0700,true);foreach(scandir($from) as $item)if($item!=='.'&&$item!=='..'&&$item!=='router.php'&&$item!=='build-release.php')$this->copy($from.'/'.$item,$to.'/'.$item);}else copy($from,$to);}
}
