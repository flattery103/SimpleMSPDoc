<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Audit;
use SimpleMSPDoc\Core\Context;
final class AuditController
{
 public function __construct(private Context $ctx){}
 public function index(): void {$this->ctx->requireAdmin();$page=max(1,min(100000,(int)($_GET['page']??1)));$events=$this->ctx->db->all('SELECT a.*,u.name AS actor_name,o.name AS organization_name FROM audit_events a LEFT JOIN users u ON u.id=a.actor_id LEFT JOIN organizations o ON o.id=a.organization_id ORDER BY a.id DESC LIMIT 50 OFFSET '.(($page-1)*50));$this->ctx->render('audit',['title'=>'Audit history','events'=>$events,'page'=>$page]);}
}
