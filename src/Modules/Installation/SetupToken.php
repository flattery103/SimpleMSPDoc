<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
final class SetupToken
{
 public function __construct(private string $root) {}
 public static function generate(string $root): string {
  if(is_file($root.'/config/local.php')||is_file($root.'/var/installed.lock'))throw new \RuntimeException('Installation is locked.');
  if(!is_dir($root.'/var'))mkdir($root.'/var',0700,true);
  $token=bin2hex(random_bytes(32));$path=$root.'/var/setup-token';$h=fopen($path,'x');if(!$h)throw new \RuntimeException('Setup token already exists.');chmod($path,0600);fwrite($h,hash('sha256',$token));fclose($h);return $token;
 }
 public function verify(string $candidate): bool { $p=$this->root.'/var/setup-token';return is_file($p)&&strlen($candidate)===64&&hash_equals(trim(file_get_contents($p)),hash('sha256',$candidate)); }
}
