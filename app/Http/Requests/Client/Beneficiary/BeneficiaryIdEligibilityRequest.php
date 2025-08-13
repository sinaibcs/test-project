<?php

namespace App\Http\Requests\Client\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

class BeneficiaryIdEligibilityRequest extends FormRequest
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
            'beneficiary_id' => ['required', 'exists:beneficiaries,beneficiary_id']
        ];
    }

    public function messages()
    {
        return [
            'beneficiary_id.required' => 'Beneficiary ID is required.',
            'beneficiary_id.exists' => 'The provided Beneficiary ID does not exist.',
        ];
    }
}
