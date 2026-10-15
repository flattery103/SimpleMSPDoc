<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Files;
use SimpleMSPDoc\Core\HttpException;
final class UploadValidator
{
 public function __construct(private int $limit=20971520){}
 public function validate(array $upload): array {
  $name=(string)($upload['name']??'');$path=(string)($upload['tmp_name']??'');$ext=strtolower(pathinfo($name,PATHINFO_EXTENSION));
  if(($upload['error']??UPLOAD_ERR_NO_FILE)!==UPLOAD_ERR_OK||!is_file($path)||$name===''||strlen($name)>190||str_contains($name,'/')||str_contains($name,'\\')||preg_match('/[\x00-\x1f\x7f]/',$name)||preg_match('/\.(php\d*|phtml|phar|html?|svg|exe|sh|js)(\.|$)/i',$name))throw new HttpException(422,'Invalid or unsafe upload filename.');
  $bytes=filesize($path);if($bytes===false||$bytes>$this->limit||$bytes===0)throw new HttpException(422,'File is empty or exceeds the upload limit.');$mime=(new \finfo(FILEINFO_MIME_TYPE))->file($path);
  $allowed=['txt'=>['text/plain'],'md'=>['text/plain'],'csv'=>['text/plain','text/csv'],'log'=>['text/plain'],'pdf'=>['application/pdf'],'png'=>['image/png'],'jpg'=>['image/jpeg'],'jpeg'=>['image/jpeg'],'webp'=>['image/webp'],'gif'=>['image/gif'],'docx'=>['application/vnd.openxmlformats-officedocument.wordprocessingml.document','application/zip'],'xlsx'=>['application/vnd.openxmlformats-officedocument.spreadsheetml.sheet','application/zip'],'pptx'=>['application/vnd.openxmlformats-officedocument.presentationml.presentation','application/zip']];
  if(!in_array($mime,$allowed[$ext]??[],true))throw new HttpException(422,'File type does not match a supported document or image format.');
  if(in_array($ext,['docx','xlsx','pptx'],true)){if(!class_exists(\ZipArchive::class))throw new HttpException(422,'Office document uploads require the PHP zip extension.');$zip=new \ZipArchive();if($zip->open($path)!==true)throw new HttpException(422,'Invalid Office document.');try{$part=match($ext){'docx'=>'word/document.xml','xlsx'=>'xl/workbook.xml','pptx'=>'ppt/presentation.xml'};if($zip->locateName('[Content_Types].xml')===false||$zip->locateName($part)===false)throw new HttpException(422,'Invalid Office document.');for($i=0;$i<$zip->numFiles;$i++)if(str_contains(strtolower($zip->getNameIndex($i)),'vbaproject'))throw new HttpException(422,'Macro-enabled documents are not accepted.');}finally{$zip->close();}}
  return ['name'=>$name,'mime'=>$mime,'bytes'=>$bytes];
 }
}
