<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class ProtectedSessionHandler implements \SessionHandlerInterface,\SessionUpdateTimestampHandlerInterface
{
 private mixed $handle=null;
 private ?string $activeId=null;
 public function __construct(private string $directory){ProtectedFile::ensureDirectory($directory);}
 private function path(string $id): string {return $this->directory.'/'.hash('sha256',$id).'.php';}
 public function open(string $path,string $name): bool {return is_writable($this->directory);}
 public function close(): bool {if(is_resource($this->handle)){flock($this->handle,LOCK_UN);fclose($this->handle);}$this->handle=null;$this->activeId=null;return true;}
 public function read(string $id): string|false {
  $this->close();$this->handle=fopen($this->path($id),'c+b');if(!$this->handle||!flock($this->handle,LOCK_EX))return false;$this->activeId=$id;
  if(fstat($this->handle)['size']===0)return '';
  if(fread($this->handle,strlen(ProtectedFile::HEADER))!==ProtectedFile::HEADER)return false;return stream_get_contents($this->handle);
 }
 public function write(string $id,string $data): bool {
  if($this->activeId!==$id||!is_resource($this->handle)){if($this->read($id)===false)return false;}
  rewind($this->handle);ftruncate($this->handle,0);fwrite($this->handle,ProtectedFile::HEADER.$data);fflush($this->handle);chmod($this->path($id),0600);return true;
 }
 public function destroy(string $id): bool {$this->close();return !is_file($this->path($id))||unlink($this->path($id));}
 public function gc(int $max_lifetime): int|false {$removed=0;foreach(glob($this->directory.'/*.php') as $path){if(basename($path)==='index.php')continue;if(filemtime($path)>=time()-$max_lifetime)continue;$h=fopen($path,'r+b');if($h){if(flock($h,LOCK_EX|LOCK_NB)){if(unlink($path))$removed++;flock($h,LOCK_UN);}fclose($h);}}return $removed;}
 public function validateId(string $id): bool {return is_file($this->path($id));}
 public function updateTimestamp(string $id,string $data): bool {return is_file($this->path($id))&&touch($this->path($id));}
}
