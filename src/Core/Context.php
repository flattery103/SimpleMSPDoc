<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
use SimpleMSPDoc\Modules\Audit\AuditService;
final class Context
{
 public string $route='/';
 public ?Actor $actor=null;
 public ?array $user=null;
 public ?Database $db=null;
 public ?AuditService $audit=null;
 public function __construct(public string $codeRoot,public string $privateRoot,public Url $url,public array $config=[]){}
 public function render(string $template,array $data=[],int $status=200): void {
  http_response_code($status);$e=fn(mixed $s)=>htmlspecialchars((string)$s,ENT_QUOTES|ENT_SUBSTITUTE,'UTF-8');$url=$this->url;$link=fn(string $s)=>$e($url->to($s));$routeInput=fn(string $path)=>'<input type="hidden" name="route" value="'.$e($path).'">';$csrf='<input type="hidden" name="_csrf" value="'.$e(Csrf::token()).'">';$actor=$this->actor;$user=$this->user;$title=$data['title']??'SimpleMSPDoc';$ctx=$this;
  extract($data,EXTR_SKIP);ob_start();require $this->codeRoot.'/templates/'.$template.'.php';$content=ob_get_clean();require $this->codeRoot.'/templates/layout.php';
 }
 public function redirect(string $path): never {header('Location: '.$this->url->to($path),true,303);exit;}
 public function flash(string $message): void {$_SESSION['flash']=$message;}
 public function requireAdmin(): void {(new AccessPolicy($this->db))->admin($this->actor);}
 public function records(string $type): RecordService {return match($type){'location'=>new \SimpleMSPDoc\Modules\Organizations\LocationService($this->db,$this->audit),'contact'=>new \SimpleMSPDoc\Modules\Organizations\ContactService($this->db,$this->audit),'asset'=>new \SimpleMSPDoc\Modules\Assets\AssetService($this->db,$this->audit),'article'=>new \SimpleMSPDoc\Modules\Articles\ArticleService($this->db,$this->audit,$this->privateRoot.'/data/cache'),default=>throw new HttpException(404,'Record type not found.')};}
}
