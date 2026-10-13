<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Organizations;
use SimpleMSPDoc\Core\HttpException;
final class ContactService extends \SimpleMSPDoc\Core\RecordService {
 protected string $type='contact';
 protected function validate(array $input,?array $old=null): array {$d=parent::validate($input,$old);$email=$d['payload']['email']??'';if($email!==''&&!filter_var($email,FILTER_VALIDATE_EMAIL))throw new HttpException(422,'Invalid email address.');return $d;}
}
