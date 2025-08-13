<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

/**
 * StoreCommitteeRequest
 */
class BeneficiaryReplaceRequest extends FormRequest
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
            'details' => 'required|string|max:120,deleted_at,NULL',
            'program_id' => 'required|integer|exists:allowance_programs,id',
            'committee_type_id' => 'required|integer|exists:lookups,id',
            'division_id' => 'sometimes|integer|exists:locations,id',
            'district_id' => 'sometimes|integer|exists:locations,id',

        ];
    }
}
