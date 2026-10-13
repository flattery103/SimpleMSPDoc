<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Organizations\LocationService;
use SimpleMSPDoc\Modules\Search\SearchService;
final class SearchTest extends DatabaseTestCase
{
 public function testSearchFiltersCountsAndLiteralWildcards(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$c=new Actor($this->user('read-only','client'),'read-only','client');$org=$this->org();$other=$this->org('Other');$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$c->id,$org]);$s=new LocationService($this->db,new AuditService($this->db));$one=$s->create($a,$org,['title'=>'Recovery 100%','visibility'=>'client']);$s->create($a,$org,['title'=>'Recovery private']);$s->create($a,$other,['title'=>'Recovery foreign','visibility'=>'client']);$search=new SearchService($this->db);
  $r=$search->search($c,'Recovery',null,null,1);self::assertSame(1,$r['total']);self::assertSame($one,(int)$r['items'][0]['id']);self::assertSame(1,$search->search($c,'%',null,null,1)['total']);self::assertSame(0,$search->search($c,'Recovery',$other,null,1)['total']);$s->archive($a,$one);self::assertSame(0,$search->search($c,'Recovery',null,null,1)['total']);
 }
}
