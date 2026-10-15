<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Passwords;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException,RecordScope,RecordRepository};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class PasswordService
{
 private string $key;
 public function __construct(private Database $db,private AuditService $audit,string $key){$raw=base64_decode($key,true);if($raw===false||strlen($raw)!==32)throw new \RuntimeException('Invalid application key.');$this->key=hash_hkdf('sha256',$raw,32,'SimpleMSPDoc passwords v1');}
 private function scope(array $p): RecordScope {return new RecordScope((int)$p['organization_id'],$p['visibility'],$p['deleted_at']!==null);}
 private function raw(Actor $a,int $id,string $action='read'): array {$p=$this->db->one('SELECT * FROM passwords WHERE id=? AND deleted_at IS NULL',[$id]);if(!$p)throw new HttpException(404,'Password not found.');(new AccessPolicy($this->db))->require($a,$action,$this->scope($p));if($p['configuration_id']){$r=(new RecordRepository($this->db))->get($a,(int)$p['configuration_id'],'asset');if((int)$r['organization_id']!==(int)$p['organization_id'])throw new HttpException(404,'Password not found.');}return $p;}
 public function get(Actor $a,int $id): array {$p=$this->raw($a,$id);unset($p['secret_cipher']);return $p;}
 public function list(Actor $a,int $org,?int $configuration=null): array {(new AccessPolicy($this->db))->require($a,'read',new RecordScope($org,$a->audience==='client'?'client':'internal'));$rows=$this->db->all('SELECT id FROM passwords WHERE organization_id=? AND deleted_at IS NULL ORDER BY title,id',[$org]);$result=[];foreach($rows as $p){try{$p=$this->get($a,(int)$p['id']);if($configuration===null||(int)$p['configuration_id']===$configuration)$result[]=$p;}catch(HttpException $e){if($e->status!==404)throw $e;}}return $result;}
 private function aad(int $org,int $id): string {return 'simplemspdoc:password:v1:'.$org.':'.$id;}
 private function encrypt(string $secret,int $org,int $id): string {$iv=random_bytes(12);$tag='';$cipher=openssl_encrypt($secret,'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,$iv,$tag,$this->aad($org,$id));if($cipher===false)throw new \RuntimeException('Password encryption failed.');return base64_encode($iv.$tag.$cipher);}
 public function reveal(Actor $a,int $id): string {$p=$this->raw($a,$id);$raw=base64_decode($p['secret_cipher'],true);if($raw===false||strlen($raw)<29)throw new \RuntimeException('Invalid encrypted password.');$plain=openssl_decrypt(substr($raw,28),'aes-256-gcm',$this->key,OPENSSL_RAW_DATA,substr($raw,0,12),substr($raw,12,16),$this->aad((int)$p['organization_id'],$id));if($plain===false)throw new \RuntimeException('Password decryption failed.');$this->audit->record($a,'password.revealed','password',$id,(int)$p['organization_id']);return $plain;}
 public function save(Actor $a,int $org,?int $id,array $input): int {
  (new AccessPolicy($this->db))->require($a,'write',new RecordScope($org));$title=trim((string)($input['title']??''));$username=trim((string)($input['username']??''));$url=trim((string)($input['url']??''));$notes=(string)($input['notes']??'');$secret=(string)($input['secret']??'');$visibility=(string)($input['visibility']??'internal');$config=(int)($input['configuration_id']??0)?:null;
  if($title===''||mb_strlen($title)>190||mb_strlen($username)>190||strlen($url)>2048||strlen($notes)>10000||strlen($secret)>10000||($id===null&&$secret==='')||!in_array($visibility,['internal','client'],true))throw new HttpException(422,'Enter a title and password, and keep fields within their limits.');
  if($url!==''&&(!filter_var($url,FILTER_VALIDATE_URL)||!in_array(strtolower(parse_url($url,PHP_URL_SCHEME)??''),['http','https'],true)))throw new HttpException(422,'URL must use http or https.');
  if($config){$r=(new RecordRepository($this->db))->get($a,$config,'asset');if((int)$r['organization_id']!==$org||($visibility==='client'&&$r['visibility']!=='client'))throw new HttpException(422,'Choose a configuration in this organization with compatible visibility.');}
  return $this->db->transaction(function()use($a,$org,$id,$input,$title,$username,$url,$notes,$secret,$visibility,$config){
   if($id!==null){$p=$this->raw($a,$id,'write');if((int)$p['organization_id']!==$org)throw new HttpException(403,'Cannot move passwords between organizations.');$this->db->one('SELECT id FROM passwords WHERE id=? FOR UPDATE',[$id]);$p=$this->raw($a,$id,'write');if((int)($input['revision']??0)!==(int)$p['revision'])throw new HttpException(409,'This password changed. Reload before saving.');}
   else{$this->db->run("INSERT INTO passwords(organization_id,title,notes,secret_cipher) VALUES (?,?,'','')",[$org,$title]);$id=(int)$this->db->connection()->lastInsertId();}
   $cipher=$secret!==''?$this->encrypt($secret,$org,$id):$p['secret_cipher'];$this->db->run('UPDATE passwords SET title=?,username=?,url=?,notes=?,visibility=?,configuration_id=?,secret_cipher=?,revision=revision+?,updated_at=CURRENT_TIMESTAMP WHERE id=?',[$title,$username,$url,$notes,$visibility,$config,$cipher,isset($p)?1:0,$id]);$this->audit->record($a,isset($p)?'password.updated':'password.created','password',$id,$org);return $id;
  });
 }
 public function archive(Actor $a,int $id): void {$p=$this->raw($a,$id,'write');$this->db->run('UPDATE passwords SET deleted_at=CURRENT_TIMESTAMP WHERE id=?',[$id]);$this->audit->record($a,'password.archived','password',$id,(int)$p['organization_id']);}
}
