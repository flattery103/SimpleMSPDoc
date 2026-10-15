<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Auth;
use SimpleMSPDoc\Core\{Actor,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class AuthService
{
 public function __construct(private Database $db,private AuditService $audit,private string $ip){}
 public function throttle(string $account,string $stage): void {
  $account=strtolower(trim($account));$hash=hash('sha256',$this->ip);
  $n=$this->db->one("SELECT COUNT(*) AS n FROM auth_attempts WHERE stage=? AND created_at >= DATE_SUB(UTC_TIMESTAMP(),INTERVAL 15 MINUTE) AND (account=? OR ip_hash=?)",[$stage,$account,$hash]);
  if((int)$n['n']>=5)throw new HttpException(429,'Too many attempts. Try again in 15 minutes.');
 }
 public function failure(string $account,string $stage): void {$this->db->run('INSERT INTO auth_attempts(account,ip_hash,stage,created_at) VALUES (?,?,?,UTC_TIMESTAMP())',[strtolower(trim($account)),hash('sha256',$this->ip),$stage]);$this->audit->record(null,'authentication.failed','user',null,null,['reason'=>$stage]);}
 public function login(string $username,string $password): ?Actor {
  $username=strtolower(trim($username));$this->throttle($username,'password');$u=$this->db->one('SELECT * FROM users WHERE username=? AND active=1',[$username]);
  $dummy='$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2uheWG/igi';
  if(!password_verify($password,$u['password_hash']??$dummy)||!$u){$this->failure($username,'password');return null;}
  if(password_needs_rehash($u['password_hash'],PASSWORD_DEFAULT))$this->db->run('UPDATE users SET password_hash=? WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$u['id']]);
  return Actor::from($u); // Password verified; HTTP layer still enforces MFA before granting access.
 }
}
