<?php

namespace App\Rules;

use App\Models\Application;
use App\Models\FinancialYear;
use Illuminate\Contracts\Validation\Rule;

class UniqueMobileNumber implements Rule
{
    private $financialYearId;

    /**
     * Create a new rule instance.
     *
     * @return void
     */
    public function __construct()
    {
        // Retrieve the current financial year id from the database
        $financialYear = FinancialYear::where('status', 1)->first();

        // Set the financial year id or null if not found
        $this->financialYearId = $financialYear ? $financialYear->id : null;
    }

    /**
     * Determine if the validation rule passes.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        // Check for uniqueness in the database based on financial_year_id
        return !Application::where('mobile', $value)
            ->where('financial_year_id', $this->financialYearId)
            ->exists();
    }

    /**
     * Get the validation error message.
     *
     * @return string
     */
    public function message()
    {
        return 'The :attribute has been used.';
    }
}
