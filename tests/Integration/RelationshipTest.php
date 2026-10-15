<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Organizations\LocationService;
use SimpleMSPDoc\Modules\Relationships\RelationshipService;
final class RelationshipTest extends DatabaseTestCase
{
 public function testHiddenTargetsOmittedAndCrossOrganizationLinksRejected(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$c=new Actor($this->user('read-only','client'),'read-only','client');$org=$this->org();$other=$this->org('Other');$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$c->id,$org]);$audit=new AuditService($this->db);$s=new LocationService($this->db,$audit);$shared=$s->create($a,$org,['title'=>'Shared','visibility'=>'client']);$hidden=$s->create($a,$org,['title'=>'Hidden']);$foreign=$s->create($a,$other,['title'=>'Foreign']);$r=new RelationshipService($this->db,$audit);$r->link($a,'location',$shared,'location',$hidden);self::assertCount(1,$r->listVisible($a,'location',$shared));self::assertSame([],$r->listVisible($c,'location',$shared));
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);$r->link($a,'location',$shared,'location',$foreign);
 }
}
