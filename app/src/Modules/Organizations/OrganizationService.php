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
  $columns=$this->db->one("SELECT version FROM migrations WHERE version='002_organization_passwords'")?'o.website,o.phone,o.address,o.notes,o.logo_mime,':"'' AS website,'' AS phone,'' AS address,'' AS notes,NULL AS logo_mime,";
  return $this->db->all('SELECT o.id,o.name,o.description,'.$columns.'o.deleted_at,o.created_at FROM organizations o WHERE '.$where.' ORDER BY o.name',$params);
 }
 public function get(Actor $actor,int $id): array {foreach($this->listVisible($actor) as $o)if((int)$o['id']===$id)return $o;throw new HttpException(404,'Organization not found.');}
 public function save(Actor $actor,?int $id,array $input): int {
  (new AccessPolicy($this->db))->admin($actor);$name=trim((string)($input['name']??''));$description=trim((string)($input['description']??''));if($name===''||mb_strlen($name)>190||strlen($description)>10000)throw new HttpException(422,'Organization name is required and description must be under 10,000 bytes.');
  $details=[];foreach(['website'=>2048,'phone'=>100,'address'=>2000,'notes'=>10000] as $field=>$limit){$details[$field]=trim((string)($input[$field]??''));if(strlen($details[$field])>$limit)throw new HttpException(422,'Organization detail is too long.');}if($details['website']!==''&&(!filter_var($details['website'],FILTER_VALIDATE_URL)||!in_array(strtolower(parse_url($details['website'],PHP_URL_SCHEME)??''),['http','https'],true)))throw new HttpException(422,'Website must use http or https.');
  return $this->db->transaction(function()use($actor,$id,$name,$description,$details){if($id===null){$this->db->run('INSERT INTO organizations(name,description) VALUES (?,?)',[$name,$description]);$id=(int)$this->db->connection()->lastInsertId();}else{$this->get($actor,$id);$this->db->run('UPDATE organizations SET name=?,description=? WHERE id=?',[$name,$description,$id]);}$this->db->run('UPDATE organizations SET website=?,phone=?,address=?,notes=? WHERE id=?',[...array_values($details),$id]);$this->audit->record($actor,'organization.saved','organization',$id,$id);return $id;});
 }
 public function setLogo(Actor $actor,int $id,?string $bytes): void {
  (new AccessPolicy($this->db))->admin($actor);$this->get($actor,$id);$mime=null;
  if($bytes!==null){if(strlen($bytes)>1048576)throw new HttpException(422,'Logo must be 1 MiB or smaller.');$info=@getimagesizefromstring($bytes);if(!$info||$info[0]>4096||$info[1]>4096||!in_array($info['mime'],['image/png','image/jpeg','image/webp'],true))throw new HttpException(422,'Use a PNG, JPEG or WebP logo up to 4096 pixels.');$mime=$info['mime'];}
  $this->db->run('UPDATE organizations SET logo_bytes=?,logo_mime=? WHERE id=?',[$bytes,$mime,$id]);$this->audit->record($actor,'organization.logo_changed','organization',$id,$id);
 }
 public function logo(Actor $actor,int $id): array {$this->get($actor,$id);$r=$this->db->one('SELECT logo_bytes,logo_mime FROM organizations WHERE id=?',[$id]);if(!$r['logo_mime'])throw new HttpException(404,'Logo not found.');return $r;}
 public function archive(Actor $actor,int $id): void {$this->setDeleted($actor,$id,true);}
 public function restore(Actor $actor,int $id): void {$this->setDeleted($actor,$id,false);}
 private function setDeleted(Actor $actor,int $id,bool $delete): void {(new AccessPolicy($this->db))->admin($actor);if(!$this->db->one('SELECT id FROM organizations WHERE id=?',[$id]))throw new HttpException(404,'Organization not found.');$this->db->transaction(function()use($actor,$id,$delete){$this->db->run('UPDATE organizations SET deleted_at=? WHERE id=?',[$delete?gmdate('Y-m-d H:i:s'):null,$id]);$this->audit->record($actor,$delete?'organization.archived':'organization.restored','organization',$id,$id);});}
}
