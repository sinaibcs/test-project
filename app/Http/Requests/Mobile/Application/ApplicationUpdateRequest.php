<?php

namespace App\Http\Requests\Mobile\Application;

use Illuminate\Validation\Rule;
use App\Rules\UniqueMobileNumber;
use App\Rules\UniqueBeneficiaryNumber;
use App\Rules\UniqueVerificationNumber;
use Illuminate\Foundation\Http\FormRequest;

class ApplicationUpdateRequest extends FormRequest
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
            'program_id' => 'required|exists:allowance_programs,id',
            'verification_type' => 'required|in:1,2',
            // 'verification_number' => 'required|unique:applications,verification_number',
                'verification_number' => [
            'required',
             Rule::unique('applications', 'verification_number')->ignore($this->id),
             Rule::unique('beneficiaries', 'verification_number')->ignore($this->id),
            // new UniqueVerificationNumber(),
            // new UniqueBeneficiaryNumber(),
        ],
            'age'                  =>'required',
            'date_of_birth'         =>'required|date',
            'name_en'               =>'required',
            'name_bn'               =>'required',
            'father_name_en'               =>'required',
            'father_name_bn'               =>'required',
            'mother_name_en'               =>'required',
            'mother_name_bn'               =>'required',
            // 'spouse_name_en'               =>'required',
            // 'spouse_name_bn'               =>'required',
            'identification_mark'               =>'sometimes',
            // 'image'                        =>'sometimes|mimes:jpeg,jpg,png|max:2048',
            // 'signature'                        =>'sometimes|mimes:jpeg,jpg,png|max:2048',
            'nationality'                   =>'required',
            'gender_id'                     =>'required|exists:lookups,id',
            'education_status'              =>'required',


            'profession'              =>'required',
            'religion'              =>'required',
            'division_id'              =>'required|exists:locations,id',
            'district_id'              =>'required|exists:locations,id',
            'upazila'              =>'sometimes|exists:locations,id',
            'post_code'              =>'required',
            'address'              =>'required',
            'location_type'              =>'required|exists:lookups,id',
            'thana_id'              =>'sometimes|exists:locations,id',
            'union_id'              =>'sometimes|exists:locations,id',
            'city_id'              =>'sometimes|exists:locations,id',
            'city_thana_id'              =>'sometimes|exists:locations,id',
            'district_pouro_id'              =>'sometimes|exists:locations,id',
            // 'mobile'                =>'required|unique:applications,mobile',
            'mobile' => [
            'required',
            Rule::unique('applications', 'mobile')->ignore($this->id),
        ],
            'permanent_division_id'              =>'required|exists:locations,id',
            'permanent_district_id'              =>'required|exists:locations,id',
            'permanent_upazila'              =>'sometimes|exists:locations,id',
            'permanent_post_code'              =>'required',
            'permanent_address'              =>'required',
            'permanent_location_type'              =>'required|exists:lookups,id',
            'permanent_thana_id'              =>'sometimes|exists:locations,id',
            'permanent_union_id'              =>'sometimes|exists:locations,id',
            'permanent_city_id'              =>'sometimes|exists:locations,id',
            'permanent_city_thana_id'              =>'sometimes|exists:locations,id',
            'permanent_district_pouro_id'              =>'sometimes|exists:locations,id',

            'nominee_en'              =>'required',
            'nominee_bn'              =>'required',
            'nominee_verification_number'              =>'required',
            'nominee_address'              =>'required',
            // 'nominee_image'              =>'required|mimes:jpeg,jpg,png|max:2048',
            // 'nominee_signature'              =>'required|mimes:jpeg,jpg,png|max:2048',
            'nominee_relation_with_beneficiary'              =>'required',
            'nominee_nationality'              =>'required',
            'account_name'              =>'required',
            'account_owner'              =>'required',
            'account_number'              =>'required',
            'marital_status'              =>'required',
            'email'                  =>'email',
            // 'application_allowance_values' => 'required|array',
            // 'application_allowance_values.*.allowance_program_additional_fields_id' => 'required|exists:additional_fields,id',
            // 'application_pmt'             => 'required|array',
            // 'application_pmt.*.variable_id'    => 'required|exists:variables,id',
            // 'application_pmt.*.sub_variables'    => "required_unless:sub_variables,0",

        ];
    }
}
