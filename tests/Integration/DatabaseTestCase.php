<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Core\Database;
abstract class DatabaseTestCase extends TestCase
{
 protected Database $db;
 protected string $root;
 protected function setUp(): void {
  $dsn=getenv('TEST_DSN');if(!$dsn){self::markTestSkipped('TEST_DSN must name a disposable *_test database.');}
  if(!preg_match('/dbname=([a-zA-Z0-9_]+_test)(?:;|$)/',$dsn)) throw new \RuntimeException('Unsafe test database name');
  $this->db=Database::connect(['dsn'=>$dsn,'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:'']);
  $this->db->run('SET FOREIGN_KEY_CHECKS=0');foreach($this->db->all('SHOW TABLES') as $t){$name=array_values($t)[0];$this->db->run('DROP TABLE `'.str_replace('`','``',$name).'`');}$this->db->run('SET FOREIGN_KEY_CHECKS=1');
  $this->root=sys_get_temp_dir().'/mspdoc-test-'.bin2hex(random_bytes(8));mkdir($this->root.'/config',0700,true);mkdir($this->root.'/var',0700,true);
 }
 protected function tearDown(): void { if(isset($this->root)){ $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $p){$p->isDir()?rmdir($p->getPathname()):unlink($p->getPathname());}rmdir($this->root); } }
 protected function schema(): void { foreach(require dirname(__DIR__,2).'/database/migrations/001_initial.php' as $sql)$this->db->run($sql); }
 protected function user(string $role='administrator',string $audience='staff'): int { $this->db->run('INSERT INTO users (username,name,password_hash,role,audience) VALUES (?,?,?,?,?)',[uniqid('user'), 'Test User',password_hash('Correct-password-123',PASSWORD_DEFAULT),$role,$audience]);return (int)$this->db->connection()->lastInsertId(); }
 protected function org(string $name='Example'): int { $this->db->run('INSERT INTO organizations(name) VALUES (?)',[$name]);return (int)$this->db->connection()->lastInsertId(); }
}
