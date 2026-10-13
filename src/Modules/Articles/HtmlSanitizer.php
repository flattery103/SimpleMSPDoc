<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Articles;
final class HtmlSanitizer
{
 private \HTMLPurifier $purifier;
 public function __construct(string $cache) {
  if(!is_dir($cache))mkdir($cache,0700,true);$c=\HTMLPurifier_Config::createDefault();$c->set('Cache.SerializerPath',$cache);$c->set('HTML.Allowed','p,br,strong,b,em,i,u,s,ul,ol,li,h1,h2,h3,h4,blockquote,pre,code,a[href|title],table,thead,tbody,tr,th,td');$c->set('URI.AllowedSchemes',['http'=>true,'https'=>true,'mailto'=>true]);$this->purifier=new \HTMLPurifier($c);
 }
 public function clean(string $html): string {return $this->purifier->purify($html);}
}
