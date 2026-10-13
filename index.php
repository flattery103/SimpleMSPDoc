<?php
declare(strict_types=1);
ini_set('display_errors','0');
$appRoot=is_dir(__DIR__.'/app/src')?__DIR__.'/app':__DIR__;
require $appRoot.'/vendor/autoload.php';
(new SimpleMSPDoc\Core\Application($appRoot,getenv('SIMPLEMSPDOC_PRIVATE_ROOT')?:__DIR__))->handle();
