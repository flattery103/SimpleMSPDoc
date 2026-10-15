<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\HttpException;
final class AssetService extends \SimpleMSPDoc\Core\RecordService
{
 protected string $type='asset';
 protected function validate(array $input,?array $old=null): array {
  $d=parent::validate($input,$old);$payload=$d['payload'];$type=(int)($payload['template_id']??0);if($old&&(int)$old['payload']['template_id']!==$type)throw new HttpException(422,'An existing asset cannot change template. Create a new asset instead.');
  $fields=(new TemplateService($this->db,$this->audit))->get($type)['fields'];$submitted=$payload['values']??[];
  $mergedEditor=!empty($input['_notes_are_merged']);$preserve=[];$oldEffective=$old?ConfigurationDetails::effective($old['payload'],$fields):[];
  foreach($fields as &$f){$key=ConfigurationDetails::fieldKey($f);if($key===null)continue;if($mergedEditor)unset($submitted[$f['id']]);elseif(array_key_exists($f['id'],$submitted))continue;
   if($key==='notes'&&$mergedEditor){if($f['required']&&trim((string)($payload['notes']??''))==='')throw new HttpException(422,'Notes is required.');$f['required']=0;$submitted[$f['id']]=$old['payload']['values'][$f['id']]??null;if($old)$preserve[$f['id']]=$old['payload']['values'][$f['id']]??null;}
   elseif($key==='title')$submitted[$f['id']]=$input['title']??'';
   elseif(array_key_exists($key,$payload['details']??[])){$submitted[$f['id']]=$payload['details'][$key];if($old&&(in_array((int)$f['id'],$old['payload']['managed_field_ids']??[],true)||(!array_key_exists($key,$old['payload']['details']??[])&&$payload['details'][$key]===($oldEffective[$key]??''))))$preserve[$f['id']]=$old['payload']['values'][$f['id']]??null;}
   else $submitted[$f['id']]=$old['payload']['values'][$f['id']]??null;
  }
  unset($f);$values=(new FieldValidator())->validate($fields,$submitted);foreach($preserve as $id=>$value)$values[$id]=$value;$notes=(string)($payload['notes']??'');[$notes,$noteOverrides]=$old?ConfigurationDetails::editNotes($notes,$old['payload'],$fields,$mergedEditor):[$notes,[]];$notes=mb_substr($notes,0,20000);
  foreach($fields as $f)if($f['archived']&&$old&&array_key_exists($f['id'],$old['payload']['values']))$values[$f['id']]=$old['payload']['values'][$f['id']];
  $details=ConfigurationDetails::validate($payload['details']??$old['payload']['details']??[],true);$effective=$old?ConfigurationDetails::effective($old['payload'],$fields):[];foreach($details as $key=>$value)if(!array_key_exists($key,$old['payload']['details']??[])&&$value===($effective[$key]??''))unset($details[$key]);
  $d['payload']=['template_id'=>$type,'values'=>$values,'details'=>$details,'reported_details'=>$old['payload']['reported_details']??[],'source_details'=>$old['payload']['source_details']??[],'managed_field_ids'=>$old['payload']['managed_field_ids']??[],'source_notes'=>$old['payload']['source_notes']??[],'note_overrides'=>$noteOverrides,'notes'=>$notes];
  foreach(['location_id'=>'location','contact_id'=>'contact'] as $key=>$targetType){$id=(int)($payload[$key]??0);if($id){$r=$this->db->one('SELECT organization_id,type,deleted_at FROM records WHERE id=?',[$id]);$org=(int)($old['organization_id']??($input['_organization_id']??0));if(!$r||$r['type']!==$targetType||($r['deleted_at']!==null&&(!$old||(int)($old['payload'][$key]??0)!==$id))||(int)$r['organization_id']!==$org)throw new HttpException(422,'Referenced record must belong to the same organization.');}$d['payload'][$key]=$id?:null;}
  return $d;
 }
 protected function searchText(array $payload): string {
  $fields=$this->db->all('SELECT * FROM asset_fields WHERE asset_type_id=? AND archived=0 ORDER BY position,id',[(int)$payload['template_id']]);return AssetSearchText::build($payload,array_column($fields,'id'),$fields);
 }
 public function create(\SimpleMSPDoc\Core\Actor $actor,int $organizationId,array $input): int {
  $input['_organization_id']=$organizationId;$type=(int)($input['payload']['template_id']??0);
  return $this->db->transaction(function()use($actor,$organizationId,$input,$type){$this->db->one('SELECT id FROM asset_types WHERE id=? FOR UPDATE',[$type]);return parent::create($actor,$organizationId,$input);});
 }
 public function update(\SimpleMSPDoc\Core\Actor $actor,int $id,array $input): void {
  $existing=$this->get($actor,$id);$type=(int)$existing['payload']['template_id'];
  $this->db->transaction(function()use($actor,$id,$input,$type){$this->db->one('SELECT id FROM asset_types WHERE id=? FOR UPDATE',[$type]);$this->db->one('SELECT id FROM records WHERE id=? FOR UPDATE',[$id]);parent::update($actor,$id,$input);});
 }
 public function setReportedDetails(\SimpleMSPDoc\Core\Actor $actor,int $id,array $details): void {$this->setSourceDetails($actor,$id,'action1',$details);}
 public function setSourceDetails(\SimpleMSPDoc\Core\Actor $actor,int $id,string $source,array $details,?string $notes=null): void {
  (new \SimpleMSPDoc\Core\AccessPolicy($this->db))->admin($actor);if(!preg_match('/^[a-z][a-z0-9_-]{0,49}$/',$source))throw new HttpException(422,'Invalid integration source.');$r=$this->get($actor,$id);$type=(int)$r['payload']['template_id'];
  $this->db->transaction(function()use($actor,$id,$source,$details,$notes,$type){$this->db->one('SELECT id FROM asset_types WHERE id=? FOR UPDATE',[$type]);$this->db->one('SELECT id FROM records WHERE id=? FOR UPDATE',[$id]);$r=$this->get($actor,$id);$p=$r['payload'];$managed=$p['managed_field_ids']??[];foreach($this->db->all('SELECT e.managed_fields FROM external_records e JOIN integration_connections c ON c.id=e.connection_id WHERE e.record_id=? AND c.organization_id=?',[$id,$r['organization_id']]) as $row){$managed=array_merge($managed,array_map('intval',json_decode($row['managed_fields'],true)??[]));}$p['managed_field_ids']=array_values(array_unique($managed));$validated=ConfigurationDetails::validate($details);$current=$p['source_details'][$source]??($source==='action1'?($p['reported_details']??[]):[]);foreach($details as $key=>$value){if(!isset(ConfigurationDetails::FIELDS[$key]))continue;if(isset($validated[$key]))$current[$key]=$validated[$key];else unset($current[$key]);}$p['source_details'][$source]=$current;if($source==='action1')$p['reported_details']=[];if($notes!==null)$p['source_notes'][$source]=mb_substr($notes,0,20000);$this->db->run('UPDATE records SET payload=?,search_text=? WHERE id=?',[json_encode($p,JSON_THROW_ON_ERROR),$this->searchText($p),$id]);});
 }
}
