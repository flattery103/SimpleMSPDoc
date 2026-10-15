<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations\Action1;
final class SecretBox
{
 private string $key;
 public function __construct(string $applicationKey){$raw=base64_decode($applicationKey,true);if($raw===false||strlen($raw)!==32)throw new \RuntimeException('Invalid application key.');$this->key=hash_hkdf('sha256',$raw,32,'SimpleMSPDoc Action1 v1');}
 public function seal(array $value,string $purpose): string {$iv=random_bytes(12);$tag='';$cipher=openssl_encrypt(json_encode($value,JSON_THROW_ON_ERROR),'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,$iv,$tag,'action1:'.$purpose);if($cipher===false)throw new \RuntimeException('Cannot protect integration credentials.');return base64_encode($iv.$tag.$cipher);}
 public function open(string $value,string $purpose): array {$raw=base64_decode($value,true);if($raw===false||strlen($raw)<29)throw new \RuntimeException('Invalid integration credentials.');$plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16),'action1:'.$purpose);if($plain===false)throw new \RuntimeException('Cannot decrypt integration credentials.');$result=json_decode($plain,true,32,JSON_THROW_ON_ERROR);if(!is_array($result))throw new \RuntimeException('Invalid integration credentials.');return $result;}
}
