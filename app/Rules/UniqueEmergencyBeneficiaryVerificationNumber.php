<?php

namespace App\Rules;

use App\Models\EmergencyBeneficiary;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmergencyBeneficiaryVerificationNumber implements Rule
{
    public function passes($attribute, $value)
    {
        // Check for uniqueness in the database based on financial_year_id
        return !EmergencyBeneficiary::where('verification_number', $value)
            ->where('status', '=', 1)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'Already applied.';
    }
}
