<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Installation;
use SimpleMSPDoc\Core\{Actor,AccessPolicy,Database,HttpException};
use SimpleMSPDoc\Modules\Audit\AuditService;
final class MigrationRunner
{
 public function __construct(private Database $db,private AuditService $audit,private string $directory,private string $privateDirectory){}
 public function lockName(): string {return 'simplemspdoc:'.substr(hash('sha256',(string)$this->db->connection()->query('SELECT DATABASE()')->fetchColumn()),0,48);}
 public function pending(): array {$done=array_column($this->db->all('SELECT version FROM migrations'),'version');$pending=[];foreach(glob($this->directory.'/*.php') as $file){$v=basename($file,'.php');if(!in_array($v,$done,true))$pending[]=['version'=>$v,'path'=>$file];}usort($pending,fn($a,$b)=>strcmp($a['version'],$b['version']));return $pending;}
 public function apply(Actor $actor,bool $backupAcknowledged): void {
  (new AccessPolicy($this->db))->admin($actor);if(!$backupAcknowledged)throw new HttpException(422,'Back up your database, configuration and uploaded files before upgrading.');
  $name=$this->lockName();if(strlen($name)>64)$name=substr($name,0,64);if((int)$this->db->run('SELECT GET_LOCK(?,0)',[$name])->fetchColumn()!==1)throw new HttpException(409,'Another upgrade is running.');
  $maintenance=new Maintenance($this->db);$version='preflight';
  try{
   if($maintenance->active())throw new HttpException(503,'Maintenance remains active. If a previous upgrade failed, restore the backup before retrying.');
   $pending=$this->pending();if(!$pending)return;
   $definitions=[];foreach($pending as $m){$d=require $m['path'];if(!is_array($d)||!isset($d['sql'],$d['checks'])||!is_array($d['sql'])||!is_array($d['checks']))throw new HttpException(422,'Invalid migration package.');$definitions[$m['version']]=$d;}
   $maintenance->set(true);
   foreach($definitions as $version=>$definition){foreach($definition['sql'] as $sql)$this->db->run($sql);foreach($definition['checks'] as $check)if(!(bool)$this->db->run($check)->fetchColumn())throw new \RuntimeException('Migration postcondition failed.');$this->db->run('INSERT INTO migrations(version) VALUES (?)',[$version]);$this->audit->record($actor,'upgrade.applied','migration',null,null,['version'=>$version]);}
   $maintenance->set(false);
  }catch(HttpException $e){throw $e;}catch(\Throwable $e){
   \SimpleMSPDoc\Core\ProtectedFile::write($this->privateDirectory.'/upgrade.log.php',gmdate(DATE_ATOM).' migration='.$version.' exception='.get_class($e).' code='.$e->getCode().PHP_EOL,true);
   throw new HttpException(503,'Upgrade failed. Maintenance remains active; restore your database and application backup before retrying.');
  }finally{$this->db->run('SELECT RELEASE_LOCK(?)',[$name]);}
 }
}
