<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\{ProtectedFile,ProtectedSessionHandler};
final class ProtectedStorageTest extends DatabaseTestCase
{
 public function testStoredFilesArePhpGuardedAndSessionsRoundTrip(): void {
  $path=$this->root.'/data/private.php';ProtectedFile::write($path,'private payload <?php invalid code');self::assertStringStartsWith('<?php',file_get_contents($path));self::assertStringContainsString('__halt_compiler();',file_get_contents($path));self::assertSame('private payload <?php invalid code',ProtectedFile::read($path));
  $h=new ProtectedSessionHandler($this->root.'/data/sessions');$h->open('','');self::assertFalse($h->validateId('example-session'));self::assertSame('',$h->read('example-session'));self::assertTrue($h->write('example-session','secret-session-data'));$h->close();self::assertTrue($h->validateId('example-session'));self::assertSame('secret-session-data',$h->read('example-session'));$h->close();self::assertTrue($h->destroy('example-session'));
 }
}
