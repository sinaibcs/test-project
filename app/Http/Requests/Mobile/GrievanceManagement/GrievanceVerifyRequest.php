<?php

namespace App\Http\Requests\Mobile\GrievanceManagement;

use App\Rules\UniqueBeneficiaryNumber;
use App\Rules\UniqueVerificationNumber;
use Illuminate\Foundation\Http\FormRequest;

class GrievanceVerifyRequest extends FormRequest
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
            'verification_type' => 'required|in:1,2',
            // 'verification_number'         =>      'required',
            'verification_number' => [
                'required',
                new UniqueBeneficiaryNumber(),
                new UniqueVerificationNumber(),

            ],
            'date_of_birth' => 'required|date',
        ];
    }
}
