<?php

namespace App\Http\Requests\Admin\Systemconfig\FinanacialYear;

use Illuminate\Foundation\Http\FormRequest;

class FinancialRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'financial_year'         => 'required|string|max:60|unique:financial_years,financial_year',
        ];
    }
}
