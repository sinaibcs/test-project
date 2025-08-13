<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * StoreCommitteeRequest
 */
class BeneficiaryExitRequest extends FormRequest
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
            'exit_reason_id' => 'required|integer|exists:lookups,id',
            'exit_reason_detail' => 'nullable|string|max:250',
            'exit_date' => 'nullable|date',
            'beneficiaries.*.beneficiary_id' => 'required|integer|exists:beneficiaries,id',
        ];
    }
}
