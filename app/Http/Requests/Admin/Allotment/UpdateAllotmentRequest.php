<?php

namespace App\Http\Requests\Admin\Allotment;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAllotmentRequest extends FormRequest
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
            'additional_beneficiaries' => 'required|numeric',
            'total_beneficiaries' => 'required|numeric',
            'total_amount' => 'required|numeric'
        ];
    }
}
