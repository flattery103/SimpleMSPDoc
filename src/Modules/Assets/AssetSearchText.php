<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
final class AssetSearchText
{
 public static function build(array $payload,array $activeFieldIds): string {
  $parts=[];foreach($activeFieldIds as $id){$value=$payload['values'][$id]??null;if(is_scalar($value))$parts[]=(string)$value;}
  $parts[]=(string)($payload['notes']??'');return implode(' ',$parts);
 }
}
