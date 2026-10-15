<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
use SimpleMSPDoc\Core\Context;
final class UpgradeController
{
 public function __construct(private Context $ctx){}
 public function index(): void {$this->ctx->requireAdmin();$r=new MigrationRunner($this->ctx->db,$this->ctx->audit,$this->ctx->codeRoot.'/database/migrations',$this->ctx->privateRoot.'/data');if($_SERVER['REQUEST_METHOD']==='POST'){$r->apply($this->ctx->actor,!empty($_POST['backup']));$this->ctx->flash('Database is up to date.');$this->ctx->redirect('/upgrade');}$this->ctx->render('upgrade',['title'=>'Database upgrades','pending'=>$r->pending(),'maintenance'=>(new Maintenance($this->ctx->db))->active()]);}
}
