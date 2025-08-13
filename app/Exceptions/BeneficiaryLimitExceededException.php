<?php

namespace App\Exceptions;

use Exception;

class BeneficiaryLimitExceededException extends Exception
{
    protected $message;

    public function __construct()
    {
        $this->message = __('messages.beneficiary_limit_exceeded');
    }
}
