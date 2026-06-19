<?php

namespace App\Exceptions;

use Exception;

class MaxRenewalsExceededException extends Exception
{
        public function __construct()
    {
        parent::__construct('Maximum renewal limit reached.', 403);
    }
}
