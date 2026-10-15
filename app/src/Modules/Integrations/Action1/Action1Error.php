<?php
declare(strict_types=1);
namespace SimpleMSPDoc\Modules\Integrations\Action1;
final class Action1Error extends \SimpleMSPDoc\Core\HttpException
{
 public function __construct(string $message,int $status=502,public int $retryAfter=0){parent::__construct($status,$message);}
}
