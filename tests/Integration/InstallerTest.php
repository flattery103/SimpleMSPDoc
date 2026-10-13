<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Modules\Installation\Installer;
use SimpleMSPDoc\Modules\Installation\SetupAccess;
final class InstallerTest extends DatabaseTestCase
{
 private function uploadedKey(): string {$secret=bin2hex(random_bytes(32));file_put_contents($this->root.'/data/setup.key',(new SetupAccess($this->root))->fileContents($secret));return $secret;}
 public function testProtectedFreshInstallationAndLock(): void {
  $token=$this->uploadedKey();$i=new Installer($this->root,dirname(__DIR__,2).'/database/migrations');
  $settings=['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:''];
  try{$i->install($settings,['username'=>'admin','name'=>'Admin','password'=>'Long-enough-password'],'wrong');self::fail('Invalid token accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(403,$e->status);}
  self::assertSame([], $this->db->all('SHOW TABLES'));
  $i->install($settings,['username'=>'admin','name'=>'Admin','password'=>'Long-enough-password'],$token);
  self::assertFileExists($this->root.'/data/config.php');self::assertFileExists($this->root.'/data/installed.php');self::assertFileDoesNotExist($this->root.'/data/setup.key');
  self::assertSame('administrator',$this->db->one('SELECT role FROM users')['role']);
  self::assertTrue(password_verify('Long-enough-password',$this->db->one('SELECT password_hash FROM users')['password_hash']));
  try{$i->install($settings,[],$token);self::fail('Reinstallation accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(403,$e->status);}
 }
 public function testExistingDatabaseIsRefused(): void {
  $this->db->run('CREATE TABLE legacy(id INT)');$token=$this->uploadedKey();
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);
  (new Installer($this->root,dirname(__DIR__,2).'/database/migrations'))->install(['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:''],['username'=>'admin','name'=>'Admin','password'=>'Long-enough-password'],$token);
 }
}
