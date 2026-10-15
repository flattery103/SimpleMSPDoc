<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
use SimpleMSPDoc\Core\Database;
final class Maintenance
{
 public function __construct(private Database $db){}
 public function active(): bool {return ($this->db->one("SELECT value FROM settings WHERE name='maintenance'")['value']??'0')==='1';}
 public function set(bool $active): void {$this->db->run("INSERT INTO settings(name,value) VALUES ('maintenance',?) ON DUPLICATE KEY UPDATE value=VALUES(value)",[$active?'1':'0']);}
}
