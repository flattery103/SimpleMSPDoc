<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations\Action1;
final class CurlTransport implements Transport
{
 public function request(string $method,string $url,array $headers,?string $body=null): array {
  if(!extension_loaded('curl'))throw new Action1Error('Enable the PHP cURL extension to use Action1.',422);
  $ch=curl_init($url);$response='';$responseHeaders=[];curl_setopt_array($ch,[CURLOPT_CUSTOMREQUEST=>$method,CURLOPT_HTTPHEADER=>$headers,CURLOPT_FOLLOWLOCATION=>false,CURLOPT_PROTOCOLS=>CURLPROTO_HTTPS,CURLOPT_CONNECTTIMEOUT=>5,CURLOPT_TIMEOUT=>15,CURLOPT_SSL_VERIFYPEER=>true,CURLOPT_SSL_VERIFYHOST=>2,CURLOPT_WRITEFUNCTION=>function($c,string $chunk)use(&$response){if(strlen($response)+strlen($chunk)>4194304)return 0;$response.=$chunk;return strlen($chunk);},CURLOPT_HEADERFUNCTION=>function($c,string $line)use(&$responseHeaders){if(str_contains($line,':')){[$k,$v]=explode(':',$line,2);$responseHeaders[strtolower(trim($k))]=trim($v);}return strlen($line);}]);if($body!==null)curl_setopt($ch,CURLOPT_POSTFIELDS,$body);
  try{if(curl_exec($ch)===false)throw new Action1Error('Action1 request could not complete. Check connectivity and try again.');return ['status'=>(int)curl_getinfo($ch,CURLINFO_RESPONSE_CODE),'body'=>$response,'headers'=>$responseHeaders];}finally{curl_close($ch);}
 }
}
