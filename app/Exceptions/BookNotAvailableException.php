<?php

namespace App\Exceptions;

use Exception;

class BookNotAvailableException extends Exception
{
        public function __construct()
    {
        parent::__construct('No available copy found for this book.', 422);
    }
}
