<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Files;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException,RecordRepository,RecordScope};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class AttachmentService
{
 public function __construct(private Database $db,private AuditService $audit,private string $storage,private int $limit=20971520){if(!is_dir($storage))mkdir($storage,0700,true);}
 public function upload(Actor $actor,string $parentType,int $parentId,array $upload): int {
  $r=(new RecordRepository($this->db))->get($actor,$parentId,$parentType);(new AccessPolicy($this->db))->require($actor,'write',new RecordScope((int)$r['organization_id'],$r['visibility']));$meta=(new UploadValidator($this->limit))->validate($upload);$storageName=bin2hex(random_bytes(32));$target=$this->storage.'/'.$storageName;
  if(!copy($upload['tmp_name'],$target))throw new \RuntimeException('Cannot store attachment.');chmod($target,0600);
  try{return $this->db->transaction(function()use($actor,$parentId,$r,$meta,$storageName){$this->db->run('INSERT INTO attachments(record_id,name,storage_name,mime,bytes,actor_id) VALUES (?,?,?,?,?,?)',[$parentId,$meta['name'],$storageName,$meta['mime'],$meta['bytes'],$actor->id]);$id=(int)$this->db->connection()->lastInsertId();$this->audit->record($actor,'attachment.created','attachment',$id,(int)$r['organization_id']);return $id;});}catch(\Throwable $e){unlink($target);throw $e;}
 }
 public function list(Actor $actor,int $recordId): array {(new RecordRepository($this->db))->get($actor,$recordId);return $this->db->all('SELECT id,name,mime,bytes,created_at FROM attachments WHERE record_id=? ORDER BY name',[$recordId]);}
 public function download(Actor $actor,int $attachmentId): array {$a=$this->db->one('SELECT * FROM attachments WHERE id=?',[$attachmentId]);if(!$a)throw new HttpException(404,'Attachment not found.');(new RecordRepository($this->db))->get($actor,(int)$a['record_id']);$a['path']=$this->storage.'/'.$a['storage_name'];if(!is_file($a['path']))throw new HttpException(404,'Attachment file is unavailable.');return $a;}
 public function delete(Actor $actor,int $id): void {$a=$this->download($actor,$id);$r=(new RecordRepository($this->db))->get($actor,(int)$a['record_id']);(new AccessPolicy($this->db))->require($actor,'write',new RecordScope((int)$r['organization_id'],$r['visibility']));$this->db->transaction(function()use($actor,$id,$r){$this->db->run('DELETE FROM attachments WHERE id=?',[$id]);$this->audit->record($actor,'attachment.deleted','attachment',$id,(int)$r['organization_id']);});unlink($a['path']);}
}
