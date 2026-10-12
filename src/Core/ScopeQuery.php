<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class ScopeQuery
{
 public static function records(Actor $actor,bool $trash=false): array {
  if($trash&&($actor->role==='read-only'||$actor->audience==='client'))throw new HttpException(403,'Access denied.');
  $sql='o.deleted_at IS NULL AND r.deleted_at IS '.($trash?'NOT NULL':'NULL');$params=[];
  if($actor->role!=='administrator'||$actor->audience!=='staff'){$sql.=' AND EXISTS (SELECT 1 FROM user_organizations uo WHERE uo.organization_id=r.organization_id AND uo.user_id=?)';$params[]=$actor->id;}
  if($actor->audience==='client')$sql.=" AND r.visibility='client'";
  return [$sql,$params];
 }
}
