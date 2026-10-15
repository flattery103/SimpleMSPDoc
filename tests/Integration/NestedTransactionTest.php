<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

final class NestedTransactionTest extends DatabaseTestCase
{
 public function testNestedServiceWritesRollbackWithOuterTransaction(): void {$this->schema();try{$this->db->transaction(function(){$this->db->transaction(fn()=>$this->db->run("INSERT INTO organizations(name) VALUES ('Nested write')"));throw new \RuntimeException('Outer failure');});self::fail('Outer failure not propagated');}catch(\RuntimeException $e){self::assertSame('Outer failure',$e->getMessage());}self::assertSame(0,(int)$this->db->one('SELECT COUNT(*) AS n FROM organizations')['n']);}
}
