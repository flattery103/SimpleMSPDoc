<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require dirname(__DIR__,2).'/app/vendor/autoload.php';
use SimpleMSPDoc\Core\{Actor,Database};
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Integrations\Action1\{Action1Service,SecretBox,Transport};
$db=Database::connect(['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:'']);
echo $db->run('SELECT CONNECTION_ID()')->fetchColumn().PHP_EOL;flush();
$transport=new class implements Transport {public function request(string $method,string $url,array $headers,?string $body=null): array {return ['status'=>200,'headers'=>[],'body'=>json_encode(['items'=>[['id'=>'one','name'=>'Upstream changed']]])];}};
$s=new Action1Service($db,new AuditService($db),new SecretBox(getenv('ACTION1_TEST_KEY')),$transport);echo json_encode($s->syncStep(new Actor((int)getenv('ACTION1_TEST_ACTOR'),'administrator','staff'),(int)getenv('ACTION1_TEST_MAPPING')));
