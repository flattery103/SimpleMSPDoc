<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations\Action1;
interface Transport {public function request(string $method,string $url,array $headers,?string $body=null): array;}
