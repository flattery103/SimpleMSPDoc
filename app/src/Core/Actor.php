<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final readonly class Actor
{
 public function __construct(public int $id,public string $role,public string $audience){}
 public static function from(array $user): self { return new self((int)$user['id'],$user['role'],$user['audience']); }
}
