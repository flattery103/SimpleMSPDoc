<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Relationships;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException,RecordRepository,RecordScope};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class RelationshipService
{
 public function __construct(private Database $db,private AuditService $audit){}
 public function link(Actor $actor,string $sourceType,int $sourceId,string $targetType,int $targetId): int {
  $repo=new RecordRepository($this->db);$s=$repo->get($actor,$sourceId,$sourceType);$t=$repo->get($actor,$targetId,$targetType);(new AccessPolicy($this->db))->require($actor,'write',new RecordScope((int)$s['organization_id'],$s['visibility']));if($sourceId===$targetId||$s['organization_id']!==$t['organization_id'])throw new HttpException(422,'Link different records within the same organization.');
  $low=min($sourceId,$targetId);$high=max($sourceId,$targetId);$existing=$this->db->one('SELECT id FROM relationships WHERE source_id=? AND target_id=?',[$low,$high]);if($existing)return (int)$existing['id'];
  return $this->db->transaction(function()use($actor,$low,$high,$s){$this->db->run('INSERT INTO relationships(source_id,target_id) VALUES (?,?)',[$low,$high]);$id=(int)$this->db->connection()->lastInsertId();$this->audit->record($actor,'relationship.created','relationship',$id,(int)$s['organization_id']);return $id;});
 }
 public function listVisible(Actor $actor,string $sourceType,int $sourceId): array {
  $repo=new RecordRepository($this->db);$repo->get($actor,$sourceId,$sourceType);$out=[];foreach($this->db->all('SELECT * FROM relationships WHERE source_id=? OR target_id=?',[$sourceId,$sourceId]) as $link){$id=(int)$link['source_id']===$sourceId?(int)$link['target_id']:(int)$link['source_id'];try{$r=$repo->get($actor,$id);$out[]=['relationship_id'=>(int)$link['id'],'id'=>$id,'type'=>$r['type'],'title'=>$r['title']];}catch(HttpException $e){if($e->status!==404)throw $e;}}return $out;
 }
 public function unlink(Actor $actor,int $sourceId,int $relationshipId): void {$repo=new RecordRepository($this->db);$r=$repo->get($actor,$sourceId);(new AccessPolicy($this->db))->require($actor,'write',new RecordScope((int)$r['organization_id'],$r['visibility']));if(!$this->db->one('SELECT id FROM relationships WHERE id=? AND (source_id=? OR target_id=?)',[$relationshipId,$sourceId,$sourceId]))throw new HttpException(404,'Relationship not found.');$this->db->transaction(function()use($actor,$relationshipId,$r){$this->db->run('DELETE FROM relationships WHERE id=?',[$relationshipId]);$this->audit->record($actor,'relationship.deleted','relationship',$relationshipId,(int)$r['organization_id']);});}
}
