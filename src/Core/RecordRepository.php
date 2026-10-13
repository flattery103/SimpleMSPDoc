<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Core;
final class RecordRepository
{
 public function __construct(private Database $db){}
 public function get(Actor $actor,int $id,?string $type=null,bool $trash=false): array {
  [$where,$params]=ScopeQuery::records($actor,$trash);$where.=' AND r.id=?';$params[]=$id;if($type!==null){$where.=' AND r.type=?';$params[]=$type;}
  $r=$this->db->one('SELECT r.* FROM records r JOIN organizations o ON o.id=r.organization_id WHERE '.$where,$params);if(!$r)throw new HttpException(404,'Record not found.');return self::decode($r);
 }
 public function list(Actor $actor,int $org,string $type,bool $trash=false,int $page=1): array {
  [$where,$params]=ScopeQuery::records($actor,$trash);$params[]=$org;$params[]=$type;$offset=(max(1,min($page,100000))-1)*50;
  return array_map(self::decode(...),$this->db->all('SELECT r.* FROM records r JOIN organizations o ON o.id=r.organization_id WHERE '.$where.' AND r.organization_id=? AND r.type=? ORDER BY r.title,r.id LIMIT 50 OFFSET '.$offset,$params));
 }
 public static function decode(array $r): array {$r['payload']=json_decode($r['payload'],true,512,JSON_THROW_ON_ERROR);$r['tags']=json_decode($r['tags']??'[]',true)??[];return $r;}
}
