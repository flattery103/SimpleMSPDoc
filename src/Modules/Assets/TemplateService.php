<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class TemplateService
{
 public function __construct(private Database $db,private AuditService $audit){}
 public function list(): array {return $this->db->all('SELECT * FROM asset_types ORDER BY name');}
 public function get(int $id): array {$r=$this->db->one('SELECT * FROM asset_types WHERE id=?',[$id]);if(!$r)throw new HttpException(422,'Invalid asset template.');$r['fields']=$this->db->all('SELECT * FROM asset_fields WHERE asset_type_id=? ORDER BY position,id',[$id]);return $r;}
 public function create(Actor $actor,array $definition): int {
  (new AccessPolicy($this->db))->admin($actor);$name=trim((string)($definition['name']??''));if($name===''||mb_strlen($name)>190||$this->db->one('SELECT id FROM asset_types WHERE name=?',[$name]))throw new HttpException(422,'Use a unique template name of at most 190 characters.');
  return $this->db->transaction(function()use($actor,$definition,$name){$this->db->run('INSERT INTO asset_types(name,description) VALUES (?,?)',[$name,mb_substr((string)($definition['description']??''),0,2000)]);$id=(int)$this->db->connection()->lastInsertId();foreach($definition['fields']??[] as $pos=>$field)$this->insertField($id,$field,(int)$pos);$this->audit->record($actor,'template.created','asset_type',$id,null);return $id;});
 }
 public function addField(Actor $actor,int $typeId,array $field): void {(new AccessPolicy($this->db))->admin($actor);$this->get($typeId);$this->db->transaction(function()use($actor,$typeId,$field){$this->insertField($typeId,$field,count($this->get($typeId)['fields']));$this->audit->record($actor,'template.field_added','asset_type',$typeId,null);});}
 private function insertField(int $typeId,array $field,int $pos): void {
  $name=trim((string)($field['name']??''));$type=$field['type']??'';$options=$field['options']??[];if(is_string($options))$options=array_filter(array_map('trim',explode(',',$options)));
  if($name===''||mb_strlen($name)>190||!in_array($type,['text','multiline','number','date','boolean','url','choice'],true)||!is_array($options)||count($options)>100||($type==='choice'&&$options===[]))throw new HttpException(422,'Invalid custom field definition.');
  foreach($options as $o)if(!is_string($o)||mb_strlen($o)>190)throw new HttpException(422,'Invalid choice option.');
  $this->db->run('INSERT INTO asset_fields(asset_type_id,name,field_type,required,options,position) VALUES (?,?,?,?,?,?)',[$typeId,$name,$type,!empty($field['required'])?1:0,json_encode(array_values($options)), $pos]);
 }
 public function archiveField(Actor $actor,int $fieldId): void {(new AccessPolicy($this->db))->admin($actor);if(!$this->db->one('SELECT id FROM asset_fields WHERE id=?',[$fieldId]))throw new HttpException(404,'Field not found.');$this->db->transaction(function()use($actor,$fieldId){$this->db->run('UPDATE asset_fields SET archived=1,required=0 WHERE id=?',[$fieldId]);$this->audit->record($actor,'template.field_archived','asset_field',$fieldId,null);});}
}
