<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Modules\Installation\Installer;
final class InstallerTest extends DatabaseTestCase
{
 public function testProtectedFreshInstallationAndLock(): void {
  $i=new Installer($this->root,dirname(__DIR__,2).'/app/database/migrations');
  $settings=['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:''];
  self::assertSame([], $this->db->all('SHOW TABLES'));
  $i->install($settings,['username'=>'admin','name'=>'Admin','password'=>'Long-enough-password']);
  self::assertFileExists($this->root.'/data/config.php');self::assertFileExists($this->root.'/data/installed.php');self::assertFileDoesNotExist($this->root.'/data/setup.key');
  self::assertSame('administrator',$this->db->one('SELECT role FROM users')['role']);
  self::assertTrue(password_verify('Long-enough-password',$this->db->one('SELECT password_hash FROM users')['password_hash']));
  try{$i->install($settings,[]);self::fail('Reinstallation accepted');}catch(\SimpleMSPDoc\Core\HttpException $e){self::assertSame(403,$e->status);}
 }
 public function testExistingDatabaseIsRefused(): void {
  $this->db->run('CREATE TABLE legacy(id INT)');
  $this->expectException(\SimpleMSPDoc\Core\HttpException::class);
  (new Installer($this->root,dirname(__DIR__,2).'/app/database/migrations'))->install(['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:''],['username'=>'admin','name'=>'Admin','password'=>'Long-enough-password']);
 }
 public function testFreshInstallCanSelectOneCharacterMinimum(): void {
  (new Installer($this->root,dirname(__DIR__,2).'/app/database/migrations'))->install(['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:''],['username'=>'admin','name'=>'Admin','password_minimum'=>'1','password'=>'x']);self::assertSame(1,(int)$this->db->one('SELECT password_minimum FROM application_settings')['password_minimum']);self::assertTrue(password_verify('x',$this->db->one('SELECT password_hash FROM users')['password_hash']));
 }
}
