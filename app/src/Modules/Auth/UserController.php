<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Auth;
use SimpleMSPDoc\Core\Context;
use SimpleMSPDoc\Modules\Organizations\OrganizationService;
final class UserController
{
 public function __construct(private Context $ctx){}
 private function service(): UserService {return new UserService($this->ctx->db,$this->ctx->audit);}
 public function index(): void {$this->ctx->render('users/index',['title'=>'Users and access','users'=>$this->service()->list($this->ctx->actor),'passwordMinimum'=>(new PasswordPolicy($this->ctx->db,$this->ctx->audit))->minimum()]);}
 public function form(array $params=[]): void {$this->ctx->requireAdmin();$this->ctx->render('users/form',['title'=>isset($params['id'])?'Edit user':'New user','passwordMinimum'=>(new PasswordPolicy($this->ctx->db,$this->ctx->audit))->minimum(),'editing'=>isset($params['id'])?$this->service()->get($this->ctx->actor,(int)$params['id']):null,'organizations'=>(new OrganizationService($this->ctx->db,$this->ctx->audit))->listVisible($this->ctx->actor)]);}
 public function save(array $params=[]): void {$id=$this->service()->save($this->ctx->actor,isset($params['id'])?(int)$params['id']:null,$_POST);$this->ctx->flash('User saved. Existing sessions were invalidated.');$this->ctx->redirect('/users');}
 public function policy(): void {(new PasswordPolicy($this->ctx->db,$this->ctx->audit))->save($this->ctx->actor,$_POST['password_minimum']??null);$this->ctx->flash('Password policy saved. It applies to new and changed passwords.');$this->ctx->redirect('/users');}
 public function reset(array $params): void {$this->service()->resetMfa($this->ctx->actor,(int)$params['id']);$this->ctx->flash('Authenticator reset. Existing sessions were invalidated.');$this->ctx->redirect('/users');}
}
