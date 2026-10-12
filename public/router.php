<?php
// Development server router. Apache uses .htaccess instead.
$base=getenv('SIMPLEMSPDOC_BASE_PATH')?:'';$path=parse_url($_SERVER['REQUEST_URI'],PHP_URL_PATH);$relative=$base!==''&&str_starts_with($path,$base.'/')?substr($path,strlen($base)):$path;
if(str_starts_with($relative,'/assets/')&&is_file(__DIR__.$relative)){
 $ext=pathinfo($relative,PATHINFO_EXTENSION);header('Content-Type: '.match($ext){'css'=>'text/css','js'=>'application/javascript',default=>'application/octet-stream'});readfile(__DIR__.$relative);return;
}
$_SERVER['SCRIPT_NAME']=$base.'/index.php';require __DIR__.'/index.php';
