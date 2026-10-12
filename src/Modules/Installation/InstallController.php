<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
use SimpleMSPDoc\Core\Context;
final class InstallController
{
 public function __construct(private Context $ctx){}
 public function handle(): void {
  if($_SERVER['REQUEST_METHOD']==='POST'){
   $dsn=(string)($_POST['dsn']??'');if($dsn===''){$host=(string)($_POST['database_host']??'localhost');$port=(int)($_POST['database_port']??3306);$name=(string)($_POST['database_name']??'');if(!preg_match('/^[a-zA-Z0-9_.:-]+$/',$host)||!preg_match('/^[a-zA-Z0-9_]+$/',$name)||$port<1||$port>65535)throw new \SimpleMSPDoc\Core\HttpException(422,'Invalid database host, port or name.');$dsn="mysql:host=$host;port=$port;dbname=$name;charset=utf8mb4";}
   (new Installer($this->ctx->privateRoot,$this->ctx->codeRoot.'/database/migrations'))->install(['dsn'=>$dsn,'username'=>(string)($_POST['database_user']??''),'password'=>(string)($_POST['database_password']??'')],$_POST,(string)($_POST['setup_token']??''));$this->ctx->flash('Installation complete. Sign in with your administrator account.');$this->ctx->redirect('/login');
  }$this->ctx->render('install',['title'=>'Fresh installation','requirements'=>Requirements::check(),'hasToken'=>is_file($this->ctx->privateRoot.'/var/setup-token')]);
 }
}
