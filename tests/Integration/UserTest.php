<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
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
}
