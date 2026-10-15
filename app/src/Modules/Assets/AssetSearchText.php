<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
final class AssetSearchText
{
 public static function build(array $payload,array $activeFieldIds,array $fields=[]): string {
  $mapped=[];foreach($fields as $f)if(ConfigurationDetails::fieldKey($f)!==null)$mapped[$f['id']]=true;
  $parts=[];foreach($activeFieldIds as $id){if(isset($mapped[$id]))continue;$value=$payload['values'][$id]??null;if(is_scalar($value))$parts[]=(string)$value;}
  $parts[]=ConfigurationDetails::notes($payload,$fields);foreach(ConfigurationDetails::effective($payload,$fields) as $v)if(is_scalar($v))$parts[]=(string)$v;return implode(' ',$parts);
 }
}
