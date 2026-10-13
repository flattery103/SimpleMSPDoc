<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class AccessPolicy
{
 public function __construct(private Database $db){}
 public function admin(Actor $actor): void { if($actor->role!=='administrator'||$actor->audience!=='staff')throw new HttpException(403,'Administrator access required.'); }
 public function can(Actor $actor,string $action,RecordScope $scope): bool {
  if($actor->audience==='client'&&($action!=='read'||$scope->visibility!=='client'||$scope->deleted))return false;
  if($action==='write'&&$actor->role==='read-only')return false;
  if($scope->deleted&&$action==='read')return false;
  $org=$this->db->one('SELECT id,deleted_at FROM organizations WHERE id=?',[$scope->organizationId]);if(!$org||$org['deleted_at']!==null)return false;
  if($actor->role==='administrator'&&$actor->audience==='staff')return true;
  return $this->db->one('SELECT user_id FROM user_organizations WHERE user_id=? AND organization_id=?',[$actor->id,$scope->organizationId])!==null;
 }
 public function require(Actor $actor,string $action,RecordScope $scope): void { if(!$this->can($actor,$action,$scope))throw new HttpException($action==='read'?404:403,$action==='read'?'Record not found.':'Access denied.'); }
}
