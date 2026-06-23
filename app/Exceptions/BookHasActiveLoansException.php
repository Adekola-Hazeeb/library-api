<?php

namespace App\Exceptions;

use Exception;

class BookHasActiveLoansException extends Exception
{

    public function __construct()
    {
        parent::__construct(
            'Cannot retire this book: there are active loans outstanding.',
            409
        );
    }
}
