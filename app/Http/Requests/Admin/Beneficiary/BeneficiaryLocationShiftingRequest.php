<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreCommitteeRequest
 */
class BeneficiaryLocationShiftingRequest extends FormRequest
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
//            'from_division_id' => 'sometimes|integer|exists:locations,id',
//            'from_district_id' => 'sometimes|integer|exists:locations,id',
//            'from_city_corp_id' => 'sometimes|integer|exists:locations,id',
//            'from_district_pourashava_id' => 'sometimes|integer|exists:locations,id',
//            'from_upazila_id' => 'sometimes|integer|exists:locations,id',
//            'from_pourashava_id' => 'sometimes|integer|exists:locations,id',
//            'from_thana_id' => 'sometimes|integer|exists:locations,id',
//            'from_union_id' => 'sometimes|integer|exists:locations,id',
//            'from_ward_id' => 'sometimes|integer|exists:locations,id',
//            'from_location_type_id' => 'sometimes|integer|exists:lookups,id',
//            'from_location_id' => 'sometimes|integer|exists:locations,id',

            'to_division_id' => 'sometimes|integer|exists:locations,id',
            'to_district_id' => 'sometimes|integer|exists:locations,id',
            'to_city_corp_id' => 'sometimes|integer|exists:locations,id',
            'to_district_pourashava_id' => 'sometimes|integer|exists:locations,id',
            'to_upazila_id' => 'sometimes|integer|exists:locations,id',
            'to_pourashava_id' => 'sometimes|integer|exists:locations,id',
            'to_thana_id' => 'sometimes|integer|exists:locations,id',
            'to_union_id' => 'sometimes|integer|exists:locations,id',
            'to_ward_id' => 'sometimes|integer|exists:locations,id',
            'to_location_type_id' => 'sometimes|integer|exists:lookups,id',
            'to_location_id' => 'sometimes|integer|exists:locations,id',

            'shifting_cause' => 'nullable|string|max:250',
            'effective_date' => 'nullable|date',
            'beneficiaries.*.beneficiary_id' => 'required|integer|exists:beneficiaries,id',
        ];
    }
}
