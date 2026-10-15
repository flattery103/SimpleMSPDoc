<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Auth;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class PasswordPolicy
{
 public function __construct(private Database $db,private AuditService $audit){}
 public function minimum(): int {return (int)$this->db->one('SELECT password_minimum FROM application_settings WHERE id=1')['password_minimum'];}
 public function save(Actor $actor,mixed $value): void {
  (new AccessPolicy($this->db))->admin($actor);if(!is_scalar($value)||!preg_match('/^(?:[1-9]|[1-6][0-9]|7[0-2])$/',(string)$value))throw new HttpException(422,'Choose a minimum password length from 1 to 72 characters.');
  $this->db->transaction(function()use($actor,$value){$this->db->run('UPDATE application_settings SET password_minimum=? WHERE id=1',[(int)$value]);$this->audit->record($actor,'password_policy.saved','settings',null,null,['minimum'=>(int)$value]);});
 }
 public function validate(string $password): void {self::validateMinimum($password,$this->minimum());}
 public static function validateMinimum(string $password,int $minimum): void {if(mb_strlen($password)<$minimum||strlen($password)>72||str_contains($password,"\0"))throw new HttpException(422,'Password must contain at least '.$minimum.' characters and at most 72 bytes.');}
}
