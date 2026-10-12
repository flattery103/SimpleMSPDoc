<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
use SimpleMSPDoc\Core\Database;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Auth\MfaService;
final class MfaConcurrencyTest extends DatabaseTestCase
{
 public function testOnlyOneConcurrentEnrollmentConfirmationSucceeds(): void {
  $this->schema();$id=$this->user();$key=base64_encode(random_bytes(32));$service=new MfaService($this->db,new AuditService($this->db),$key);$enrollment=$service->enroll($id);$code=\OTPHP\TOTP::createFromSecret($enrollment['secret'])->now();
  $this->db->connection()->beginTransaction();$this->db->run('SELECT id FROM users WHERE id=? FOR UPDATE',[$id]);$children=[];$streams=[];
  try{for($i=0;$i<2;$i++){$env=array_merge(getenv(),['TEST_MFA_KEY'=>$key,'TEST_MFA_USER'=>(string)$id,'TEST_MFA_CODE'=>$code]);$p=proc_open([PHP_BINARY,'-c',php_ini_loaded_file(),dirname(__DIR__).'/Fixtures/mfa-confirm-worker.php'],[0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$env);self::assertIsResource($p);$children[]=$p;$streams[]=$pipes;}
   $bothWaiting=false;for($i=0;$i<200;$i++){$waiters=$this->db->one("SELECT COUNT(*) AS n FROM information_schema.PROCESSLIST WHERE ID<>CONNECTION_ID() AND COMMAND IN ('Query','Execute') AND INFO LIKE '%users%' AND INFO NOT LIKE '%PROCESSLIST%'");if((int)$waiters['n']>=2){$bothWaiting=true;break;}usleep(10000);}$debug=[];if(!$bothWaiting){foreach($streams as $pipes){stream_set_blocking($pipes[1],false);stream_set_blocking($pipes[2],false);$debug[]=[stream_get_contents($pipes[1]),stream_get_contents($pipes[2])];}foreach($this->db->all('SELECT ID,COMMAND,STATE,LEFT(INFO,60) AS query_start FROM information_schema.PROCESSLIST') as $info)$debug[]=$info;}self::assertTrue($bothWaiting,'Both confirmations must be blocked on the row before releasing the lock. '.json_encode($debug));$this->db->connection()->commit();$successes=0;foreach($streams as $pipes){$body=stream_get_contents($pipes[1]);$err=stream_get_contents($pipes[2]);$r=json_decode($body,true);self::assertIsArray($r,$err.' '.$body);$successes+=!empty($r['success'])?1:0;}self::assertSame(1,$successes,'The same enrollment code cannot confirm two pending sessions.');self::assertSame(2,(int)$this->db->one('SELECT session_version FROM users WHERE id=?',[$id])['session_version']);
  }finally{if($this->db->connection()->inTransaction())$this->db->connection()->rollBack();foreach($streams as $pipes)foreach($pipes as $p)if(is_resource($p))fclose($p);foreach($children as $p){proc_terminate($p);proc_close($p);}}
 }
 public function testResetSessionVersionPreventsEnrollmentConfirmation(): void {$this->schema();$id=$this->user();$s=new MfaService($this->db,new AuditService($this->db),base64_encode(random_bytes(32)));$e=$s->enroll($id);$this->db->run('UPDATE users SET session_version=2 WHERE id=?',[$id]);$this->expectException(\SimpleMSPDoc\Core\HttpException::class);$s->confirmEnrollment($id,\OTPHP\TOTP::createFromSecret($e['secret'])->now(),1);}
}
