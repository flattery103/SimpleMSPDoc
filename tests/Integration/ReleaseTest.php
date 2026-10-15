<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Core\ReleaseBuilder;
final class ReleaseTest extends TestCase
{
 public function testZipContainsProductionDependenciesAndNoLegacyOrSecrets(): void {
  $file=sys_get_temp_dir().'/mspdoc-release-'.bin2hex(random_bytes(5)).'.zip';
  try{(new ReleaseBuilder(dirname(__DIR__,2)))->build('2.0.0-alpha.5',$file);$zip=new \ZipArchive();self::assertTrue($zip->open($file));$prefix='';$zip->getExternalAttributesName('data/',$opsys,$attrs);self::assertSame(0750,($attrs>>16)&0777,'Private data directory permissions');foreach(['index.php','app/vendor/autoload.php','app/vendor/spomky-labs/otphp/src/TOTP.php','assets/app.css','assets/app.js','docs/installation.md','manifest.json'] as $name)self::assertNotFalse($zip->locateName($prefix.$name),$name);
   foreach(['data/config.php','public/index.php','install.php','admin.php','scripts/setup-token.php','tests/Integration/AuthTest.php','app/vendor/phpunit/phpunit/phpunit'] as $name)self::assertFalse($zip->locateName($prefix.$name),$name);
   self::assertStringContainsString('not backward-compatible',$zip->getFromName($prefix.'README.md'));$zip->close();
  }finally{if(is_file($file))unlink($file);}
 }
}
