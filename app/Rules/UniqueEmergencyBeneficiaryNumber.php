<?php

namespace App\Rules;

use App\Models\EmergencyBeneficiary;
use Illuminate\Contracts\Validation\Rule;

class UniqueEmergencyBeneficiaryNumber implements Rule
{
    public function passes($attribute, $value): bool
    {
        // Check for uniqueness in the database based on financial_year_id
        return !EmergencyBeneficiary::where('verification_number', $value)
            ->exists();
    }

    public function message(): string
    {
        return 'You are already a emergency beneficiary.Can not apply again';
    }
}
