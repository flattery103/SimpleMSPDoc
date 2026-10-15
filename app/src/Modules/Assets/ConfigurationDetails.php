<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Assets;
use SimpleMSPDoc\Core\HttpException;
final class ConfigurationDetails
{
 public const FIELDS=['serial_number'=>'Serial number','device_type'=>'Device type','manufacturer'=>'Manufacturer','model'=>'Model','cpu'=>'Processor','cpu_specs'=>'Processor specifications','ram'=>'RAM','storage'=>'Storage','mac_address'=>'MAC address','purchase_date'=>'Purchase date','warranty_expiration'=>'Warranty expiration','location_text'=>'Location (text)','operating_system'=>'Operating system','ip_address'=>'IP address','external_ip_address'=>'External IP address','platform'=>'Platform','agent_version'=>'Agent version','agent_installed'=>'Agent installed','last_seen'=>'Last seen','status'=>'Status','subscription_status'=>'Subscription status','last_user'=>'Last reported user','missing_critical_updates'=>'Missing critical updates','missing_other_updates'=>'Missing other updates'];
 public const SECONDARY=['platform','agent_version','agent_installed','last_seen','status','subscription_status','last_user','missing_critical_updates','missing_other_updates'];
 public const TYPES=['Workstation','Laptop','Server','Firewall','Switch','Router','Access point','Printer','Storage','Virtual machine','Other'];
 public static function validate(mixed $input,bool $keepEmpty=false): array {
  if(!is_array($input))throw new HttpException(422,'Invalid configuration details.');$out=[];
  foreach(self::FIELDS as $key=>$label){if(!array_key_exists($key,$input))continue;$value=$input[$key];if(!is_string($value)||mb_strlen($value)>1000)throw new HttpException(422,'Invalid '.$label.'.');$value=trim($value);if($value===''){if($keepEmpty)$out[$key]='';continue;}
   if(in_array($key,['purchase_date','warranty_expiration'],true)&&(!preg_match('/^\d{4}-\d{2}-\d{2}$/',$value)||!($date=\DateTimeImmutable::createFromFormat('!Y-m-d',$value))||$date->format('Y-m-d')!==$value))throw new HttpException(422,'Invalid '.$label.'.');$out[$key]=$value;
  }return $out;
 }
 // All adapters use these keys. Legacy template labels are resolved without deleting their values.
 public static function fieldKey(array $field): ?string {
  if(!empty($field['archived']))return null;
  $name=strtolower(preg_replace('/[^a-zA-Z0-9]/','',(string)$field['name']));
  $aliases=['serial'=>'serial_number','serialno'=>'serial_number','serialnumber'=>'serial_number','type'=>'device_type','devicetype'=>'device_type','manufacturer'=>'manufacturer','systemmanufacturer'=>'manufacturer','manufacturername'=>'manufacturer','make'=>'manufacturer','model'=>'model','modelnumber'=>'model','systemmodel'=>'model','cpu'=>'cpu','cpuname'=>'cpu','processor'=>'cpu','cpusize'=>'cpu_specs','processorspecifications'=>'cpu_specs','ram'=>'ram','memory'=>'ram','storage'=>'storage','disk'=>'storage','mac'=>'mac_address','macaddress'=>'mac_address','purchasedate'=>'purchase_date','warrantyexpire'=>'warranty_expiration','warrantyexpiration'=>'warranty_expiration','warrantyexpiry'=>'warranty_expiration','warrantyexpirationdate'=>'warranty_expiration','warrantyexpirydate'=>'warranty_expiration','location'=>'location_text','locationtext'=>'location_text','os'=>'operating_system','operatingsystem'=>'operating_system','ipaddress'=>'ip_address','externalipaddress'=>'external_ip_address','platform'=>'platform','agentversion'=>'agent_version','action1agentversion'=>'agent_version','agentinstalled'=>'agent_installed','lastseen'=>'last_seen','lastseeninaction1'=>'last_seen','status'=>'status','action1status'=>'status','subscriptionstatus'=>'subscription_status','lastreporteduser'=>'last_user','missingcriticalupdates'=>'missing_critical_updates','missingotherupdates'=>'missing_other_updates','notes'=>'notes','action1notes'=>'notes','action1devicename'=>'title'];
  return $aliases[$name]??null;
 }
 public static function extraFields(array $fields): array {return array_values(array_filter($fields,fn($f)=>self::fieldKey($f)===null));}
 public static function effective(array $payload,array $fields=[]): array {
  $result=[];$legacyManual=[];foreach($fields as $f){$key=self::fieldKey($f);$v=$payload['values'][$f['id']]??null;if(isset(self::FIELDS[$key??''])&&is_scalar($v)&&$v!==''){$result[$key]=(string)$v;if(!in_array((int)$f['id'],$payload['managed_field_ids']??[],true))$legacyManual[$key]=(string)$v;}}
  $result=array_replace($result,$payload['reported_details']??[]);
  $sources=$payload['source_details']??[];ksort($sources,SORT_STRING);
  // Sources replace integration-owned compatibility values; manual legacy values keep priority.
  $resolved=[];foreach($sources as $values)foreach($values as $key=>$value)if($value!==null&&$value!==''&&!array_key_exists($key,$resolved))$resolved[$key]=$value;
  return array_replace($result,$resolved,$legacyManual,$payload['details']??[]);
 }
 public static function noteSources(array $payload,array $fields=[]): array {
  $sources=$payload['source_notes']??[];ksort($sources,SORT_STRING);
  foreach($fields as $f)if(self::fieldKey($f)==='notes'){
   if(strtolower((string)$f['name'])==='action1 notes'&&array_key_exists('action1',$sources))continue;
   $value=$payload['values'][$f['id']]??null;if(is_string($value)&&trim($value)!=='')$sources['legacy:'.$f['id']]=$value;
  }return $sources;
 }
 public static function notes(array $payload,array $fields=[]): string {
  $text=trim((string)($payload['notes']??''));foreach(self::noteSources($payload,$fields) as $source=>$value){$value=trim(str_replace("\r\n","\n",$value));if($value===''||!empty($payload['note_overrides'][$source]))continue;if(!str_contains("\n\n".$text."\n\n","\n\n".$value."\n\n"))$text.=($text===''?'':"\n\n").$value;}return $text;
 }
 public static function editNotes(string $submitted,array $old,array $fields,bool $mergedEditor=false): array {
  $overrides=$old['note_overrides']??[];$submitted=str_replace("\r\n","\n",$submitted);
  if((!$mergedEditor&&$submitted===(string)($old['notes']??''))||$submitted===self::notes($old,$fields))return [(string)($old['notes']??''),$overrides];
  $original="\n\n".$submitted."\n\n";foreach(self::noteSources($old,$fields) as $source=>$text){if(!empty($overrides[$source])||trim($text)==='')continue;$needle="\n\n".trim(str_replace("\r\n","\n",$text))."\n\n";$wrapped="\n\n".$submitted."\n\n";if(str_contains($original,$needle))$submitted=trim(str_replace($needle,"\n\n",$wrapped));else $overrides[$source]=true;}
  return [$submitted,$overrides];
 }
}
