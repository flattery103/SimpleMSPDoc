<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Unit;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use PHPUnit\Framework\TestCase;
final class LayoutTest extends TestCase
{
 public function testCheckoutUsesInstalledWebsiteLayout(): void {
  $root=dirname(__DIR__,2);
  foreach(['index.php','app/src/Core/Application.php','app/templates/layout.php','app/database/migrations/001_initial.php','app/composer.json','assets/app.js','manifest.json','data/index.php'] as $path)self::assertFileExists($root.'/'.$path,$path);
  foreach(['src','templates','database','public'] as $path)self::assertDirectoryDoesNotExist($root.'/'.$path,$path);
 }
}
