<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
final class SetupAccess
{
 public function __construct(private string $root){}
 public function fileContents(string $secret): string {return "SIMPLEMSPDOC_SETUP_KEY\n".hash('sha256',$secret)."\n";}
 public function verify(string $secret): bool {$path=$this->root.'/data/setup.key';return strlen($secret)===64&&is_file($path)&&filesize($path)<256&&hash_equals($this->fileContents($secret),file_get_contents($path));}
}
