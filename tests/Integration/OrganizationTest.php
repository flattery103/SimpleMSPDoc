<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Organizations\{OrganizationService,LocationService,ContactService};
final class OrganizationTest extends DatabaseTestCase
{
 public function testAssignmentVisibilityArchiveAndRestore(): void {
  $this->schema();$admin=new Actor($this->user(),'administrator','staff');$client=new Actor($this->user('read-only','client'),'read-only','client');$org=$this->org();$other=$this->org('Other');$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$client->id,$org]);$audit=new AuditService($this->db);$s=new LocationService($this->db,$audit);
  $internal=$s->create($admin,$org,['title'=>'Private office','payload'=>['address'=>'123 Road']]);$shared=$s->create($admin,$org,['title'=>'Shared office','visibility'=>'client']);$foreign=$s->create($admin,$other,['title'=>'Other shared','visibility'=>'client']);
  self::assertSame([$shared],array_map('intval',array_column($s->list($client,$org),'id')));self::assertSame(1,count((new OrganizationService($this->db,$audit))->listVisible($client)));
  foreach([$internal,$foreign] as $id){try{$s->get($client,$id);self::fail('Private record leaked');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(404,$e->status);}}
  $s->archive($admin,$shared);self::assertSame([],$s->list($client,$org));$s->restore($admin,$shared);self::assertSame('Shared office',$s->get($client,$shared)['title']);
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);$s->update($client,$shared,['title'=>'Tampered']);
 }
 public function testRecordCannotMoveOrganizationsThroughUpdate(): void {$this->schema();$a=new Actor($this->user(),'administrator','staff');$o=$this->org();$other=$this->org('Other');$s=new ContactService($this->db,new AuditService($this->db));$id=$s->create($a,$o,['title'=>'Contact']);$s->update($a,$id,['title'=>'Edited','organization_id'=>$other]);self::assertSame($o,(int)$s->get($a,$id)['organization_id']);}
}
