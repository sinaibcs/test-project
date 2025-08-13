<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNomineeInfoRequest extends FormRequest
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
            'nominee_en' => 'required',
            'nominee_bn' => 'required',
            'nominee_verification_number' => 'required',
            'nominee_address' => 'required',
            'nominee_image' => 'nullable|mimes:jpeg,jpg,png|max:2048',
            'nominee_signature' => 'nullable|mimes:jpeg,jpg,png|max:2048',
            'nominee_relation_with_beneficiary' => 'required',
            'nominee_nationality' => 'required',
        ];
    }
}
