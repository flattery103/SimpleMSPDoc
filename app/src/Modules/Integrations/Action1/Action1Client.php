<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations\Action1;
use SimpleMSPDoc\Core\Database;
final class Action1Client
{
 public const ORIGINS=['us'=>'https://app.action1.com','eu'=>'https://app.eu.action1.com'];
 public function __construct(private Database $db,private SecretBox $box,private Transport $transport){}
 private function settings(): array {$s=$this->db->one('SELECT * FROM action1_settings WHERE id=1');if(!$s||!$s['enabled']||!$s['credentials'])throw new Action1Error('Enable Action1 and save API credentials first.',422);if(!isset(self::ORIGINS[$s['region']]))throw new Action1Error('Unsupported Action1 region.',422);return $s;}
 private function reserve(): void {$this->db->transaction(function(){ $s=$this->db->one('SELECT next_request_at FROM action1_settings WHERE id=1 FOR UPDATE');$wait=(int)$s['next_request_at']-time();if($wait>0)throw new Action1Error('Waiting for the next Action1 request window.',429,$wait);$this->db->run('UPDATE action1_settings SET next_request_at=? WHERE id=1',[time()+3]);});}
 private function request(string $method,string $url,array $headers,?string $body=null): array {
  $this->reserve();$r=$this->transport->request($method,$url,$headers,$body);$status=(int)($r['status']??0);$json=json_decode((string)($r['body']??''),true,64);
  if($status===429){$delay=$json['details']['retry_after']??$r['headers']['retry-after']??30;$delay=is_numeric($delay)?max(1,min(86400,(int)$delay)):30;$this->db->run('UPDATE action1_settings SET next_request_at=GREATEST(next_request_at,?) WHERE id=1',[time()+$delay]);throw new Action1Error('Action1 is rate limiting requests. The sync will resume after its retry delay.',429,$delay);}
  if($status===401&&$method==='GET'){$this->db->run('UPDATE action1_settings SET token_cipher=NULL,token_expires=0 WHERE id=1');throw new Action1Error('Renewing the Action1 access token.',429,3);}
  if(in_array($status,[401,403],true))throw new Action1Error('Action1 rejected the credentials or their permissions. Check the API role and region.',422);
  if($status<200||$status>=300)throw new Action1Error('Action1 returned an unsuccessful response (HTTP '.$status.'). Try again later.');
  if(!is_array($json)||json_last_error()!==JSON_ERROR_NONE)throw new Action1Error('Action1 returned invalid JSON. No sync cursor was advanced.');return $json;
 }
 private function token(array $s): string {
  if($s['token_cipher']&&(int)$s['token_expires']>time()+5){$t=$this->box->open($s['token_cipher'],'token');if(is_string($t['access_token']??null))return $t['access_token'];}
  $credentials=$this->box->open($s['credentials'],'credentials');$r=$this->request('POST',self::ORIGINS[$s['region']].'/api/3.0/oauth2/token',['Content-Type: application/x-www-form-urlencoded','Accept: application/json'],http_build_query(['client_id'=>$credentials['client_id'],'client_secret'=>$credentials['client_secret']],'','&',PHP_QUERY_RFC3986));
  $token=$r['access_token']??null;if(!is_string($token)||$token===''||strlen($token)>16384||preg_match('/[\r\n]/',$token))throw new Action1Error('Action1 did not return a valid access token.');$ttl=max(1,min(86400,(int)($r['expires_in']??3600))-30);$this->db->run('UPDATE action1_settings SET token_cipher=?,token_expires=? WHERE id=1',[$this->box->seal(['access_token'=>$token],'token'),time()+$ttl]);return $token;
 }
 public static function validId(mixed $id): bool {return (is_string($id)||is_int($id))&&preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_.-]{0,189}$/',(string)$id)===1;}
 public function organizations(int $offset=0): array {return $this->page('/organizations',$offset,false);}
 public function endpoints(string $org,int $offset=0): array {if(!self::validId($org))throw new Action1Error('Invalid Action1 organization ID.',422);return $this->page('/endpoints/managed/'.rawurlencode($org),$offset,true);}
 private function page(string $path,int $offset,bool $extended): array {
  if($offset<0||$offset>100000000)throw new Action1Error('Invalid Action1 pagination cursor.');$s=$this->settings();$token=$this->token($s);$origin=self::ORIGINS[$s['region']];$query=['from'=>$offset,'limit'=>50];if($extended)$query['fields']='*';$r=$this->request('GET',$origin.'/api/3.0'.$path.'?'.http_build_query($query),['Authorization: Bearer '.$token,'Accept: application/json']);
  if(!isset($r['items'])||!is_array($r['items'])||!array_is_list($r['items'])||count($r['items'])>50)throw new Action1Error('Action1 returned an invalid result page. No sync cursor was advanced.');$next=null;$pointer=$r['next_page']??null;
  if($pointer!==null&&$pointer!==''){
   if(!is_string($pointer)||strlen($pointer)>4096)throw new Action1Error('Invalid Action1 pagination link.');$p=parse_url($pointer);if($p===false||isset($p['user'])||isset($p['pass'])||isset($p['fragment'])||(isset($p['scheme'])&&$p['scheme']!=='https')||(isset($p['host'])&&$p['host']!==parse_url($origin,PHP_URL_HOST))||(isset($p['port'])&&$p['port']!==443)||(isset($p['path'])&&$p['path']!==''&&$p['path']!=='/api/3.0'.$path))throw new Action1Error('Action1 returned an unexpected pagination link.');parse_str($p['query']??'',$q);if(!isset($q['from'])||!is_string($q['from'])||!ctype_digit($q['from']))throw new Action1Error('Invalid Action1 pagination offset.');$next=(int)$q['from'];
  }elseif(isset($r['total_items'])&&is_numeric($r['total_items'])&&(int)$r['total_items']>$offset+count($r['items']))$next=$offset+count($r['items']);
  if($next!==null&&($next<=$offset||$next>100000000||$r['items']===[]))throw new Action1Error('Action1 pagination did not advance.');return ['items'=>$r['items'],'next'=>$next];
 }
}
