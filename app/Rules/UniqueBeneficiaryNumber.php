<?php

namespace App\Rules;

use Closure;
use App\Models\Beneficiary;
use Illuminate\Contracts\Validation\Rule;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueBeneficiaryNumber implements Rule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
      public function passes($attribute, $value)
    {
        // Check for uniqueness in the database based on financial_year_id
        return !Beneficiary::where('verification_number', $value)
          
            ->exists();
    }
      public function message()
    {
        return 'You are already a beneficiary.Can not apply again';
    }
    
}
