<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class Config
{
    public function __construct(public readonly array $data) {}
    public static function load(string $path): self { if(!is_file($path))throw new \RuntimeException('Application is not installed.');if(!defined('SIMPLEMSPDOC_BOOTSTRAP'))define('SIMPLEMSPDOC_BOOTSTRAP',true);$data=require $path; if(!is_array($data))throw new \RuntimeException('Invalid configuration.'); return new self($data); }
}
