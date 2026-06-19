<?php
namespace App\Exceptions;
use Exception;
class MemberSuspendedException extends Exception
{
    public function __construct()
    {
        parent::__construct('Member account is suspended.', 403);
    }
}