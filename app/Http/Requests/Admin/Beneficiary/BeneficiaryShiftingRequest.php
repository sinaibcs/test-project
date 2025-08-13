<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * StoreCommitteeRequest
 */
class BeneficiaryShiftingRequest extends FormRequest
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
            'to_program_id' => 'required|integer|exists:allowance_programs,id',
            'shifting_cause' => 'nullable|string|max:250',
            'activation_date' => 'required|date|after_or_equal:today',
            'beneficiaries.*.beneficiary_id' => 'required|integer|exists:beneficiaries,id',
            'beneficiaries.*.from_program_id' => 'required|integer|exists:allowance_programs,id',
        ];
    }
}
