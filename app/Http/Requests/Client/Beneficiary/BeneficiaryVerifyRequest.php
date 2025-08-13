<?php

namespace App\Http\Requests\Client\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

class BeneficiaryVerifyRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules()
    {
        return [
            'id_type' => ['required', 'in:nid_birthid,beneficiaryId'],
            'id_value' => ['required', 'numeric', 'digits_between:10,17'],
            'date_of_birth' => ['required', 'date', 'before:today'],
        ];
    }

    public function messages()
    {
        return [
            'id_type.required' => 'ID type is required.',
            'id_value.required' => 'Please provide the NID or Beneficiary ID.',
            'id_value.numeric' => 'The ID must be a valid number.',
            'id_value.digits_between' => 'The ID must be between 10 and 17 digits.',
            'date_of_birth.required' => 'Date of Birth is required.',
            'date_of_birth.before' => 'Date of Birth must be before today.',
        ];
    }
}
