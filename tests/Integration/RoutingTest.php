<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Core\Url;
use SimpleMSPDoc\Core\ErrorHandler;
final class RoutingTest extends TestCase
{
 public function testRoutesUseRealEntryFileAndAssetsStayDirect(): void {
  self::assertSame('/docs/assets/app.css',(new Url('/docs/index.php'))->to('/assets/app.css'));
  self::assertSame('/index.php?route=%2Flogin',(new Url('/index.php'))->to('/login'));
  self::assertSame('/docs/index.php?route=%2Fsearch&q=a%26b&page=2',(new Url('/docs/index.php'))->to('/search?q=a%26b&page=2'));
  self::assertSame('An unexpected error occurred. Check the application log.',ErrorHandler::message(new \RuntimeException('password=secret')));
 }
}
