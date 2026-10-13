<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Core\Database;
abstract class DatabaseTestCase extends TestCase
{
 protected Database $db;
 protected string $root;
 private string $originalDsn;
 private string $databaseName;
 protected function setUp(): void {
  $dsn=getenv('TEST_DSN');if(!$dsn){self::markTestSkipped('TEST_DSN must name a disposable *_test database.');}
  if(!preg_match('/dbname=([a-zA-Z0-9_]+_test)(?:;|$)/',$dsn)) throw new \RuntimeException('Unsafe test database name');
  $this->originalDsn=$dsn;$this->databaseName='simplemspdoc_'.bin2hex(random_bytes(6)).'_test';
  $serverDsn=preg_replace('/dbname=[a-zA-Z0-9_]+;?/','',$dsn);$server=Database::connect(['dsn'=>$serverDsn,'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:'']);$server->run('CREATE DATABASE `'.$this->databaseName.'` CHARACTER SET utf8mb4');
  $dsn=preg_replace('/dbname=[a-zA-Z0-9_]+/','dbname='.$this->databaseName,$dsn);putenv('TEST_DSN='.$dsn);
  $this->db=Database::connect(['dsn'=>$dsn,'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:'']);
  $this->root=sys_get_temp_dir().'/mspdoc-test-'.bin2hex(random_bytes(8));mkdir($this->root.'/data',0700,true);
 }
 protected function tearDown(): void { if(isset($this->databaseName)){$this->db->run('DROP DATABASE `'.$this->databaseName.'`');putenv('TEST_DSN='.$this->originalDsn);} if(isset($this->root)){ $it=new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($this->root,\FilesystemIterator::SKIP_DOTS),\RecursiveIteratorIterator::CHILD_FIRST);foreach($it as $p){($p->isDir()&&!$p->isLink())?rmdir($p->getPathname()):unlink($p->getPathname());}rmdir($this->root); } }
 protected function schema(): void { foreach(require dirname(__DIR__,2).'/database/migrations/001_initial.php' as $sql)$this->db->run($sql);$m=require dirname(__DIR__,2).'/database/migrations/002_organization_passwords.php';foreach($m['sql'] as $sql)$this->db->run($sql);$this->db->run("INSERT INTO migrations(version) VALUES ('002_organization_passwords')"); }
 protected function user(string $role='administrator',string $audience='staff'): int { $this->db->run('INSERT INTO users (username,name,password_hash,role,audience) VALUES (?,?,?,?,?)',[uniqid('user'), 'Test User',password_hash('Correct-password-123',PASSWORD_DEFAULT),$role,$audience]);return (int)$this->db->connection()->lastInsertId(); }
 protected function org(string $name='Example'): int { $this->db->run('INSERT INTO organizations(name) VALUES (?)',[$name]);return (int)$this->db->connection()->lastInsertId(); }
}
