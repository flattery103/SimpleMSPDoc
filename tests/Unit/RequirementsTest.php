<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Unit;
use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Modules\Installation\Requirements;
final class RequirementsTest extends TestCase
{
    public function testVersionFloorAndMissingExtensions(): void
    {
        $extensions = ['pdo_mysql','mbstring','dom','fileinfo','openssl'];
        self::assertFalse(Requirements::check(['php'=>'8.2.9','extensions'=>$extensions])['PHP 8.3 or newer']);
        self::assertTrue(Requirements::check(['php'=>'8.3.0','extensions'=>$extensions])['PHP 8.3 or newer']);
        self::assertFalse(Requirements::check(['php'=>'8.3.0','extensions'=>[]])['pdo_mysql']);
    }
}
