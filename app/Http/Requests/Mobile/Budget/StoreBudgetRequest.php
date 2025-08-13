<?php

namespace App\Http\Requests\Mobile\Budget;


use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreBudgetRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'program_id' => [
                'required',
                'exists:allowance_programs,id',
                Rule::unique('budgets', 'program_id')
//                    ->where('program_id', $this->input('program_id'))
                    ->where('financial_year_id', $this->input('financial_year_id')),
            ],
            'financial_year_id' => 'required|integer|exists:financial_years,id',
            'calculation_type' => 'required|integer|exists:lookups,id',
            'no_of_previous_year' => 'nullable|numeric',
            'calculation_value' => 'required|numeric',
            'remarks' => 'nullable|max:255'
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'program_id' => 'program'
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'program_id.unique' => 'Budget already exists',
        ];
    }
}
