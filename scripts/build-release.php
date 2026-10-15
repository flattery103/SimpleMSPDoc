<?php
if (PHP_SAPI !== 'cli') { http_response_code(404); exit; }

require dirname(__DIR__).'/app/vendor/autoload.php';
$version=$argv[1]??'2.0.0-alpha.7';$out=dirname(__DIR__).'/dist/SimpleMSPDoc-'.$version.'.zip';
try{(new SimpleMSPDoc\Core\ReleaseBuilder(dirname(__DIR__)))->build($version,$out);echo $out.PHP_EOL;}catch(Throwable $e){fwrite(STDERR,$e->getMessage().PHP_EOL);exit(1);}
