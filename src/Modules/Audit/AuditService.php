<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Audit;
use SimpleMSPDoc\Core\{Actor,Database};
final class AuditService
{
 public function __construct(private Database $db){}
 public function record(?Actor $actor,string $action,string $targetType,?int $targetId,?int $organizationId,array $metadata=[]): void {
  $safe=array_intersect_key($metadata,array_flip(['changed_fields','revision','reason','role','audience','username','version']));
  $this->db->run('INSERT INTO audit_events(actor_id,organization_id,action,target_type,target_id,metadata) VALUES (?,?,?,?,?,?)',[$actor?->id,$organizationId,$action,$targetType,$targetId,json_encode($safe,JSON_THROW_ON_ERROR)]);
 }
}
