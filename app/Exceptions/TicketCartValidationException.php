<?php

namespace App\Exceptions;

use Exception;

/**
 * Field => message errors raised while validating/pricing a ticket cart
 * (invalid ticket, sold out, invalid addon selection, etc). Callers turn
 * this into a 422 JSON response instead of the web flow's back()->withErrors().
 */
class TicketCartValidationException extends Exception
{
    /** @var array<string, string> */
    public array $errors;

    public function __construct(string $field, string $message)
    {
        parent::__construct($message);
        $this->errors = [$field => $message];
    }
}
