<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\Actor;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Installation\{MigrationRunner,Maintenance};
final class UpgradeTest extends DatabaseTestCase
{
 private function runner(): MigrationRunner {mkdir($this->root.'/migrations');return new MigrationRunner($this->db,new AuditService($this->db),$this->root.'/migrations',$this->root.'/var');}
 public function testBackupRequiredAndSuccessfulMigrationsNotRepeated(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$r=$this->runner();file_put_contents($this->root.'/migrations/002_example.php',"<?php return ['sql'=>['CREATE TABLE example(id INT)'],'checks'=>['SELECT COUNT(*)=1 FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name=\'example\'']];");
  try{$r->apply($a,false);self::fail('Backup acknowledgment missing');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(422,$e->status);}
  self::assertCount(1,$r->pending());$r->apply($a,true);self::assertSame([],$r->pending());self::assertFalse((new Maintenance($this->db))->active());$r->apply($a,true);self::assertSame(1,(int)$this->db->one("SELECT COUNT(*) AS n FROM migrations WHERE version='002_example'")['n']);
 }
 public function testPartialDdlFailureKeepsMaintenanceAndNoSuccessRecord(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$r=$this->runner();file_put_contents($this->root.'/migrations/002_broken.php',"<?php return ['sql'=>['CREATE TABLE partial(id INT)','INVALID SQL'],'checks'=>[]];");
  try{$r->apply($a,true);self::fail('Broken migration passed');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(503,$e->status);self::assertStringContainsString('restore',$e->getMessage());}
  self::assertTrue((new Maintenance($this->db))->active());self::assertNull($this->db->one("SELECT * FROM migrations WHERE version='002_broken'"));self::assertNotNull($this->db->one("SELECT * FROM information_schema.tables WHERE table_schema=DATABASE() AND table_name='partial'"));
 }
 public function testNonAdminAndConcurrentUpgradeRejected(): void {
  $this->schema();$a=new Actor($this->user(),'administrator','staff');$r=$this->runner();$other=\SimpleMSPDoc\Core\Database::connect(['dsn'=>getenv('TEST_DSN'),'username'=>'root','password'=>'']);$other->run('SELECT GET_LOCK(?,0)',[$r->lockName()]);
  try{$r->apply($a,true);self::fail('Concurrent lock ignored');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(409,$e->status);}finally{$other->run('SELECT RELEASE_LOCK(?)',[$r->lockName()]);}
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);$r->apply(new Actor($this->user('technician'),'technician','staff'),true);
 }
}
