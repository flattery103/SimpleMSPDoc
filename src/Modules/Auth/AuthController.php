<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Auth;
use SimpleMSPDoc\Core\{Actor,Context,HttpException,Session};
final class AuthController
{
 public function __construct(private Context $ctx){}
 private function auth(): AuthService {return new AuthService($this->ctx->db,$this->ctx->audit,$_SERVER['REMOTE_ADDR']??'unknown');}
 private function service(): MfaService {return new MfaService($this->ctx->db,$this->ctx->audit,$this->ctx->config['key']);}
 public function login(): void {
  if($this->ctx->actor)$this->ctx->redirect('/');
  if($_SERVER['REQUEST_METHOD']==='POST'){$a=$this->auth()->login((string)($_POST['username']??''),(string)($_POST['password']??''));if(!$a){$this->ctx->render('auth/login',['title'=>'Sign in','error'=>'Invalid username or password.'],401);return;}$u=$this->ctx->db->one('SELECT * FROM users WHERE id=?',[$a->id]);
   session_regenerate_id(true);$_SESSION=['csrf'=>bin2hex(random_bytes(32))];if($u['mfa_enabled']||$u['require_mfa']){$_SESSION['pending_id']=$a->id;$_SESSION['pending_version']=(int)$u['session_version'];$_SESSION['pending_at']=time();$_SESSION['pending_enroll']=!$u['mfa_enabled'];$this->ctx->redirect($u['mfa_enabled']?'/mfa':'/mfa/enroll');}
   Session::authenticate($u);$this->ctx->audit->record($a,'authentication.succeeded','user',$a->id,null);$this->ctx->redirect('/');
  }$this->ctx->render('auth/login',['title'=>'Sign in']);
 }
 public function logout(): void {$this->ctx->audit->record($this->ctx->actor,'authentication.logout','user',$this->ctx->actor?->id,null);Session::logout();$this->ctx->redirect('/login');}
 public function mfa(string $path): void {
  if($path==='/mfa/recovery'){if(!$this->ctx->actor)$this->ctx->redirect('/login');$codes=$_SESSION['recovery_display']??[];unset($_SESSION['recovery_display']);$this->ctx->render('auth/recovery',['title'=>'Save your recovery codes','codes'=>$codes]);return;}
  $id=$this->ctx->actor?->id??($_SESSION['pending_id']??null);if(!$id)$this->ctx->redirect('/login');$u=$this->ctx->db->one('SELECT * FROM users WHERE id=? AND active=1',[$id]);if(!$u||(!$this->ctx->actor&&(int)$u['session_version']!==(int)($_SESSION['pending_version']??0))){$this->ctx->redirect('/login');}
  if($path==='/mfa/enroll'){$enrollment=$this->service()->enroll((int)$id);$this->ctx->render('auth/enroll',['title'=>'Authenticator setup','enrollment'=>$enrollment]);return;}
  if($_SERVER['REQUEST_METHOD']==='POST'){
   $this->auth()->throttle($u['username'],'mfa');$code=trim((string)($_POST['code']??''));
   if($path==='/mfa/confirm'){
    if($u['mfa_enabled'])throw new HttpException(409,'MFA is already configured.');try{$codes=$this->service()->confirmEnrollment((int)$id,$code);}catch(HttpException $e){$this->auth()->failure($u['username'],'mfa');throw $e;}$u=$this->ctx->db->one('SELECT * FROM users WHERE id=?',[$id]);Session::authenticate($u);$_SESSION['recovery_display']=$codes;$this->ctx->audit->record(Actor::from($u),'authentication.succeeded','user',(int)$id,null);$this->ctx->redirect('/mfa/recovery');
   }
   if(!$u['mfa_enabled'])$this->ctx->redirect('/mfa/enroll');if(!$this->service()->verify((int)$id,$code)&&!$this->service()->consumeRecoveryCode((int)$id,$code)){$this->auth()->failure($u['username'],'mfa');$this->ctx->render('auth/mfa',['title'=>'Verify your identity','error'=>'Invalid or already-used code.'],401);return;}
   Session::authenticate($u);$this->ctx->audit->record(Actor::from($u),'authentication.succeeded','user',(int)$id,null);$this->ctx->redirect('/');
  }$this->ctx->render('auth/mfa',['title'=>'Verify your identity']);
 }
}
