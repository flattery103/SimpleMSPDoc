<?php
require dirname(__DIR__).'/vendor/autoload.php';
try{echo 'One-time setup token: '.SimpleMSPDoc\Modules\Installation\SetupToken::generate(dirname(__DIR__)).PHP_EOL;}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
