<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\HttpException;
final class AssetService extends \SimpleMSPDoc\Core\RecordService
{
 protected string $type='asset';
 protected function validate(array $input,?array $old=null): array {
  $d=parent::validate($input,$old);$payload=$d['payload'];$type=(int)($payload['template_id']??0);if($old&&(int)$old['payload']['template_id']!==$type)throw new HttpException(422,'An existing asset cannot change template. Create a new asset instead.');
  $fields=(new TemplateService($this->db,$this->audit))->get($type)['fields'];$values=(new FieldValidator())->validate($fields,$payload['values']??[]);
  foreach($fields as $f)if($f['archived']&&$old&&array_key_exists($f['id'],$old['payload']['values']))$values[$f['id']]=$old['payload']['values'][$f['id']];
  $d['payload']=['template_id'=>$type,'values'=>$values,'notes'=>mb_substr((string)($payload['notes']??''),0,20000)];
  foreach(['location_id'=>'location','contact_id'=>'contact'] as $key=>$targetType){$id=(int)($payload[$key]??0);if($id){$r=$this->db->one('SELECT organization_id,type,deleted_at FROM records WHERE id=?',[$id]);$org=(int)($old['organization_id']??($input['_organization_id']??0));if(!$r||$r['type']!==$targetType||$r['deleted_at']!==null||(int)$r['organization_id']!==$org)throw new HttpException(422,'Referenced record must belong to the same organization.');}$d['payload'][$key]=$id?:null;}
  return $d;
 }
 protected function searchText(array $payload): string {
  $ids=array_column($this->db->all('SELECT id FROM asset_fields WHERE asset_type_id=? AND archived=0 ORDER BY position,id',[(int)$payload['template_id']]),'id');return AssetSearchText::build($payload,$ids);
 }
 public function create(\SimpleMSPDoc\Core\Actor $actor,int $organizationId,array $input): int {
  $input['_organization_id']=$organizationId;$type=(int)($input['payload']['template_id']??0);
  return $this->db->transaction(function()use($actor,$organizationId,$input,$type){$this->db->one('SELECT id FROM asset_types WHERE id=? FOR UPDATE',[$type]);return parent::create($actor,$organizationId,$input);});
 }
 public function update(\SimpleMSPDoc\Core\Actor $actor,int $id,array $input): void {
  $existing=$this->get($actor,$id);$type=(int)$existing['payload']['template_id'];
  $this->db->transaction(function()use($actor,$id,$input,$type){$this->db->one('SELECT id FROM asset_types WHERE id=? FOR UPDATE',[$type]);$this->db->one('SELECT id FROM records WHERE id=? FOR UPDATE',[$id]);parent::update($actor,$id,$input);});
 }
}
