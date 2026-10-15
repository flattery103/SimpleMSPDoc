<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Passwords;
use SimpleMSPDoc\Core\{Context,AccessPolicy,RecordScope};
use SimpleMSPDoc\Modules\Organizations\OrganizationService;
final class PasswordController
{
 public function __construct(private Context $ctx){}
 private function service(): PasswordService {return new PasswordService($this->ctx->db,$this->ctx->audit,$this->ctx->config['key']);}
 private function org(int $id): array {return (new OrganizationService($this->ctx->db,$this->ctx->audit))->get($this->ctx->actor,$id);}
 private function writable(int $org): bool {return (new AccessPolicy($this->ctx->db))->can($this->ctx->actor,'write',new RecordScope($org));}
 public function index(array $p): void {$o=$this->org((int)$p['org']);$this->ctx->render('passwords/index',['title'=>'Passwords','organization'=>$o,'passwords'=>$this->service()->list($this->ctx->actor,(int)$o['id']),'canWrite'=>$this->writable((int)$o['id'])]);}
 public function form(array $p): void {$entry=isset($p['id'])?$this->service()->get($this->ctx->actor,(int)$p['id']):null;$org=(int)($entry['organization_id']??$p['org']);(new AccessPolicy($this->ctx->db))->require($this->ctx->actor,'write',new RecordScope($org));$configs=[];for($page=1;;$page++){$batch=$this->ctx->records('asset')->list($this->ctx->actor,$org,false,$page);$configs=array_merge($configs,$batch);if(count($batch)<50)break;}$this->ctx->render('passwords/form',['title'=>$entry?'Edit password':'New password','organization'=>$this->org($org),'entry'=>$entry,'configurations'=>$configs,'selectedConfiguration'=>(int)($entry['configuration_id']??$_GET['configuration']??0)]);}
 public function save(array $p): void {$entry=isset($p['id'])?$this->service()->get($this->ctx->actor,(int)$p['id']):null;$org=(int)($entry['organization_id']??$p['org']);$id=$this->service()->save($this->ctx->actor,$org,$entry?(int)$entry['id']:null,$_POST);$this->ctx->flash('Password saved.');$this->ctx->redirect('/passwords/'.$id);}
 public function show(array $p): void {$entry=$this->service()->get($this->ctx->actor,(int)$p['id']);$configuration=$entry['configuration_id']?$this->ctx->records('asset')->get($this->ctx->actor,(int)$entry['configuration_id']):null;$secret=$_SERVER['REQUEST_METHOD']==='POST'?$this->service()->reveal($this->ctx->actor,(int)$p['id']):null;$this->ctx->render('passwords/show',['title'=>$entry['title'],'entry'=>$entry,'organization'=>$this->org((int)$entry['organization_id']),'configuration'=>$configuration,'secret'=>$secret,'canWrite'=>$this->writable((int)$entry['organization_id'])]);}
 public function archive(array $p): void {$entry=$this->service()->get($this->ctx->actor,(int)$p['id']);$this->service()->archive($this->ctx->actor,(int)$p['id']);$this->ctx->redirect('/organizations/'.$entry['organization_id'].'/passwords');}
}
