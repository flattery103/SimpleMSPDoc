<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class ProtectedFile
{
 public const HEADER="<?php http_response_code(404); exit; __halt_compiler();\n";
 public static function ensureDirectory(string $directory): void {if(!is_dir($directory))mkdir($directory,0700,true);$index=$directory.'/index.php';if(!is_file($index)){file_put_contents($index,self::HEADER,LOCK_EX);chmod($index,0600);}}
 public static function write(string $path,string $data,bool $append=false): void {
  if(!is_dir(dirname($path)))mkdir(dirname($path),0700,true);$h=fopen($path,'c+b');if(!$h)throw new \RuntimeException('Cannot open protected storage.');
  try{if(!flock($h,LOCK_EX))throw new \RuntimeException('Cannot lock protected storage.');if($append&&fstat($h)['size']>0){if(fread($h,strlen(self::HEADER))!==self::HEADER)throw new \RuntimeException('Invalid protected storage.');fseek($h,0,SEEK_END);}else{rewind($h);ftruncate($h,0);fwrite($h,self::HEADER);}fwrite($h,$data);fflush($h);chmod($path,0600);}finally{flock($h,LOCK_UN);fclose($h);}
 }
 public static function read(string $path): string {$h=self::open($path);try{return stream_get_contents($h);}finally{fclose($h);}}
 public static function open(string $path): mixed {$h=fopen($path,'rb');if(!$h||fread($h,strlen(self::HEADER))!==self::HEADER){if($h)fclose($h);throw new \RuntimeException('Invalid protected file.');}return $h;}
 public static function copyFrom(string $source,string $target): void {$out=fopen($target,'xb');$in=fopen($source,'rb');if(!$out||!$in)throw new \RuntimeException('Cannot store attachment.');try{fwrite($out,self::HEADER);stream_copy_to_stream($in,$out);chmod($target,0600);}finally{fclose($in);fclose($out);}}
}
