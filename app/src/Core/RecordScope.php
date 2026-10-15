<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final readonly class RecordScope
{
 public function __construct(public int $organizationId,public string $visibility='internal',public bool $deleted=false){}
}
