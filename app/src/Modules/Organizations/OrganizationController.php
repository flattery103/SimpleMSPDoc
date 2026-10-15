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
 public function save(array $params=[]): void {
  $id=$this->ctx->db->transaction(function()use($params){$id=$this->service()->save($this->ctx->actor,isset($params['id'])?(int)$params['id']:null,$_POST);if(!empty($_POST['remove_logo']))$this->service()->setLogo($this->ctx->actor,$id,null);$file=$_FILES['logo']??[];if(($file['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_NO_FILE){if(($file['error']??1)!==UPLOAD_ERR_OK||!is_uploaded_file($file['tmp_name']??'')||($file['size']??0)>1048576)throw new \SimpleMSPDoc\Core\HttpException(422,'Upload a PNG, JPEG or WebP logo up to 1 MiB.');$this->service()->setLogo($this->ctx->actor,$id,file_get_contents($file['tmp_name']));}return $id;});$this->ctx->flash('Organization saved.');$this->ctx->redirect('/organizations/'.$id);
 }
 public function show(array $params): void {$o=$this->service()->get($this->ctx->actor,(int)$params['id']);$counts=[];$recent=[];foreach(['asset','article','contact','location'] as $type){$items=$this->ctx->records($type)->list($this->ctx->actor,(int)$o['id']);$counts[$type]=count($items);$recent[$type]=array_slice($items,0,4);}$this->ctx->render('organizations/show',['title'=>$o['name'],'organization'=>$o,'counts'=>$counts,'recent'=>$recent]);}
 public function logo(array $params): void {$logo=$this->service()->logo($this->ctx->actor,(int)$params['id']);header('Content-Type: '.$logo['logo_mime']);header('Content-Length: '.strlen($logo['logo_bytes']));header("Content-Security-Policy: default-src 'none'; sandbox");echo $logo['logo_bytes'];}
 public function archive(array $params): void {$this->service()->archive($this->ctx->actor,(int)$params['id']);$this->ctx->flash('Organization archived.');$this->ctx->redirect('/organizations');}
 public function restore(array $params): void {$this->service()->restore($this->ctx->actor,(int)$params['id']);$this->ctx->flash('Organization restored.');$this->ctx->redirect('/organizations');}
}
