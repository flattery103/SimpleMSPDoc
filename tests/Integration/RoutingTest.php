<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Core\Url;
use SimpleMSPDoc\Core\ErrorHandler;
final class RoutingTest extends TestCase
{
    public function testSubpathAndSafeErrors(): void
    {
        self::assertSame('/docs/assets/app.css', (new Url('/docs/index.php'))->to('/assets/app.css'));
        self::assertSame('/login', (new Url('/index.php'))->to('/login'));
        self::assertSame('An unexpected error occurred. Check the application log.', ErrorHandler::message(new \RuntimeException('password=secret')));
    }
}
