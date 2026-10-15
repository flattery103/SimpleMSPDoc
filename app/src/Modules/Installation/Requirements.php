<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
final class Requirements
{
    public static function check(array $runtime = []): array
    {
        $result = ['PHP 8.3 or newer'=>version_compare($runtime['php'] ?? PHP_VERSION, '8.3', '>=')];
        $extensions = $runtime['extensions'] ?? get_loaded_extensions();
        foreach (['pdo_mysql','mbstring','dom','fileinfo','openssl'] as $ext) $result[$ext] = in_array($ext, $extensions, true);
        return $result;
    }
}
