<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
use SimpleMSPDoc\Modules\Audit\AuditService;
class RecordService
{
 protected string $type;
 protected RecordRepository $repository;
 protected AccessPolicy $policy;
 public function __construct(protected Database $db,protected AuditService $audit){$this->repository=new RecordRepository($db);$this->policy=new AccessPolicy($db);}
 protected function validate(array $input,?array $old=null): array {
  $title=trim((string)($input['title']??''));$visibility=$input['visibility']??($old['visibility']??'internal');$category=trim((string)($input['category']??''));$tags=$input['tags']??[];if(is_string($tags))$tags=array_filter(array_map('trim',explode(',',$tags)));
  if($title===''||mb_strlen($title)>190||!in_array($visibility,['internal','client'],true)||mb_strlen($category)>190||!is_array($tags)||count($tags)>30)throw new HttpException(422,'Provide a title, valid visibility, and at most 30 tags.');
  foreach($tags as $tag)if(!is_string($tag)||mb_strlen($tag)>80)throw new HttpException(422,'Tags must be text of at most 80 characters.');
  $payload=$input['payload']??[];if(!is_array($payload)||strlen(json_encode($payload,JSON_THROW_ON_ERROR))>1024*1024)throw new HttpException(422,'Record content is too large or invalid.');
  return ['title'=>$title,'visibility'=>$visibility,'category'=>$category,'tags'=>array_values(array_unique($tags)),'payload'=>$payload];
 }
 protected function searchText(array $payload): string {if($this->type==='article')return html_entity_decode(strip_tags($payload['html']??''),ENT_QUOTES|ENT_HTML5,'UTF-8');if($this->type==='asset')$payload=array_merge($payload['values']??[],[$payload['notes']??'']);return implode(' ',array_map(fn($v)=>is_scalar($v)?(string)$v:'', $payload));}
 protected function afterSaved(Actor $actor,int $id): void {}
 public function get(Actor $actor,int $id): array {return $this->repository->get($actor,$id,$this->type);}
 public function list(Actor $actor,int $org,bool $trash=false,int $page=1): array {return $this->repository->list($actor,$org,$this->type,$trash,$page);}
 public function create(Actor $actor,int $organizationId,array $input): int {
  $this->policy->require($actor,'write',new RecordScope($organizationId));$data=$this->validate($input);
  return $this->db->transaction(function()use($actor,$organizationId,$data){$this->db->run('INSERT INTO records(organization_id,type,title,visibility,payload,category,tags,search_text) VALUES (?,?,?,?,?,?,?,?)',[$organizationId,$this->type,$data['title'],$data['visibility'],json_encode($data['payload'],JSON_THROW_ON_ERROR|JSON_PRESERVE_ZERO_FRACTION),$data['category'],json_encode($data['tags']),$this->searchText($data['payload'])]);$id=(int)$this->db->connection()->lastInsertId();$this->afterSaved($actor,$id);$this->audit->record($actor,'record.created',$this->type,$id,$organizationId);return $id;});
 }
 public function update(Actor $actor,int $id,array $input): void {
  $old=$this->get($actor,$id);$this->policy->require($actor,'write',new RecordScope((int)$old['organization_id'],$old['visibility']));$data=$this->validate($input,$old);
  $this->db->transaction(function()use($actor,$id,$old,$data){$this->db->run('UPDATE records SET title=?,visibility=?,payload=?,category=?,tags=?,search_text=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?',[$data['title'],$data['visibility'],json_encode($data['payload'],JSON_THROW_ON_ERROR|JSON_PRESERVE_ZERO_FRACTION),$data['category'],json_encode($data['tags']),$this->searchText($data['payload']),$id]);$this->audit->record($actor,'record.updated',$this->type,$id,(int)$old['organization_id'],['changed_fields'=>array_keys($data)]);});
 }
 public function archive(Actor $actor,int $id): void {$r=$this->get($actor,$id);$this->setDeleted($actor,$r,true);}
 public function restore(Actor $actor,int $id): void {$r=$this->repository->get($actor,$id,$this->type,true);$this->setDeleted($actor,$r,false);}
 private function setDeleted(Actor $actor,array $r,bool $delete): void {$this->policy->require($actor,'write',new RecordScope((int)$r['organization_id'],$r['visibility'],(bool)$r['deleted_at']));$this->db->transaction(function()use($actor,$r,$delete){$this->db->run('UPDATE records SET deleted_at=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?',[$delete?gmdate('Y-m-d H:i:s'):null,$r['id']]);$this->audit->record($actor,$delete?'record.archived':'record.restored',$this->type,(int)$r['id'],(int)$r['organization_id']);});}
}
