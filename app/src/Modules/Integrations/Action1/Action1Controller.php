<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations\Action1;
use SimpleMSPDoc\Core\{Context,HttpException};
use SimpleMSPDoc\Modules\Organizations\OrganizationService;
final class Action1Controller
{
 public function __construct(private Context $ctx){}
 private function service(): Action1Service {return new Action1Service($this->ctx->db,$this->ctx->audit,new SecretBox($this->ctx->config['key']));}
 public function index(): void {$this->ctx->requireAdmin();$state=$this->service()->state($this->ctx->actor);$this->ctx->render('addons/action1',['title'=>'Action1 add-on',...$state,'localOrganizations'=>(new OrganizationService($this->ctx->db,$this->ctx->audit))->listVisible($this->ctx->actor),'curlAvailable'=>extension_loaded('curl')]);}
 public function addons(): void {$this->ctx->requireAdmin();$this->ctx->render('addons/index',['title'=>'Add-ons','action1'=>$this->service()->state($this->ctx->actor)['settings']]);}
 public function save(): void {$this->ctx->requireAdmin();$this->service()->saveSettings($this->ctx->actor,$_POST);$this->ctx->flash('Action1 settings saved.');$this->ctx->redirect('/addons/action1');}
 public function map(): void {$this->ctx->requireAdmin();$this->service()->map($this->ctx->actor,(string)($_POST['external_organization_id']??''),(int)($_POST['organization_id']??0),!empty($_POST['active']));$this->ctx->flash('Organization mapping saved.');$this->ctx->redirect('/addons/action1');}
 public function operation(array $params): void {
  $this->ctx->requireAdmin();$json=str_contains($_SERVER['HTTP_ACCEPT']??'','application/json');$status=200;
  try{$s=$this->service();$a=$this->ctx->actor;$id=(int)($_POST['mapping_id']??0);$result=match($params['operation']){'test'=>$s->test($a),'discover-start'=>$s->discoverStart($a),'discover-step'=>$s->discoverStep($a),'sync-start'=>$s->startSync($a,$id),'sync-step'=>$s->syncStep($a,$id),default=>throw new HttpException(404,'Unknown Action1 operation.')};if($result['more']??false)$result['retry_after']=3;}
  catch(HttpException $e){$status=$e->status;$result=['more'=>false,'message'=>$e->getMessage(),'retry_after'=>$e instanceof Action1Error?$e->retryAfter:0];}
  if(!$json){$this->ctx->flash($result['message']);$this->ctx->redirect('/addons/action1');}
  http_response_code($status);header('Content-Type: application/json; charset=utf-8');if($status===429)header('Retry-After: '.max(1,$result['retry_after']));echo json_encode($result,JSON_THROW_ON_ERROR);
 }
}
