<?php

namespace App\Exceptions;

use Exception;

class DuplicateLoanException extends Exception
{
        public function __construct()
    {
        parent::__construct('Member already has an active loan for this book.', 422);
    }
}
