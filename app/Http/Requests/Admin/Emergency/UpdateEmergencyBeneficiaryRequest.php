<?php

namespace App\Http\Requests\Admin\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class UpdateEmergencyBeneficiaryRequest extends FormRequest
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
            'nominee_en' => 'sometimes',
            'nominee_bn' => 'sometimes',
            'nominee_verification_number' => 'sometimes|unique:emergency_beneficiaries,verification_number,' . $this->route('id'),
            'nominee_nationality' => 'sometimes',
            'nominee_relation_with_beneficiary' => 'sometimes',
            'nominee_date_of_birth' => 'sometimes|date',
            'nominee_address' => 'sometimes',
            'account_name' => 'sometimes',
            'account_number' => 'sometimes',
            'account_owner' => 'sometimes',
            'account_type' => 'sometimes',
            'bank_name' => 'sometimes',
            'branch_name' => 'sometimes',
        ];
    }

    /**
     * Get the custom messages for validator errors.
     *
     * @return array
     */
    public function messages()
    {
        return [
            // Custom error messages
            'nominee_en.required' => 'The nominee English name is required.',
            'nominee_bn.required' => 'The nominee Bangla name is required.',
            'nominee_verification_number.required' => 'The nominee verification number is required.',
            'nominee_verification_number.unique' => 'The nominee verification number must be unique.',
            'nominee_nationality.required' => 'The nominee nationality is required.',
            'nominee_relation_with_beneficiary.required' => 'The relation with the beneficiary is required.',
            'nominee_date_of_birth.required' => 'The nominee date of birth is required.',
            'nominee_address.required' => 'The nominee address is required.',
            'account_name.required' => 'The account name is required.',
            'account_number.required' => 'The account number is required.',
            'account_owner.required' => 'The account owner is required.',
            'account_type.required' => 'The account type is required.',
            'bank_name.required' => 'The bank name is required.',
            'branch_name.required' => 'The branch name is required.',
            'nominee_image.image' => 'The nominee image must be an image file.',
            'nominee_signature.image' => 'The nominee signature must be an image file.'
        ];
    }
}
