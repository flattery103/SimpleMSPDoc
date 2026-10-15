<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,RecordScope};
final class ConnectionRepository
{
 public function __construct(private Database $db){}
 public function list(Actor $actor,int $organizationId): array {(new AccessPolicy($this->db))->require($actor,'write',new RecordScope($organizationId));return $this->db->all('SELECT id,provider,status,last_sync FROM integration_connections WHERE organization_id=?',[$organizationId]);}
}
