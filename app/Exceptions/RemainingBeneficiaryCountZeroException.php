<?php

namespace App\Exceptions;

use Exception;

class RemainingBeneficiaryCountZeroException extends Exception
{
    protected $message;

    public function __construct()
    {
        $this->message = __('messages.remaining_beneficiary_count_zero');
    }
}
