<?php

namespace App\Exceptions;

use Exception;

class MaxBooksExceededException extends Exception
{
    public function __construct()
    {
        parent::__construct('Loan limit reached for your membership tier.', 422);
    }
}
