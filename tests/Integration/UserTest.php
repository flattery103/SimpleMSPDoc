<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Auth\UserService;
use SimpleMSPDoc\Modules\Audit\AuditService;
final class UserTest extends DatabaseTestCase
{
 public function testAdminCreatesClientWithAssignmentsAndResetInvalidatesSession(): void {
  $this->schema();$aid=$this->user();$org=$this->org();$a=new Actor($aid,'administrator','staff');$s=new UserService($this->db,new AuditService($this->db));$id=$s->save($a,null,['username'=>'Client@example.com','name'=>'Client','password'=>'Long-enough-password','role'=>'read-only','audience'=>'client','active'=>1,'require_mfa'=>1,'organizations'=>[$org]]);
  self::assertSame('client@example.com',$this->db->one('SELECT username FROM users WHERE id=?',[$id])['username']);self::assertSame($org,(int)$this->db->one('SELECT organization_id FROM user_organizations WHERE user_id=?',[$id])['organization_id']);
  $s->resetMfa($a,$id);self::assertSame(2,(int)$this->db->one('SELECT session_version FROM users WHERE id=?',[$id])['session_version']);
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);$s->save($a,$aid,['username'=>'admin','name'=>'Admin','role'=>'read-only','audience'=>'staff','active'=>1,'organizations'=>[]]);
 }
 public function testTechnicianCannotManageUsers(): void {$this->schema();$id=$this->user('technician');$this->expectException(\SimpleMSPDoc\Core\HttpException::class);(new UserService($this->db,new AuditService($this->db)))->list(new Actor($id,'technician','staff'));}
 public function testAdministratorCanLowerPasswordMinimumToOneCharacter(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$audit=new AuditService($this->db);$policy=new \SimpleMSPDoc\Modules\Auth\PasswordPolicy($this->db,$audit);
  self::assertSame(12,$policy->minimum());$policy->save($a,'1');self::assertSame(1,$policy->minimum());
  $id=(new UserService($this->db,$audit))->save($a,null,['username'=>'short','name'=>'Short','password'=>'x','active'=>1]);self::assertTrue(password_verify('x',$this->db->one('SELECT password_hash FROM users WHERE id=?',[$id])['password_hash']));
  $policy->save($a,'3');try{(new UserService($this->db,$audit))->save($a,null,['username'=>'short2','name'=>'Short','password'=>'xx','active'=>1]);self::fail('Short password accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(422,$e->status);}
  foreach(['0','73','1.5','abc'] as $n){try{$policy->save($a,$n);self::fail('Invalid policy accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(422,$e->status);}}
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);$policy->save(new Actor($this->user('technician'),'technician','staff'),'1');
 }
}
