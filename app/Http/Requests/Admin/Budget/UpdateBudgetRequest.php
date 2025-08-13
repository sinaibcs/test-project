<?php

namespace App\Http\Requests\Admin\Budget;

use Illuminate\Database\Query\Builder;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateBudgetRequest extends FormRequest
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
            'calculation_type' => 'required|integer|exists:lookups,id',
            'no_of_previous_year' => 'nullable|numeric',
            'calculation_value' => 'required|numeric',
            'remarks' => 'nullable|max:255'
        ];
    }
}
