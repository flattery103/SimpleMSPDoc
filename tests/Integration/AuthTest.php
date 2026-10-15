<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Modules\Auth\{AuthService,MfaService};
use SimpleMSPDoc\Core\{Actor,Csrf};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class AuthTest extends DatabaseTestCase
{
 public function testPasswordsThrottlingAndAuditRedaction(): void {
  $this->schema();$id=$this->user();$name=$this->db->one('SELECT username FROM users WHERE id=?',[$id])['username'];$audit=new AuditService($this->db);$auth=new AuthService($this->db,$audit,'127.0.0.1');
  self::assertSame($id,$auth->login($name,'Correct-password-123')->id);
  for($i=0;$i<5;$i++)self::assertNull($auth->login($name,'wrong'));
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);$auth->login($name,'Correct-password-123');
 }
 public function testMfaEnrollmentReplayAndRecoveryCodes(): void {
  $this->schema();$id=$this->user();$m=new MfaService($this->db,new AuditService($this->db),base64_encode(random_bytes(32)));$enroll=$m->enroll($id);$secret=$enroll['secret'];$code=\OTPHP\TOTP::createFromSecret($secret)->now();
  self::assertStringNotContainsString($secret,$this->db->one('SELECT mfa_secret FROM users WHERE id=?',[$id])['mfa_secret']);
  $recovery=$m->confirmEnrollment($id,$code);self::assertCount(8,$recovery);self::assertFalse($m->verify($id,$code));
  self::assertTrue($m->consumeRecoveryCode($id,$recovery[0]));self::assertFalse($m->consumeRecoveryCode($id,$recovery[0]));
 }
 public function testCsrfAndAuditSecrets(): void {
  $this->schema();$audit=new AuditService($this->db);$audit->record(null,'test','user',null,null,['password'=>'secret','token'=>'secret','reason'=>'ok']);$event=$this->db->one('SELECT * FROM audit_events');self::assertStringNotContainsString('secret',$event['metadata']);
  self::assertFalse(Csrf::valid('bad','good'));self::assertTrue(Csrf::valid('good','good'));
 }
}
