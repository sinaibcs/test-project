<?php

namespace App\Http\Requests\Admin\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyAllotmentRequest extends FormRequest
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
            'program_id' => 'sometimes|exists:allowance_programs,id',
            'name_en' => 'required|string',
            'name_bn' => 'required|string',
            'payment_cycle' => 'required|string',
            'per_person_amount' => 'required|numeric',
            'no_of_existing_beneficiary' => 'nullable|numeric',
            'no_of_new_beneficiary' => 'required|numeric',
            'division_id' => 'required|exists:locations,id',
            'district_id' => 'required|exists:locations,id',
            'location_type' => 'required',
            'city_corp_id' => 'sometimes|nullable|exists:locations,id',
            'district_pourashava_id' => 'sometimes|nullable|exists:locations,id',
            'upazila_id' => 'sometimes|nullable|exists:locations,id',
            'pourashava_id' => 'sometimes|nullable|exists:locations,id',
            'thana_id' => 'sometimes|nullable|exists:locations,id',
            'union_id' => 'sometimes|nullable|exists:locations,id',
            'financial_year_id' => 'sometimes|nullable|exists:financial_years,id',
            'starting_period' => 'required|date_format:Y-m-d',
            'closing_period' => 'required|date_format:Y-m-d',
            'status' => 'sometimes|integer|in:0,1',
        ];
    }
}
