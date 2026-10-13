<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations;
final readonly class SyncResult
{
 /** Records contain external_id:string, managed_fields:string[], values:array. No provider connector ships yet. */
 public function __construct(public array $records,public array $cursor,public string $status){}
}
