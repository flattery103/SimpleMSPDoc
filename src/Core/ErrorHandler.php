<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class ErrorHandler
{
    public static function message(\Throwable $error): string { return $error instanceof HttpException ? $error->getMessage() : 'An unexpected error occurred. Check the application log.'; }
}
