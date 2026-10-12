<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Unit;
use PHPUnit\Framework\TestCase;
use SimpleMSPDoc\Modules\Integrations\ManagedFields;
final class ConnectorContractTest extends TestCase
{
 public function testManagedUpdatesPreserveManualFields(): void {self::assertSame(['hostname'=>'new','notes'=>'manual'],ManagedFields::merge(['hostname'=>'old','notes'=>'manual'],['hostname'=>'new','notes'=>'provider'],['hostname']));}
}
