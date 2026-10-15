<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Search;
use SimpleMSPDoc\Core\{Actor,Database,HttpException,ScopeQuery};
final class SearchService
{
 public function __construct(private Database $db){}
 public function search(Actor $actor,string $query,?int $organizationId,?string $recordType,int $page): array {
  $query=trim($query);if(mb_strlen($query)>200)throw new HttpException(422,'Search is limited to 200 characters.');if($recordType!==null&&!in_array($recordType,['asset','article','contact','location'],true))throw new HttpException(422,'Invalid record type.');$page=max(1,min(100000,$page));if($query==='')return ['items'=>[],'total'=>0,'page'=>$page];
  [$where,$params]=ScopeQuery::records($actor);$pattern='%'.str_replace(['!','%','_'],['!!','!%','!_'],$query).'%';$where.=" AND (r.title LIKE ? ESCAPE '!' OR r.search_text LIKE ? ESCAPE '!')";$params[]=$pattern;$params[]=$pattern;
  if($organizationId!==null){$where.=' AND r.organization_id=?';$params[]=$organizationId;}if($recordType!==null){$where.=' AND r.type=?';$params[]=$recordType;}
  $join=' FROM records r JOIN organizations o ON o.id=r.organization_id WHERE '.$where;$total=(int)$this->db->one('SELECT COUNT(*) AS n'.$join,$params)['n'];$items=$this->db->all('SELECT r.id,r.title,r.type,r.organization_id,r.visibility,r.search_text,o.name AS organization_name'.$join.' ORDER BY r.title,r.id LIMIT 25 OFFSET '.(($page-1)*25),$params);
  foreach($items as &$r){$r['snippet']=mb_substr($r['search_text'],0,180);unset($r['search_text']);}return ['items'=>$items,'total'=>$total,'page'=>$page];
 }
}
