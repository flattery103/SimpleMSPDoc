<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\HttpException;
final class FieldValidator
{
 public function validate(array $definitions,array $values): array {
  $out=[];foreach($definitions as $f){if($f['archived'])continue;$id=(string)$f['id'];$v=$values[$id]??null;
   if($v===null||$v===''){if($f['required'])throw new HttpException(422,$f['name'].' is required.');$out[$id]=null;continue;}
   $valid=match($f['field_type']){
    'text','multiline'=>is_string($v)&&mb_strlen($v)<=($f['field_type']==='text'?1000:20000),
    'number'=>is_scalar($v)&&is_numeric($v)&&is_finite((float)$v),
    'boolean'=>in_array($v,[true,false,0,1,'0','1'],true),
    'date'=>is_string($v)&&preg_match('/^\d{4}-\d{2}-\d{2}$/',$v)&&($date=\DateTimeImmutable::createFromFormat('!Y-m-d',$v))&&$date->format('Y-m-d')===$v,
    'url'=>is_string($v)&&strlen($v)<=2000&&filter_var($v,FILTER_VALIDATE_URL)&&in_array(strtolower(parse_url($v,PHP_URL_SCHEME)??''),['http','https'],true),
    'choice'=>is_string($v)&&in_array($v,is_array($f['options'])?$f['options']:json_decode($f['options']??'[]',true),true),default=>false};
   if(!$valid)throw new HttpException(422,'Invalid value for '.$f['name'].'.');$out[$id]=match($f['field_type']){'number'=>(float)$v,'boolean'=>in_array($v,[true,1,'1'],true),default=>$v};
  }return $out;
 }
}
