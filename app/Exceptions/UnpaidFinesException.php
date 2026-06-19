<?php

namespace App\Exceptions;

use Exception;

class UnpaidFinesException extends Exception
{
        public function __construct()
    {
        parent::__construct('Member account is suspended.', 403);
    }
}
