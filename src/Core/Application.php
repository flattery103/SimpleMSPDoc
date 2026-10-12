<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
use FastRoute\RouteCollector;
use SimpleMSPDoc\Modules\{Auth\AuthController,Installation\InstallController,Installation\UpgradeController,Organizations\OrganizationController,Records\RecordController,Auth\UserController,Assets\TemplateController,Search\SearchController,Audit\AuditController};
use SimpleMSPDoc\Modules\Installation\{Maintenance,MigrationRunner};
final class Application
{
 public function __construct(private string $codeRoot,private string $privateRoot){}
 public function handle(): void {
  $url=new Url($_SERVER['SCRIPT_NAME']??'/index.php');$ctx=new Context($this->codeRoot,$this->privateRoot,$url);
  header('X-Content-Type-Options: nosniff');header('Referrer-Policy: same-origin');header('X-Frame-Options: DENY');header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self'; img-src 'self' data:; form-action 'self'; frame-ancestors 'none'; base-uri 'self'");header('Cache-Control: no-store');
  $sessionPath=$this->privateRoot.'/var/sessions';if(!is_dir($sessionPath))mkdir($sessionPath,0700,true);session_save_path($sessionPath);
  Session::start(!empty($_SERVER['HTTPS'])&&$_SERVER['HTTPS']!=='off',$url->base?:'/');
  try {
   $path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH)?:'/';if($url->base!==''&&str_starts_with($path,$url->base))$path=substr($path,strlen($url->base))?:'/';$method=$_SERVER['REQUEST_METHOD'];
   if(!is_file($this->privateRoot.'/config/local.php')){if($path!=='/install')$ctx->redirect('/install');if($method==='POST')Csrf::verify();(new InstallController($ctx))->handle();return;}
   if($path==='/install')throw new HttpException(403,'Installation is locked.');
   $ctx->config=Config::load($this->privateRoot.'/config/local.php')->data;$ctx->db=Database::connect($ctx->config['database']);$ctx->db->run("SET time_zone='+00:00'");$ctx->audit=new \SimpleMSPDoc\Modules\Audit\AuditService($ctx->db);
   if(isset($_SESSION['user_id'])){$u=$ctx->db->one('SELECT * FROM users WHERE id=? AND active=1',[$_SESSION['user_id']]);if(!$u||(int)$u['session_version']!==(int)($_SESSION['session_version']??0)||time()-(int)($_SESSION['last_seen']??0)>1800){Session::logout();}else{$ctx->user=$u;$ctx->actor=Actor::from($u);$_SESSION['last_seen']=time();}}
   if(isset($_SESSION['pending_id'])&&time()-(int)($_SESSION['pending_at']??0)>600)Session::logout();
   if($method==='POST')Csrf::verify();
   $auth=new AuthController($ctx);
   if($path==='/login'){$auth->login();return;}
   if($path==='/logout'&&$method==='POST'){$auth->logout();return;}
   if(in_array($path,['/mfa','/mfa/enroll','/mfa/confirm','/mfa/recovery'],true)){$auth->mfa($path);return;}
   if(!$ctx->actor){$ctx->redirect(isset($_SESSION['pending_id'])?($_SESSION['pending_enroll']?'/mfa/enroll':'/mfa'):'/login');}
   if($ctx->user['require_mfa']&&!$ctx->user['mfa_enabled']){$_SESSION['pending_id']=$ctx->actor->id;$_SESSION['pending_at']=time();$_SESSION['pending_enroll']=true;unset($_SESSION['user_id']);$ctx->redirect('/mfa/enroll');}
   if((new Maintenance($ctx->db))->active()&&$path!=='/upgrade')throw new HttpException(503,'Maintenance is active. An administrator must review the upgrade status.');
   $org=new OrganizationController($ctx);$record=new RecordController($ctx);$users=new UserController($ctx);$templates=new TemplateController($ctx);
   $dispatcher=\FastRoute\simpleDispatcher(function(RouteCollector $r)use($org,$record,$users,$templates,$ctx){
    $r->addRoute('GET','/',[$org,'index']);$r->addRoute('GET','/organizations',[$org,'index']);$r->addRoute('GET','/organizations/new',[$org,'form']);$r->addRoute('POST','/organizations/save',[$org,'save']);$r->addRoute('GET','/organizations/{id:\d+}',[$org,'show']);$r->addRoute('GET','/organizations/{id:\d+}/edit',[$org,'form']);$r->addRoute('POST','/organizations/{id:\d+}/save',[$org,'save']);$r->addRoute('POST','/organizations/{id:\d+}/archive',[$org,'archive']);$r->addRoute('POST','/organizations/{id:\d+}/restore',[$org,'restore']);
    $r->addRoute('GET','/organizations/{org:\d+}/{type:asset|article|contact|location}',[$record,'index']);$r->addRoute('GET','/organizations/{org:\d+}/{type:asset|article|contact|location}/new',[$record,'form']);$r->addRoute('POST','/organizations/{org:\d+}/{type:asset|article|contact|location}/save',[$record,'save']);
    $r->addRoute('GET','/records/{id:\d+}',[$record,'show']);$r->addRoute('GET','/records/{id:\d+}/edit',[$record,'form']);$r->addRoute('POST','/records/{id:\d+}/save',[$record,'save']);$r->addRoute('POST','/records/{id:\d+}/archive',[$record,'archive']);$r->addRoute('POST','/records/{id:\d+}/restore',[$record,'restore']);$r->addRoute('POST','/records/{id:\d+}/attachments',[$record,'upload']);$r->addRoute('GET','/attachments/{id:\d+}',[$record,'download']);$r->addRoute('POST','/attachments/{id:\d+}/delete',[$record,'deleteAttachment']);$r->addRoute('POST','/records/{id:\d+}/relationships',[$record,'relate']);$r->addRoute('POST','/records/{id:\d+}/relationships/{relationship:\d+}/delete',[$record,'unrelate']);$r->addRoute('GET','/records/{id:\d+}/revisions/{revision:\d+}',[$record,'revision']);$r->addRoute('POST','/records/{id:\d+}/revisions/{revision:\d+}/restore',[$record,'restoreRevision']);
    $r->addRoute('GET','/users',[$users,'index']);$r->addRoute('GET','/users/new',[$users,'form']);$r->addRoute('GET','/users/{id:\d+}/edit',[$users,'form']);$r->addRoute('POST','/users/save',[$users,'save']);$r->addRoute('POST','/users/{id:\d+}/save',[$users,'save']);$r->addRoute('POST','/users/{id:\d+}/reset-mfa',[$users,'reset']);
    $r->addRoute('GET','/templates',[$templates,'index']);$r->addRoute('POST','/templates/save',[$templates,'save']);$r->addRoute('GET','/templates/{id:\d+}',[$templates,'show']);$r->addRoute('POST','/templates/{id:\d+}/fields',[$templates,'addField']);$r->addRoute('POST','/template-fields/{id:\d+}/archive',[$templates,'archiveField']);
    $r->addRoute('GET','/search',[new SearchController($ctx),'index']);$r->addRoute('GET','/audit',[new AuditController($ctx),'index']);$r->addRoute(['GET','POST'],'/upgrade',[new UpgradeController($ctx),'index']);
   });$route=$dispatcher->dispatch($method,$path);if($route[0]===\FastRoute\Dispatcher::NOT_FOUND)throw new HttpException(404,'Page not found.');if($route[0]===\FastRoute\Dispatcher::METHOD_NOT_ALLOWED)throw new HttpException(405,'Method not allowed.');($route[1])($route[2]);
  }catch(\Throwable $error){if(!$error instanceof HttpException){$log=$this->privateRoot.'/var/logs';if(!is_dir($log))mkdir($log,0700,true);file_put_contents($log.'/application.log',gmdate(DATE_ATOM).' '.get_class($error).' code='.$error->getCode().' file='.$error->getFile().' line='.$error->getLine().PHP_EOL,FILE_APPEND|LOCK_EX);}$ctx->render('error',['title'=>'Request could not be completed','message'=>ErrorHandler::message($error)],$error instanceof HttpException?$error->status:500);}
 }
}
