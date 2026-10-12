<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Organizations;
use SimpleMSPDoc\Core\Context;
final class OrganizationController
{
 public function __construct(private Context $ctx){}
 private function service(): OrganizationService {return new OrganizationService($this->ctx->db,$this->ctx->audit);}
 public function index(): void {$trash=!empty($_GET['trash']);$this->ctx->render('organizations/index',['title'=>$trash?'Archived organizations':'Organizations','organizations'=>$this->service()->listVisible($this->ctx->actor,$trash),'trash'=>$trash]);}
 public function form(array $params=[]): void {$this->ctx->requireAdmin();$this->ctx->render('organizations/form',['title'=>isset($params['id'])?'Edit organization':'New organization','organization'=>isset($params['id'])?$this->service()->get($this->ctx->actor,(int)$params['id']):null]);}
 public function save(array $params=[]): void {$id=$this->service()->save($this->ctx->actor,isset($params['id'])?(int)$params['id']:null,$_POST);$this->ctx->flash('Organization saved.');$this->ctx->redirect('/organizations/'.$id);}
 public function show(array $params): void {$o=$this->service()->get($this->ctx->actor,(int)$params['id']);$counts=[];$recent=[];foreach(['asset','article','contact','location'] as $type){$items=$this->ctx->records($type)->list($this->ctx->actor,(int)$o['id']);$counts[$type]=count($items);$recent[$type]=array_slice($items,0,4);}$this->ctx->render('organizations/show',['title'=>$o['name'],'organization'=>$o,'counts'=>$counts,'recent'=>$recent]);}
 public function archive(array $params): void {$this->service()->archive($this->ctx->actor,(int)$params['id']);$this->ctx->flash('Organization archived.');$this->ctx->redirect('/organizations');}
 public function restore(array $params): void {$this->service()->restore($this->ctx->actor,(int)$params['id']);$this->ctx->flash('Organization restored.');$this->ctx->redirect('/organizations');}
}
