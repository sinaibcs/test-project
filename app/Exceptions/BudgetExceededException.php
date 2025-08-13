<?php

namespace App\Exceptions;

use Exception;

class BudgetExceededException extends Exception
{
    protected $message;

    public function __construct()
    {
        $this->message = __('messages.budget_exceeded');
    }
}
