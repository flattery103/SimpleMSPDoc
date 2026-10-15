<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Articles\ArticleService;
final class ArticleTest extends DatabaseTestCase
{
 public function testSanitizedHtmlRevisionRestoreAndConflict(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$org=$this->org();$s=new ArticleService($this->db,new AuditService($this->db),$this->root.'/data');
  $id=$s->create($a,$org,['title'=>'Guide','payload'=>['html'=>'<p onclick="evil()">Hello<script>alert(1)</script><a href="javascript:evil()">link</a></p>'],'tags'=>'recovery, server']);$r=$s->get($a,$id);self::assertStringNotContainsString('onclick',$r['payload']['html']);self::assertStringNotContainsString('<script',$r['payload']['html']);self::assertStringNotContainsString('javascript:',$r['payload']['html']);$rev=$s->history($a,$id)[0]['id'];
  $s->update($a,$id,['title'=>'New guide','payload'=>['html'=>'<p>New content</p>']],1);
  try{$s->update($a,$id,['title'=>'Stale edit','payload'=>['html'=>'bad']],1);self::fail('Stale edit accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(409,$e->status);}
  self::assertSame('New guide',$s->get($a,$id)['title']);$s->restoreRevision($a,$id,(int)$rev,2);self::assertSame('Guide',$s->get($a,$id)['title']);self::assertCount(3,$s->history($a,$id));
 }
 public function testRevisionCannotBeReadByClientOrBorrowedFromAnotherArticle(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$org=$this->org();$s=new ArticleService($this->db,new AuditService($this->db),$this->root.'/data');$one=$s->create($a,$org,['title'=>'Internal']);$two=$s->create($a,$org,['title'=>'Another']);$rev=$s->history($a,$two)[0]['id'];
  try{$s->restoreRevision($a,$one,(int)$rev,1);self::fail('Borrowed revision accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(404,$e->status);}
  $client=new Actor($this->user('read-only','client'),'read-only','client');$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$client->id,$org]);$this->expectException(\SimpleMSPDoc\Core\HttpException::class);$s->history($client,$one);
 }
}
