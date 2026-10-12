<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Organizations;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class OrganizationService
{
 public function __construct(private Database $db,private AuditService $audit){}
 public function listVisible(Actor $actor,bool $trash=false): array {
  $where='o.deleted_at IS '.($trash?'NOT NULL':'NULL');$params=[];if($trash)(new AccessPolicy($this->db))->admin($actor);
  if($actor->role!=='administrator'||$actor->audience!=='staff'){$where.=' AND EXISTS(SELECT 1 FROM user_organizations uo WHERE uo.organization_id=o.id AND uo.user_id=?)';$params[]=$actor->id;}
  return $this->db->all('SELECT o.* FROM organizations o WHERE '.$where.' ORDER BY o.name',$params);
 }
 public function get(Actor $actor,int $id): array {foreach($this->listVisible($actor) as $o)if((int)$o['id']===$id)return $o;throw new HttpException(404,'Organization not found.');}
 public function save(Actor $actor,?int $id,array $input): int {
  (new AccessPolicy($this->db))->admin($actor);$name=trim((string)($input['name']??''));$description=trim((string)($input['description']??''));if($name===''||mb_strlen($name)>190||strlen($description)>10000)throw new HttpException(422,'Organization name is required and description must be under 10,000 bytes.');
  return $this->db->transaction(function()use($actor,$id,$name,$description){if($id===null){$this->db->run('INSERT INTO organizations(name,description) VALUES (?,?)',[$name,$description]);$id=(int)$this->db->connection()->lastInsertId();}else{$this->get($actor,$id);$this->db->run('UPDATE organizations SET name=?,description=? WHERE id=?',[$name,$description,$id]);}$this->audit->record($actor,'organization.saved','organization',$id,$id);return $id;});
 }
 public function archive(Actor $actor,int $id): void {$this->setDeleted($actor,$id,true);}
 public function restore(Actor $actor,int $id): void {$this->setDeleted($actor,$id,false);}
 private function setDeleted(Actor $actor,int $id,bool $delete): void {(new AccessPolicy($this->db))->admin($actor);if(!$this->db->one('SELECT id FROM organizations WHERE id=?',[$id]))throw new HttpException(404,'Organization not found.');$this->db->transaction(function()use($actor,$id,$delete){$this->db->run('UPDATE organizations SET deleted_at=? WHERE id=?',[$delete?gmdate('Y-m-d H:i:s'):null,$id]);$this->audit->record($actor,$delete?'organization.archived':'organization.restored','organization',$id,$id);});}
}
