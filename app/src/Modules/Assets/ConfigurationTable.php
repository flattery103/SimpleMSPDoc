<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException,RecordRepository,RecordScope,ScopeQuery};
final class ConfigurationTable
{
 public function __construct(private Database $db){}
 public function available(Actor $actor,int $org): array {
  (new AccessPolicy($this->db))->require($actor,'read',new RecordScope($org,$actor->audience==='client'?'client':'internal')); $columns=['title'=>'Name','template'=>'Template','category'=>'Category','tags'=>'Tags','visibility'=>'Visibility','updated_at'=>'Updated (UTC)','created_at'=>'Created (UTC)']+ConfigurationDetails::FIELDS+['location_id'=>'Linked location','contact_id'=>'Primary contact','notes'=>'Notes','action1_id'=>'Action1 device ID','action1_last_sync'=>'Action1 last sync (UTC)'];
  [$where,$args]=ScopeQuery::records($actor);if($actor->audience==='staff'&&$actor->role!=='read-only'){[$archived,$archivedArgs]=ScopeQuery::records($actor,true);$where='(('.$where.') OR ('.$archived.'))';$args=array_merge($args,$archivedArgs);}$args[]=$org;
  foreach($this->db->all("SELECT f.*,t.name AS template_name FROM asset_fields f JOIN asset_types t ON t.id=f.asset_type_id WHERE f.archived=0 AND EXISTS (SELECT 1 FROM records r JOIN organizations o ON o.id=r.organization_id WHERE r.type='asset' AND CAST(JSON_UNQUOTE(JSON_EXTRACT(r.payload,'$.template_id')) AS UNSIGNED)=t.id AND ".$where.' AND r.organization_id=?) ORDER BY t.name,f.position,f.id',$args) as $f)if(ConfigurationDetails::fieldKey($f)===null)$columns['field_'.$f['id']]=$f['template_name'].' · '.$f['name'];return $columns;
 }
 public function selected(Actor $actor,int $org,array $available): array {
  $row=$this->db->one('SELECT columns_json FROM configuration_preferences WHERE user_id=? AND organization_id=?',[$actor->id,$org]);$saved=$row?json_decode($row['columns_json'],true):['title','category','visibility','updated_at'];foreach($saved as &$key)if(is_string($key)&&preg_match('/^field_(\d+)$/',$key,$m)){$f=$this->db->one('SELECT * FROM asset_fields WHERE id=?',[(int)$m[1]]);$mapped=$f?ConfigurationDetails::fieldKey($f):null;if($mapped!==null)$key=$mapped;}unset($key);$result=array_values(array_unique(array_filter($saved,fn($k)=>is_string($k)&&isset($available[$k]))));return $result?:['title'];
 }
 public function save(Actor $actor,int $org,mixed $columns): void {
  $available=$this->available($actor,$org);if(!is_array($columns)||!array_is_list($columns)||count($columns)>1000)throw new HttpException(422,'Select valid configuration columns.');foreach($columns as $key)if(!is_string($key)||!isset($available[$key]))throw new HttpException(422,'Select valid configuration columns.');$columns=array_values(array_unique($columns));if($columns===[])throw new HttpException(422,'Select at least one column.');$this->db->run('INSERT INTO configuration_preferences(user_id,organization_id,columns_json) VALUES (?,?,?) ON DUPLICATE KEY UPDATE columns_json=VALUES(columns_json)',[$actor->id,$org,json_encode($columns,JSON_THROW_ON_ERROR)]);
 }
 public function cells(Actor $actor,array $records): array {
  $out=[];$repo=new RecordRepository($this->db);foreach($records as $r){$p=$r['payload'];$fields=$this->db->all('SELECT * FROM asset_fields WHERE asset_type_id=?',[$p['template_id']]);$t=$this->db->one('SELECT name FROM asset_types WHERE id=?',[$p['template_id']]);$cells=['title'=>$r['title'],'template'=>$t['name']??'','category'=>$r['category'],'tags'=>implode(', ',$r['tags']),'visibility'=>$r['visibility']==='client'?'Client visible':'Internal only','updated_at'=>$r['updated_at'],'created_at'=>$r['created_at'],'notes'=>ConfigurationDetails::notes($p,$fields)]+ConfigurationDetails::effective($p,$fields);
   foreach(['location_id','contact_id'] as $key){$cells[$key]='';if(!empty($p[$key]))try{$ref=$repo->get($actor,(int)$p[$key]);$cells[$key]=$ref['title'];}catch(HttpException $e){if($e->status!==404)throw $e;}}
   $source=$this->db->one("SELECT e.external_id,c.last_sync FROM external_records e JOIN integration_connections c ON c.id=e.connection_id WHERE e.record_id=? AND c.organization_id=? AND c.provider='action1' LIMIT 1",[$r['id'],$r['organization_id']]);$cells['action1_id']=$source['external_id']??'';$cells['action1_last_sync']=$source['last_sync']??'';
   foreach($p['values'] as $id=>$v)$cells['field_'.$id]=is_bool($v)?($v?'Yes':'No'):($v??'');$out[$r['id']]=$cells;
  }return $out;
 }
}
