<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Auth;
use SimpleMSPDoc\Core\{Actor,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
use OTPHP\TOTP;
final class MfaService
{
 private string $key;
 public function __construct(private Database $db,private AuditService $audit,string $key){$this->key=base64_decode($key,true)?:'';if(strlen($this->key)!==32)throw new \RuntimeException('Invalid application key.');}
 private function encrypt(string $secret): string {$iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($secret,'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,$iv,$tag);if($cipher===false)throw new \RuntimeException('Cannot encrypt MFA secret.');return base64_encode($iv.$tag.$cipher);}
 private function decrypt(string $value): string {$raw=base64_decode($value,true);if($raw===false||strlen($raw)<29)throw new \RuntimeException('Invalid MFA secret.');$plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16));if($plain===false)throw new \RuntimeException('Cannot decrypt MFA secret.');return $plain;}
 public function enroll(int $userId): array {
  $u=$this->db->one('SELECT * FROM users WHERE id=? AND active=1',[$userId]);if(!$u||$u['mfa_enabled'])throw new HttpException(409,'MFA is already enabled or the account is unavailable.');
  if($u['mfa_secret'])$secret=$this->decrypt($u['mfa_secret']);else{$secret=TOTP::generate()->getSecret();$this->db->run('UPDATE users SET mfa_secret=? WHERE id=?',[$this->encrypt($secret),$userId]);}
  $totp=TOTP::createFromSecret($secret);$totp->setLabel($u['username']);$totp->setIssuer('SimpleMSPDoc');return ['secret'=>$secret,'uri'=>$totp->getProvisioningUri()];
 }
 public function confirmEnrollment(int $userId,string $code,?int $expectedSessionVersion=null): array {
  return $this->db->transaction(function()use($userId,$code,$expectedSessionVersion){
   $u=$this->db->one('SELECT * FROM users WHERE id=? FOR UPDATE',[$userId]);
   if(!$u||!$u['active']||($expectedSessionVersion!==null&&(int)$u['session_version']!==$expectedSessionVersion))throw new HttpException(403,'Account changed. Sign in again before enabling MFA.');
   if($u['mfa_enabled'])throw new HttpException(409,'MFA is already configured.');
   if(!$u['mfa_secret']||!preg_match('/^\\d{6}$/',$code)||!TOTP::createFromSecret($this->decrypt($u['mfa_secret']))->verify($code))throw new HttpException(422,'The authenticator code is invalid.');
   $codes=[];for($i=0;$i<8;$i++)$codes[]=bin2hex(random_bytes(8));$hashes=array_map(fn($c)=>hash('sha256',$c),$codes);
   $this->db->run('UPDATE users SET mfa_enabled=1,mfa_last_step=?,recovery_codes=?,session_version=session_version+1 WHERE id=?',[(int)floor(time()/30),json_encode($hashes),$userId]);
   $this->audit->record(Actor::from($u),'mfa.enrolled','user',$userId,null);return $codes;
  });
 }
 public function verify(int $userId,string $code): bool {
  if(!preg_match('/^\d{6}$/',$code))return false;
  return $this->db->transaction(function()use($userId,$code){$u=$this->db->one('SELECT * FROM users WHERE id=? AND active=1 AND mfa_enabled=1 FOR UPDATE',[$userId]);if(!$u||!$u['mfa_secret'])return false;$otp=TOTP::createFromSecret($this->decrypt($u['mfa_secret']));$time=time();foreach([-1,0,1] as $window){$step=(int)floor($time/30)+$window;if($step>(int)($u['mfa_last_step']??0)&&hash_equals($otp->at($step*30),$code)){$this->db->run('UPDATE users SET mfa_last_step=? WHERE id=?',[$step,$userId]);return true;}}return false;});
 }
 public function consumeRecoveryCode(int $userId,string $code): bool {
  return $this->db->transaction(function()use($userId,$code){$u=$this->db->one('SELECT * FROM users WHERE id=? AND active=1 AND mfa_enabled=1 FOR UPDATE',[$userId]);if(!$u)return false;$codes=json_decode($u['recovery_codes']??'[]',true);$hash=hash('sha256',trim($code));foreach($codes as $i=>$stored)if(hash_equals($stored,$hash)){unset($codes[$i]);$this->db->run('UPDATE users SET recovery_codes=? WHERE id=?',[json_encode(array_values($codes)),$userId]);$this->audit->record(Actor::from($u),'mfa.recovery_used','user',$userId,null);return true;}return false;});
 }
}
