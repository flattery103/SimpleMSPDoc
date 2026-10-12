<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Assets\{TemplateService,AssetService,FieldValidator};
final class AssetTest extends DatabaseTestCase
{
 public function testCustomTypesAndArchivedFieldsKeepValues(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$org=$this->org();$audit=new AuditService($this->db);$t=new TemplateService($this->db,$audit);$type=$t->create($a,['name'=>'Warranty','fields'=>[['name'=>'Count','type'=>'number','required'=>true],['name'=>'Covered','type'=>'boolean'],['name'=>'Due','type'=>'date'],['name'=>'Portal','type'=>'url'],['name'=>'Plan','type'=>'choice','options'=>['Basic','Pro']],['name'=>'Notes','type'=>'multiline'],['name'=>'Text','type'=>'text']]]);
  $fields=$t->get($type)['fields'];$values=[];foreach($fields as $f)$values[$f['id']]=match($f['field_type']){'number'=>'0','boolean'=>false,'date'=>'2027-01-01','url'=>'https://example.com','choice'=>'Pro','multiline'=>"a\nb",default=>'hello'};
  $s=new AssetService($this->db,$audit);$id=$s->create($a,$org,['title'=>'Asset','payload'=>['template_id'=>$type,'values'=>$values]]);$r=$s->get($a,$id);self::assertSame(0.0,$r['payload']['values'][$fields[0]['id']]);self::assertFalse($r['payload']['values'][$fields[1]['id']]);
  $t->archiveField($a,(int)$fields[0]['id']);unset($values[$fields[0]['id']]);$s->update($a,$id,['title'=>'Asset updated','payload'=>['template_id'=>$type,'values'=>$values]]);self::assertSame(0.0,$s->get($a,$id)['payload']['values'][$fields[0]['id']]);self::assertSame(1,(int)$t->get($type)['fields'][0]['archived']);
 }
 public function testInvalidValuesAndMissingRequiredFields(): void {
  $v=new FieldValidator();foreach([['url','javascript:alert(1)'],['date','2026-02-31'],['number','hello'],['choice','Other'],['boolean','maybe']] as [$type,$value]){try{$v->validate([['id'=>1,'field_type'=>$type,'name'=>'Field','required'=>1,'options'=>'["Basic"]','archived'=>0]],[1=>$value]);self::fail('Invalid value accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(422,$e->status);}}
 }
 public function testArchivedValuesAreRemovedFromClientSearch(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$c=new Actor($this->user('read-only','client'),'read-only','client');$org=$this->org();$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$c->id,$org]);$audit=new AuditService($this->db);$t=new TemplateService($this->db,$audit);$type=$t->create($a,['name'=>'Sensitive legacy fields','fields'=>[['name'=>'Old field','type'=>'text']]]);$field=$t->get($type)['fields'][0]['id'];$s=new AssetService($this->db,$audit);$s->create($a,$org,['title'=>'Shared asset','visibility'=>'client','payload'=>['template_id'=>$type,'values'=>[$field=>'Old sensitive value']]]);$search=new \SimpleMSPDoc\Modules\Search\SearchService($this->db);self::assertSame(1,$search->search($c,'Old sensitive value',null,null,1)['total']);$t->archiveField($a,(int)$field);self::assertSame(0,$search->search($c,'Old sensitive value',null,null,1)['total']);$s->update($a,(int)$this->db->one("SELECT id FROM records WHERE type='asset'")['id'],['title'=>'Shared asset','visibility'=>'client','payload'=>['template_id'=>$type,'values'=>[]]]);self::assertSame(0,$search->search($c,'Old sensitive value',null,null,1)['total']);
 }

}
