<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
class HttpException extends \RuntimeException
{
    public function __construct(public readonly int $status, string $message) { parent::__construct($message); }
}
