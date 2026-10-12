<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Search;
use SimpleMSPDoc\Core\Context;
use SimpleMSPDoc\Modules\Organizations\OrganizationService;
final class SearchController
{
 public function __construct(private Context $ctx){}
 public function index(): void {$q=(string)($_GET['q']??'');$org=empty($_GET['organization'])?null:(int)$_GET['organization'];$type=empty($_GET['type'])?null:(string)$_GET['type'];$result=(new SearchService($this->ctx->db))->search($this->ctx->actor,$q,$org,$type,(int)($_GET['page']??1));$this->ctx->render('search',['title'=>'Search documentation','query'=>$q,'result'=>$result,'selectedOrg'=>$org,'selectedType'=>$type,'organizations'=>(new OrganizationService($this->ctx->db,$this->ctx->audit))->listVisible($this->ctx->actor)]);}
}
