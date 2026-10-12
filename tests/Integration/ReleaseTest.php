<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Core\ReleaseBuilder;
final class ReleaseTest extends TestCase
{
 public function testZipContainsProductionDependenciesAndNoLegacyOrSecrets(): void {
  $file=sys_get_temp_dir().'/mspdoc-release-'.bin2hex(random_bytes(5)).'.zip';
  try{(new ReleaseBuilder(dirname(__DIR__,2)))->build('2.0.0-alpha.1',$file);$zip=new \ZipArchive();self::assertTrue($zip->open($file));$prefix='SimpleMSPDoc-2.0.0-alpha.1/';$zip->getExternalAttributesName($prefix.'config/',$opsys,$attrs);self::assertSame(0750,($attrs>>16)&0777,'Private config directory permissions');$zip->getExternalAttributesName($prefix.'var/',$opsys,$attrs);self::assertSame(0750,($attrs>>16)&0777,'Private storage directory permissions');foreach(['public/index.php','vendor/autoload.php','vendor/spomky-labs/otphp/src/TOTP.php','public/assets/app.css','public/assets/app.js','scripts/setup-token.php','manifest.json'] as $name)self::assertNotFalse($zip->locateName($prefix.$name),$name);
   foreach(['config/local.php','index.php','install.php','admin.php','tests/Integration/AuthTest.php','vendor/phpunit/phpunit/phpunit'] as $name)self::assertFalse($zip->locateName($prefix.$name),$name);
   self::assertStringContainsString('not backward-compatible',$zip->getFromName($prefix.'README.md'));$zip->close();
  }finally{if(is_file($file))unlink($file);}
 }
}
