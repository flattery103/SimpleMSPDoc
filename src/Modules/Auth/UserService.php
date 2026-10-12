<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Auth;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class UserService
{
 public function __construct(private Database $db,private AuditService $audit){}
 public function list(Actor $actor): array {(new AccessPolicy($this->db))->admin($actor);return $this->db->all('SELECT id,username,name,role,audience,active,require_mfa,mfa_enabled FROM users ORDER BY name');}
 public function get(Actor $actor,int $id): array {(new AccessPolicy($this->db))->admin($actor);$u=$this->db->one('SELECT id,username,name,role,audience,active,require_mfa,mfa_enabled FROM users WHERE id=?',[$id]);if(!$u)throw new HttpException(404,'User not found.');$u['organizations']=array_column($this->db->all('SELECT organization_id FROM user_organizations WHERE user_id=?',[$id]),'organization_id');return $u;}
 public function save(Actor $actor,?int $id,array $input): int {
  (new AccessPolicy($this->db))->admin($actor);$name=trim((string)($input['name']??''));$username=strtolower(trim((string)($input['username']??'')));$role=$input['role']??'read-only';$audience=$input['audience']??'staff';$password=(string)($input['password']??'');
  if(!preg_match('/^[a-z0-9@._+-]{3,190}$/',$username)||$name===''||strlen($name)>190||!in_array($role,['administrator','technician','read-only'],true)||!in_array($audience,['staff','client'],true)||($audience==='client'&&$role!=='read-only'))throw new HttpException(422,'Invalid user details. Client users must be read-only.');
  if(($id===null||$password!=='')&&(strlen($password)<12||strlen($password)>72))throw new HttpException(422,'Password must contain 12–72 bytes.');
  $active=empty($input['active'])?0:1;$required=empty($input['require_mfa'])?0:1;$orgs=array_unique(array_map('intval',$input['organizations']??[]));
  return $this->db->transaction(function()use($actor,$id,$input,$name,$username,$role,$audience,$password,$active,$required,$orgs){
   // Serialize administrator changes to prevent two concurrent demotions removing every administrator.
   $admins=$this->db->all("SELECT id FROM users WHERE role='administrator' AND audience='staff' AND active=1 FOR UPDATE");
   if($id!==null&&!$this->db->one('SELECT id FROM users WHERE id=? FOR UPDATE',[$id]))throw new HttpException(404,'User not found.');
   if($id!==null&&in_array($id,array_map('intval',array_column($admins,'id')),true)&&count($admins)===1&&(!$active||$role!=='administrator'||$audience!=='staff'))throw new HttpException(422,'Keep at least one active staff administrator.');
   if($this->db->one('SELECT id FROM users WHERE username=? AND id<>?',[$username,$id??0]))throw new HttpException(422,'Username is already in use.');
   foreach($orgs as $org)if(!$this->db->one('SELECT id FROM organizations WHERE id=? AND deleted_at IS NULL',[$org]))throw new HttpException(422,'Invalid organization assignment.');
   if($id===null){$this->db->run('INSERT INTO users(username,name,password_hash,role,audience,active,require_mfa) VALUES (?,?,?,?,?,?,?)',[$username,$name,password_hash($password,PASSWORD_DEFAULT),$role,$audience,$active,$required]);$id=(int)$this->db->connection()->lastInsertId();}
   else{$this->db->run('UPDATE users SET username=?,name=?,role=?,audience=?,active=?,require_mfa=?,session_version=session_version+1 WHERE id=?',[$username,$name,$role,$audience,$active,$required,$id]);if($password!=='')$this->db->run('UPDATE users SET password_hash=? WHERE id=?',[password_hash($password,PASSWORD_DEFAULT),$id]);}
   $this->db->run('DELETE FROM user_organizations WHERE user_id=?',[$id]);foreach($orgs as $org)$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$id,$org]);$this->audit->record($actor,'user.saved','user',$id,null,['role'=>$role,'audience'=>$audience]);return $id;
  });
 }
 public function resetMfa(Actor $actor,int $id): void {(new AccessPolicy($this->db))->admin($actor);$this->get($actor,$id);$this->db->transaction(function()use($actor,$id){$this->db->run('UPDATE users SET mfa_secret=NULL,mfa_enabled=0,mfa_last_step=NULL,recovery_codes=NULL,session_version=session_version+1 WHERE id=?',[$id]);$this->audit->record($actor,'mfa.admin_reset','user',$id,null);});}
}
