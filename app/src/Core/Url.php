<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class Url
{
 public readonly string $base;
 public function __construct(string $scriptName){$this->base=rtrim(str_replace('\\','/',dirname($scriptName)),'/.');}
 public function to(string $path): string {
  if(str_starts_with($path,'/assets/'))return $this->base.$path;
  $parts=explode('?',$path,2);$query=[];if(isset($parts[1]))parse_str($parts[1],$query);unset($query['route']);
  return $this->base.'/index.php?'.http_build_query(['route'=>$parts[0]]+$query,'','&',PHP_QUERY_RFC3986);
 }
}
