<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class Csrf
{
 public static function valid(string $candidate,string $expected): bool {return $expected!==''&&$candidate!==''&&hash_equals($expected,$candidate);}
 public static function token(): string { return $_SESSION['csrf']??=bin2hex(random_bytes(32)); }
 public static function verify(): void {if(!self::valid((string)($_POST['_csrf']??''),self::token()))throw new HttpException(403,'Form expired. Reload the page and try again.');}
}
