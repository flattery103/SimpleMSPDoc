<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
use SimpleMSPDoc\Core\{Context,HttpException};
final class InstallController
{
 public function __construct(private Context $ctx){}
 public function handle(string $path='/install'): void {
  $access=new SetupAccess($this->ctx->privateRoot);
  if($path==='/install/setup-file'){
   if($_SERVER['REQUEST_METHOD']!=='POST')throw new HttpException(405,'Use the download button on the installer.');
   $_SESSION['setup_secret']=bin2hex(random_bytes(32));header('Content-Type: text/plain');header('Content-Disposition: attachment; filename="setup.key"');echo $access->fileContents($_SESSION['setup_secret']);return;
  }
  $ready=$access->verify((string)($_SESSION['setup_secret']??''));
  if($_SERVER['REQUEST_METHOD']==='POST'){
   if(!$ready)throw new HttpException(403,'Download the setup file and upload it into the data folder using your file manager or FTP, then refresh this page.');
   $host=(string)($_POST['database_host']??'localhost');$port=(int)($_POST['database_port']??3306);$name=(string)($_POST['database_name']??'');$dsn=(string)($_POST['dsn']??'');
   if($dsn===''){if(!preg_match('/^[a-zA-Z0-9_.:-]+$/',$host)||!preg_match('/^[a-zA-Z0-9_]+$/',$name)||$port<1||$port>65535)throw new HttpException(422,'Invalid database host, port or name.');$dsn="mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";}
   (new Installer($this->ctx->privateRoot,$this->ctx->codeRoot.'/database/migrations'))->install(['dsn'=>$dsn,'username'=>(string)($_POST['database_user']??''),'password'=>(string)($_POST['database_password']??'')],$_POST,$_SESSION['setup_secret']);unset($_SESSION['setup_secret']);$this->ctx->flash('Installation complete. Sign in with your administrator account.');$this->ctx->redirect('/login');
  }
  $this->ctx->render('install',['title'=>'Install SimpleMSPDoc','requirements'=>Requirements::check(),'ready'=>$ready]);
 }
}
