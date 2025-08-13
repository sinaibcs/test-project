<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateContactInfoRequest extends FormRequest
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
            'current_division_id' => 'required|integer|exists:locations,id',
            'current_district_id' => 'required|integer|exists:locations,id',
            'current_city_corp_id' => 'sometimes|integer|exists:locations,id',
            'current_district_pourashava_id' => 'sometimes|integer|exists:locations,id',
            'current_upazila_id' => 'sometimes|integer|exists:locations,id',
            'current_pourashava_id' => 'sometimes|integer|exists:locations,id',
            'current_thana_id' => 'sometimes|integer|exists:locations,id',
            'current_union_id' => 'sometimes|integer|exists:locations,id',
            'current_ward_id' => 'sometimes|integer|exists:locations,id',
            'current_post_code' => 'required',
            'current_address' => 'required',
            'permanent_division_id' => 'required|integer|exists:locations,id',
            'permanent_district_id' => 'required|integer|exists:locations,id',
            'permanent_city_corp_id' => 'sometimes|integer|exists:locations,id',
            'permanent_district_pourashava_id' => 'sometimes|integer|exists:locations,id',
            'permanent_upazila_id' => 'sometimes|integer|exists:locations,id',
            'permanent_pourashava_id' => 'sometimes|integer|exists:locations,id',
            'permanent_thana_id' => 'sometimes|integer|exists:locations,id',
            'permanent_union_id' => 'sometimes|integer|exists:locations,id',
            'permanent_ward_id' => 'sometimes|integer|exists:locations,id',
            'permanent_post_code' => 'required',
            'permanent_address' => 'required',
            'mobile' => 'required',
            'email' => 'nullable|email',
        ];
    }
}
