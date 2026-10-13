<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
use SimpleMSPDoc\Core\{Database,HttpException};
final class Installer
{
 public function __construct(private string $root,private string $migrations){}
 public function install(array $databaseSettings,array $adminInput): void {
  if(is_file($this->root.'/data/config.php')||is_file($this->root.'/data/installed.php'))throw new HttpException(403,'Installation is locked.');
  if(in_array(false,Requirements::check(),true))throw new HttpException(422,'Server requirements are not satisfied.');
  $username=strtolower(trim((string)($adminInput['username']??'')));$name=trim((string)($adminInput['name']??''));$password=(string)($adminInput['password']??'');
  if(!preg_match('/^[a-z0-9@._+-]{3,190}$/',$username)||$name===''||strlen($name)>190||strlen($password)<12||strlen($password)>72)throw new HttpException(422,'Enter a valid username, name, and a password of 12–72 bytes.');
  if(!str_starts_with($databaseSettings['dsn']??'','mysql:'))throw new HttpException(422,'Only MySQL or MariaDB databases are supported.');
  if(!is_file($this->root.'/data/installing.php'))\SimpleMSPDoc\Core\ProtectedFile::write($this->root.'/data/installing.php','');$lock=fopen($this->root.'/data/installing.php','c');if(!$lock||!flock($lock,LOCK_EX|LOCK_NB))throw new HttpException(409,'Another installation is running.');
  try {
   if(is_file($this->root.'/data/config.php'))throw new HttpException(403,'Installation is locked.');
   $db=Database::connect($databaseSettings);$version=$db->one('SELECT VERSION() AS version')['version'];$minimum=str_contains($version,'MariaDB')?'10.11':'8.0';if(version_compare($version,$minimum,'<'))throw new HttpException(422,'Database version is unsupported.');
   if($db->all('SHOW TABLES')!==[])throw new HttpException(422,'Use an empty database. Legacy migration and in-place upgrades are not supported.');
   foreach(['data','data/uploads','data/cache'] as $d){$path=$this->root.'/'.$d;if(!is_dir($path)&&!mkdir($path,0700,true))throw new HttpException(422,'Private directories are not writable.');if(!is_writable($path))throw new HttpException(422,'Private directories are not writable.');}
   foreach(require $this->migrations.'/001_initial.php' as $sql)$db->run($sql);
   $db->run('INSERT INTO users(username,name,password_hash,role,audience) VALUES (?,?,?,?,?)',[$username,$name,password_hash($password,PASSWORD_DEFAULT),'administrator','staff']);
   $config=['database'=>$databaseSettings,'key'=>base64_encode(random_bytes(32)),'upload_limit'=>20*1024*1024,'version'=>'2.0.0-alpha.3'];
   $tmp=$this->root.'/data/config.tmp.php';file_put_contents($tmp,"<?php\nif (!defined('SIMPLEMSPDOC_BOOTSTRAP')) { http_response_code(404); exit; }\nreturn ".var_export($config,true).";\n",LOCK_EX);chmod($tmp,0600);if(!rename($tmp,$this->root.'/data/config.php'))throw new \RuntimeException('Cannot save configuration.');
   \SimpleMSPDoc\Core\ProtectedFile::write($this->root.'/data/installed.php',date(DATE_ATOM));chmod($this->root.'/data/installed.php',0600);
  }finally{flock($lock,LOCK_UN);fclose($lock);}
 }
}
