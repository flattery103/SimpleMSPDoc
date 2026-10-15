<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require dirname(__DIR__,2).'/app/vendor/autoload.php';
use SimpleMSPDoc\Core\Database;
use SimpleMSPDoc\Modules\Audit\AuditService;
use SimpleMSPDoc\Modules\Auth\MfaService;
try{$db=Database::connect(['dsn'=>getenv('TEST_DSN'),'username'=>getenv('TEST_DB_USER')?:'root','password'=>getenv('TEST_DB_PASSWORD')?:'']);$service=new MfaService($db,new AuditService($db),getenv('TEST_MFA_KEY'));$codes=$service->confirmEnrollment((int)getenv('TEST_MFA_USER'),getenv('TEST_MFA_CODE'),1);echo json_encode(['success'=>true,'codes'=>count($codes)]);}catch(Throwable $e){echo json_encode(['success'=>false,'class'=>get_class($e)]);}
