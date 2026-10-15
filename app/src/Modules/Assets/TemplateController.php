<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\Context;
final class TemplateController
{
 public function __construct(private Context $ctx){}
 private function service(): TemplateService {return new TemplateService($this->ctx->db,$this->ctx->audit);}
 public function index(): void {$this->ctx->requireAdmin();$this->ctx->render('templates/index',['title'=>'Asset templates','templates'=>$this->service()->list()]);}
 public function show(array $params): void {$this->ctx->requireAdmin();$this->ctx->render('templates/show',['title'=>'Edit asset template','template'=>$this->service()->get((int)$params['id'])]);}
 public function save(): void {$id=$this->service()->create($this->ctx->actor,$_POST);$this->ctx->redirect('/templates/'.$id);}
 public function addField(array $params): void {$this->service()->addField($this->ctx->actor,(int)$params['id'],$_POST);$this->ctx->redirect('/templates/'.$params['id']);}
 public function archiveField(array $params): void {$this->ctx->requireAdmin();$id=(int)$params['id'];$t=$this->ctx->db->one('SELECT asset_type_id FROM asset_fields WHERE id=?',[$id]);$this->service()->archiveField($this->ctx->actor,$id);$this->ctx->redirect('/templates/'.$t['asset_type_id']);}
}
