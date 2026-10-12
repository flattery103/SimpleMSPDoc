<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Modules\Installation\SetupToken;
final class HttpAcceptanceTest extends DatabaseTestCase
{
 private $server;
 private string $base;
 private string $cookie;
 private string $prefix='';
 private function startServer(string $prefix=''): void {
  $this->prefix=$prefix;$port=random_int(21000,29000);$this->base='http://127.0.0.1:'.$port.$prefix;$this->cookie=$this->root.'/cookies';
  $env=array_merge(getenv(),['SIMPLEMSPDOC_PRIVATE_ROOT'=>$this->root,'SIMPLEMSPDOC_BASE_PATH'=>$prefix]);
  $this->server=proc_open([PHP_BINARY,'-c',php_ini_loaded_file(),'-S','127.0.0.1:'.$port,'-t',dirname(__DIR__,2).'/public',dirname(__DIR__,2).'/public/router.php'],[0=>['pipe','r'],1=>['file',$this->root.'/server.log','a'],2=>['file',$this->root.'/server.log','a']],$pipes,dirname(__DIR__,2),$env);
  for($i=0;$i<100;$i++){try{$r=$this->request('GET','/login');if($r['status']>0)return;}catch(\Throwable){}usleep(20000);}throw new \RuntimeException('HTTP server did not start: '.file_get_contents($this->root.'/server.log'));
 }
 protected function tearDown(): void {if(is_resource($this->server)){proc_terminate($this->server);proc_close($this->server);}foreach(glob($this->root.'/var/logs/*') as $log)file_put_contents(dirname(__DIR__,2).'/.superpowers/http-debug.log',file_get_contents($log),FILE_APPEND);file_put_contents(dirname(__DIR__,2).'/.superpowers/http-debug.log',file_get_contents($this->root.'/server.log'),FILE_APPEND);parent::tearDown();}
 private function request(string $method,string $path,array $data=[]): array {
  $c=curl_init($this->base.$path);curl_setopt_array($c,[CURLOPT_RETURNTRANSFER=>true,CURLOPT_HEADER=>true,CURLOPT_COOKIEJAR=>$this->cookie,CURLOPT_COOKIEFILE=>$this->cookie,CURLOPT_TIMEOUT=>5]);if($method==='POST'){curl_setopt($c,CURLOPT_POST,true);curl_setopt($c,CURLOPT_POSTFIELDS,http_build_query($data));}
  $raw=curl_exec($c);if($raw===false)throw new \RuntimeException(curl_error($c));$size=curl_getinfo($c,CURLINFO_HEADER_SIZE);$status=curl_getinfo($c,CURLINFO_RESPONSE_CODE);curl_close($c);unset($c);clearstatcache();return ['status'=>$status,'body'=>substr($raw,$size),'headers'=>substr($raw,0,$size)];
 }
 private function csrf(string $path): string {$r=$this->request('GET',$path);preg_match('/name="_csrf" value="([a-f0-9]+)"/',$r['body'],$m);self::assertNotEmpty($m,'CSRF token missing from '.$path.' status '.$r['status'].' '.$r['body']);return $m[1];}
 private function installed(): int {$this->schema();$id=$this->user();$this->db->run("UPDATE users SET username='admin' WHERE id=?",[$id]);file_put_contents($this->root.'/config/local.php','<?php return '.var_export(['database'=>['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:''],'key'=>base64_encode(random_bytes(32)),'upload_limit'=>20971520],true).';');return $id;}
 private function login(): void {$token=$this->csrf('/login');$r=$this->request('POST','/login',['_csrf'=>$token,'username'=>'admin','password'=>'Correct-password-123']);self::assertSame(303,$r['status'],$r['body']);}
 public function testWebInstallerLocksAndSubpathAssetsLoad(): void {
  $token=SetupToken::generate($this->root);$this->startServer('/docs');$r=$this->request('GET','/install');self::assertSame(200,$r['status']);self::assertStringContainsString('Fresh installation',$r['body']);
  $db=parse_url('http://unused');$r=$this->request('POST','/install',['_csrf'=>$this->csrf('/install'),'setup_token'=>$token,'dsn'=>getenv('TEST_DSN'),'database_user'=>getenv('TEST_DB_USER')?:'root','database_password'=>getenv('TEST_DB_PASSWORD')?:'','username'=>'admin','name'=>'Admin','password'=>'Correct-password-123']);self::assertSame(303,$r['status'],$r['body']);
  $this->login();self::assertSame(403,$this->request('GET','/install')['status']);$r=$this->request('GET','/');self::assertSame(200,$r['status']);self::assertStringContainsString('/docs/assets/app.css',$r['body']);self::assertSame(200,$this->request('GET','/assets/app.css')['status']);
 }
 public function testRealFormsCreateDocumentationAndRejectClientAccess(): void {
  $this->installed();$this->startServer();$this->login();$r=$this->request('POST','/organizations/save',['_csrf'=>$this->csrf('/organizations/new'),'name'=>'Example Customer','description'=>'IT documentation']);self::assertSame(303,$r['status'],$r['body']);$org=(int)$this->db->one('SELECT id FROM organizations')['id'];
  $r=$this->request('POST',"/organizations/$org/article/save",['_csrf'=>$this->csrf("/organizations/$org/article/new"),'title'=>'Recovery instructions','visibility'=>'internal','html'=>'<p>Restore from backup</p>','category'=>'Operations','tags'=>'recovery']);self::assertSame(303,$r['status'],$r['body']);$id=(int)$this->db->one('SELECT id FROM records')['id'];self::assertStringContainsString('Restore from backup',$this->request('GET',"/records/$id")['body']);
  $r=$this->request('POST',"/records/$id/save",['_csrf'=>$this->csrf("/records/$id/edit"),'title'=>'Recovery instructions v2','visibility'=>'internal','html'=>'<p>Updated</p>','revision'=>1]);self::assertSame(303,$r['status'],$r['body']);self::assertSame(409,$this->request('POST',"/records/$id/save",['_csrf'=>$this->csrf("/records/$id/edit"),'title'=>'stale','visibility'=>'internal','html'=>'stale','revision'=>1])['status']);
  self::assertStringContainsString('Recovery instructions v2',$this->request('GET','/search?q=Recovery')['body']);self::assertSame(403,$this->request('POST','/organizations/save',['name'=>'No CSRF'])['status']);
  $client=$this->user('read-only','client');$this->db->run("UPDATE users SET username='client' WHERE id=?",[$client]);$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$client,$org]);$this->request('POST','/logout',['_csrf'=>$this->csrf('/')]);$r=$this->request('POST','/login',['_csrf'=>$this->csrf('/login'),'username'=>'client','password'=>'Correct-password-123']);self::assertSame(303,$r['status']);self::assertSame(404,$this->request('GET',"/records/$id")['status']);self::assertSame(403,$this->request('GET','/users')['status']);self::assertStringNotContainsString('Recovery instructions',$this->request('GET','/search?q=Recovery')['body']);
 }
 public function testRequiredMfaDoesNotGrantSessionBeforeEnrollment(): void {
  $id=$this->installed();$this->db->run('UPDATE users SET require_mfa=1 WHERE id=?',[$id]);$this->startServer();$this->login();self::assertSame(303,$this->request('GET','/users')['status']);$r=$this->request('GET','/mfa/enroll');self::assertStringContainsString('Authenticator setup',$r['body']);self::assertSame(200,$r['status']);
 }
 public function testAssetFormsRelationshipsUploadsAndRevisionRestore(): void {
  $this->installed();$this->startServer();$this->login();$org=$this->org();$other=$this->org('Another organization');
  $r=$this->request('POST',"/organizations/$org/location/save",['_csrf'=>$this->csrf("/organizations/$org/location/new"),'title'=>'Main office','visibility'=>'internal','address'=>'123 Main Street']);self::assertSame(303,$r['status'],$r['body']);$location=(int)$this->db->one("SELECT id FROM records WHERE type='location'")['id'];
  $type=(int)$this->db->one("SELECT id FROM asset_types WHERE name='Server'")['id'];$field=(int)$this->db->one('SELECT id FROM asset_fields WHERE asset_type_id=? ORDER BY id LIMIT 1',[$type])['id'];
  $r=$this->request('POST',"/organizations/$org/asset/save",['_csrf'=>$this->csrf("/organizations/$org/asset/new?template=$type"),'title'=>'DC01','template_id'=>$type,'values'=>[$field=>'dc01.example.test'],'location_id'=>$location,'visibility'=>'client']);self::assertSame(303,$r['status'],$r['body']);$asset=(int)$this->db->one("SELECT id FROM records WHERE type='asset'")['id'];$body=$this->request('GET',"/records/$asset")['body'];self::assertStringContainsString('dc01.example.test',$body);self::assertStringContainsString('Main office',$body);
  $r=$this->request('POST',"/records/$asset/relationships",['_csrf'=>$this->csrf("/records/$asset"),'target_id'=>$location]);self::assertSame(303,$r['status'],$r['body']);
  $tmp=$this->root.'/notes.txt';file_put_contents($tmp,'Asset notes');$curl=curl_init($this->base."/records/$asset/attachments");curl_setopt_array($curl,[CURLOPT_POST=>true,CURLOPT_POSTFIELDS=>['_csrf'=>$this->csrf("/records/$asset"),'attachment'=>new \CURLFile($tmp,'text/plain','notes.txt')],CURLOPT_RETURNTRANSFER=>true,CURLOPT_COOKIEFILE=>$this->cookie]);$body=curl_exec($curl);self::assertSame(303,curl_getinfo($curl,CURLINFO_RESPONSE_CODE),$body);unset($curl);$attachment=(int)$this->db->one('SELECT id FROM attachments')['id'];self::assertSame('Asset notes',$this->request('GET',"/attachments/$attachment")['body']);
  foreach([['First','<p>First text</p>'],['Second','<p>Second text</p>']] as $i=>[$title,$html]){if($i===0){$path="/organizations/$org/article/save";$form="/organizations/$org/article/new";}else{$article=(int)$this->db->one("SELECT id FROM records WHERE type='article'")['id'];$path="/records/$article/save";$form="/records/$article/edit";}$r=$this->request('POST',$path,['_csrf'=>$this->csrf($form),'title'=>$title,'html'=>$html,'revision'=>1]);self::assertSame(303,$r['status'],$r['body']);}
  $revision=(int)$this->db->one('SELECT id FROM revisions ORDER BY id LIMIT 1')['id'];$r=$this->request('POST',"/records/$article/revisions/$revision/restore",['_csrf'=>$this->csrf("/records/$article/revisions/$revision"),'expected_revision'=>2]);self::assertSame(303,$r['status'],$r['body']);self::assertSame('First',$this->db->one('SELECT title FROM records WHERE id=?',[$article])['title']);
  $client=$this->user('read-only','client');$this->db->run("UPDATE users SET username='client' WHERE id=?",[$client]);$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$client,$org]);$this->request('POST','/logout',['_csrf'=>$this->csrf('/')]);$this->request('POST','/login',['_csrf'=>$this->csrf('/login'),'username'=>'client','password'=>'Correct-password-123']);$body=$this->request('GET',"/records/$asset")['body'];self::assertStringContainsString('DC01',$body);self::assertStringNotContainsString('Main office',$body);self::assertStringNotContainsString('Another organization',$body);self::assertSame('Asset notes',$this->request('GET',"/attachments/$attachment")['body']);self::assertSame(403,$this->request('GET',"/records/$asset/edit")['status']);
 }

}
