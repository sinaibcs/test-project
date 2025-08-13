<?php

namespace App\Http\Requests\Admin\Systemconfig\FinanacialYear;

use Illuminate\Foundation\Http\FormRequest;

class FinancialUpdateRequest extends FormRequest
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
            'financialyear'         => 'required|string|max:60',
            'start_date'             => 'required|date',
            'end_date'               => 'required|date',
            'status'                 => 'required|boolean',
        ];
    }
}
