<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class Session
{
 public static function start(bool $secure,string $path): void {if(session_status()===PHP_SESSION_ACTIVE)return;ini_set('session.use_strict_mode','1');ini_set('session.use_only_cookies','1');session_name('simplemspdoc');session_set_cookie_params(['httponly'=>true,'secure'=>$secure,'samesite'=>'Lax','path'=>$path?:'/']);session_start();}
 public static function authenticate(array $user): void {session_regenerate_id(true);$_SESSION=['user_id'=>(int)$user['id'],'session_version'=>(int)$user['session_version'],'last_seen'=>time(),'csrf'=>bin2hex(random_bytes(32))];}
 public static function logout(): void {$_SESSION=[];session_regenerate_id(true);}
}
