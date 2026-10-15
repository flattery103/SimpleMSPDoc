<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations;
interface Connector { public function sync(array $connection,array $cursor): SyncResult; }
