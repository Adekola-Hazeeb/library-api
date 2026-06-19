<?php

namespace App\Exceptions;

use Exception;

class BookHasActiveLoansException extends Exception
{
        /* Thrown when staff attempt to retire a book
       that still has members holding active loans */
    public function __construct()
    {
        parent::__construct(
            'Cannot retire this book: there are active loans outstanding.',
            409
        );
    }
}
