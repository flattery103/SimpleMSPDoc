<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class Url
{
    public readonly string $base;
    public function __construct(string $scriptName) { $this->base = rtrim(str_replace('\\','/',dirname($scriptName)), '/.'); }
    public function to(string $path): string { return $this->base.'/'.ltrim($path,'/'); }
}
