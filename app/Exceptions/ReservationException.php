<?php

namespace App\Exceptions;

use Exception;

class ReservationException extends Exception
{
    public function __construct(string $message, int $code = 403)
    {
        parent::__construct($message, $code);
    }
}