<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Tests\Integration;
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

use SimpleMSPDoc\Core\{Actor,AccessPolicy,RecordScope};
final class AccessPolicyTest extends DatabaseTestCase
{
 public function testOrganizationAudienceAndRoleAreEnforced(): void {
  $this->schema();$org=$this->org();$other=$this->org('Other');$uid=$this->user('read-only','client');$this->db->run('INSERT INTO user_organizations VALUES (?,?)',[$uid,$org]);
  $p=new AccessPolicy($this->db);$client=new Actor($uid,'read-only','client');$staff=new Actor($uid,'read-only','staff');$tech=new Actor($uid,'technician','staff');$admin=new Actor($uid,'administrator','staff');
  self::assertFalse($p->can($client,'read',new RecordScope($org,'internal')));self::assertTrue($p->can($client,'read',new RecordScope($org,'client')));self::assertFalse($p->can($client,'write',new RecordScope($org,'client')));
  self::assertTrue($p->can($staff,'read',new RecordScope($org,'internal')));self::assertFalse($p->can($staff,'write',new RecordScope($org,'internal')));self::assertTrue($p->can($tech,'write',new RecordScope($org,'internal')));
  self::assertFalse($p->can($tech,'read',new RecordScope($other,'client')));self::assertTrue($p->can($admin,'read',new RecordScope($other,'internal')));self::assertFalse($p->can($client,'read',new RecordScope($org,'client',true)));
 }
}
