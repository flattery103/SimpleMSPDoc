<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Articles;
use SimpleMSPDoc\Core\{Actor,Database,HttpException,RecordScope,RecordService};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class ArticleService extends RecordService
{
 protected string $type='article';
 private HtmlSanitizer $sanitizer;
 public function __construct(Database $db,AuditService $audit,string $cache){parent::__construct($db,$audit);$this->sanitizer=new HtmlSanitizer($cache);}
 protected function validate(array $input,?array $old=null): array {$d=parent::validate($input,$old);$html=$d['payload']['html']??'';if(!is_string($html))throw new HttpException(422,'Article content must be text.');$d['payload']=['html'=>$this->sanitizer->clean($html)];return $d;}
 protected function afterSaved(Actor $actor,int $id): void {$r=$this->get($actor,$id);$this->db->run('INSERT INTO revisions(record_id,number,actor_id,snapshot) VALUES (?,?,?,?)',[$id,$r['revision'],$actor->id,json_encode($r,JSON_THROW_ON_ERROR)]);}
 public function update(Actor $actor,int $id,array $input,?int $expectedRevision=null): void {
  if($expectedRevision===null)throw new HttpException(422,'Article revision is required.');
  $this->db->transaction(function()use($actor,$id,$input,$expectedRevision){$this->db->one('SELECT id FROM records WHERE id=? FOR UPDATE',[$id]);$old=$this->get($actor,$id);$this->policy->require($actor,'write',new RecordScope((int)$old['organization_id'],$old['visibility']));if((int)$old['revision']!==$expectedRevision)throw new HttpException(409,'This article changed since you opened it. Reload before saving.');$d=$this->validate($input,$old);
   $this->db->run('UPDATE records SET title=?,visibility=?,payload=?,category=?,tags=?,search_text=?,revision=revision+1,updated_at=UTC_TIMESTAMP() WHERE id=?',[$d['title'],$d['visibility'],json_encode($d['payload'],JSON_THROW_ON_ERROR),$d['category'],json_encode($d['tags']),$this->searchText($d['payload']),$id]);$this->afterSaved($actor,$id);$this->audit->record($actor,'article.updated','article',$id,(int)$old['organization_id'],['revision'=>$expectedRevision+1]);});
 }
 public function history(Actor $actor,int $id): array {$this->get($actor,$id);return $this->db->all('SELECT v.id,v.number,v.created_at,u.name AS author FROM revisions v JOIN users u ON u.id=v.actor_id WHERE v.record_id=? ORDER BY v.number DESC',[$id]);}
 public function revision(Actor $actor,int $articleId,int $revisionId): array {$this->get($actor,$articleId);$r=$this->db->one('SELECT * FROM revisions WHERE id=? AND record_id=?',[$revisionId,$articleId]);if(!$r)throw new HttpException(404,'Revision not found.');$r['snapshot']=json_decode($r['snapshot'],true,512,JSON_THROW_ON_ERROR);return $r;}
 public function restoreRevision(Actor $actor,int $articleId,int $revisionId,int $expectedRevision): void {$r=$this->revision($actor,$articleId,$revisionId);$this->update($actor,$articleId,$r['snapshot'],$expectedRevision);}
}
