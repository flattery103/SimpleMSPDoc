<?php
declare(strict_types=1);
ini_set('display_errors','0');
require dirname(__DIR__).'/vendor/autoload.php';
(new SimpleMSPDoc\Core\Application(dirname(__DIR__),getenv('SIMPLEMSPDOC_PRIVATE_ROOT')?:dirname(__DIR__)))->handle();
