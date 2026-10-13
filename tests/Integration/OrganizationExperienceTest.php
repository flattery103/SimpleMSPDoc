<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\{Actor,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Organizations\OrganizationService;
use SimpleMSPDoc\Modules\Passwords\PasswordService;
final class OrganizationExperienceTest extends DatabaseTestCase
{
 public function testEncryptedPasswordsPermissionsLinksAndAudit(): void {
  $this->schema();$admin=new Actor($this->user(),'administrator','staff');$reader=new Actor($this->user('read-only'),'read-only','staff');$client=new Actor($this->user('read-only','client'),'read-only','client');$org=$this->org();$other=$this->org();foreach([$reader,$client] as $a)$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$a->id,$org]);$s=new PasswordService($this->db,new AuditService($this->db),base64_encode(random_bytes(32)));
  $id=$s->save($admin,$org,null,['title'=>'Server admin','secret'=>'Do-not-leak-this!','username'=>'root']);$stored=$this->db->one('SELECT * FROM passwords WHERE id=?',[$id]);self::assertStringNotContainsString('Do-not-leak-this!',json_encode($stored));self::assertArrayNotHasKey('secret_cipher',$s->get($reader,$id));self::assertSame('Do-not-leak-this!',$s->reveal($reader,$id));self::assertSame('password.revealed',$this->db->one('SELECT action FROM audit_events ORDER BY id DESC LIMIT 1')['action']);
  foreach([fn()=>$s->reveal($client,$id),fn()=>$s->save($reader,$org,$id,['title'=>'Bad']),fn()=>$s->save($admin,$other,$id,['title'=>'Move'])] as $f){try{$f();self::fail('Access accepted');}catch(HttpException $e){self::assertContains($e->status,[403,404]);}}
  $s->save($admin,$org,$id,['title'=>'Updated','secret'=>'','revision'=>1]);self::assertSame('Do-not-leak-this!',$s->reveal($admin,$id));
  $s->save($admin,$org,$id,['title'=>'Shared','visibility'=>'client','revision'=>2]);self::assertSame('Do-not-leak-this!',$s->reveal($client,$id));
  $asset=(new \SimpleMSPDoc\Modules\Assets\AssetService($this->db,new AuditService($this->db)))->create($admin,$other,['title'=>'Foreign','payload'=>['template_id'=>1,'values'=>[]]]);
  try{$s->save($admin,$org,null,['title'=>'Invalid link','secret'=>'x','configuration_id'=>$asset]);self::fail('Cross organization link accepted');}catch(HttpException $e){self::assertSame(422,$e->status);}
  self::assertStringNotContainsString('Do-not-leak-this!',json_encode($this->db->all('SELECT * FROM audit_events')));$this->db->run("UPDATE passwords SET secret_cipher='broken' WHERE id=?",[$id]);$this->expectException(\RuntimeException::class);$s->reveal($admin,$id);
 }
 public function testOrganizationDetailsAndSafeLogo(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$s=new OrganizationService($this->db,new AuditService($this->db));$id=$s->save($a,null,['name'=>'Example','website'=>'https://example.com','phone'=>'555-1234','address'=>'Main Street','notes'=>'Account notes']);self::assertSame('555-1234',$s->get($a,$id)['phone']);$png=base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+jK1kAAAAASUVORK5CYII=');$s->setLogo($a,$id,$png);self::assertSame($png,$s->logo($a,$id)['logo_bytes']);$this->expectException(HttpException::class);$s->setLogo($a,$id,'<svg onload="alert(1)"></svg>');
 }
}
