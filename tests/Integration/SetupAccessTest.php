<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Modules\Installation\SetupAccess;
final class SetupAccessTest extends DatabaseTestCase
{
 public function testDownloadCannotAuthorizeWithoutAnUploadedFile(): void {
  $s=new SetupAccess($this->root);$secret=bin2hex(random_bytes(32));$file=$s->fileContents($secret);self::assertFileDoesNotExist($this->root.'/data/setup.key');self::assertFalse($s->verify($secret));
  file_put_contents($this->root.'/data/setup.key',$file);self::assertTrue($s->verify($secret));self::assertFalse($s->verify(bin2hex(random_bytes(32))));self::assertStringNotContainsString($secret,$file);
 }
 public function testExampleAndMalformedKeysDoNotEnableSetup(): void {$s=new SetupAccess($this->root);foreach(['','example','SIMPLEMSPDOC_SETUP_KEY\nnot-a-hash'] as $v){file_put_contents($this->root.'/data/setup.key',$v);self::assertFalse($s->verify('secret'));}}
}
