<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations;
final class ManagedFields
{
 public static function merge(array $existing,array $incoming,array $managed): array {foreach($managed as $key)if(array_key_exists($key,$incoming))$existing[$key]=$incoming[$key];return $existing;}
}
