<?php
require dirname(__DIR__).'/vendor/autoload.php';
$version=$argv[1]??'2.0.0-alpha.2';$out=dirname(__DIR__).'/dist/SimpleMSPDoc-'.$version.'.zip';
try{(new SimpleMSPDoc\Core\ReleaseBuilder(dirname(__DIR__)))->build($version,$out);echo $out.PHP_EOL;}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
