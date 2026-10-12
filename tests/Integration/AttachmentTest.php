<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Organizations\LocationService;
use SimpleMSPDoc\Modules\Files\{AttachmentService,UploadValidator};
final class AttachmentTest extends DatabaseTestCase
{
 public function testDownloadsInheritParentVisibilityAndArchive(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$c=new Actor($this->user('read-only','client'),'read-only','client');$org=$this->org();$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$c->id,$org]);$audit=new AuditService($this->db);$s=new LocationService($this->db,$audit);$parent=$s->create($a,$org,['title'=>'Private']);$f=new AttachmentService($this->db,$audit,$this->root.'/var/uploads');$tmp=$this->root.'/upload';file_put_contents($tmp,'Hello documentation');$id=$f->upload($a,'location',$parent,['tmp_name'=>$tmp,'name'=>'notes.txt','error'=>0,'size'=>19]);self::assertSame('Hello documentation',file_get_contents($f->download($a,$id)['path']));
  try{$f->download($c,$id);self::fail('Internal file leaked');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(404,$e->status);}
  $s->update($a,$parent,['title'=>'Shared','visibility'=>'client']);self::assertSame('notes.txt',$f->download($c,$id)['name']);$s->archive($a,$parent);$this->expectException(\SimpleMSPDoc\Core\HttpException::class);$f->download($a,$id);
 }
 public function testTraversalExecutableAndOversizedUploadsRejected(): void {
  $tmp=$this->root.'/input';file_put_contents($tmp,'hello');$v=new UploadValidator(4);foreach(['../x.txt','evil.php.txt','x.svg','x.txt'] as $name){try{$v->validate(['tmp_name'=>$tmp,'name'=>$name,'error'=>0]);self::fail('Unsafe file accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(422,$e->status);}}
 }
}
