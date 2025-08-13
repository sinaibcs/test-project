<?php

namespace App\Http\Requests\Admin\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserUpdateRequest extends FormRequest
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
            'full_name'                 => 'required|string|max:50',
            'username'                  => 'required|string|unique:users,username,'. $this->id,
//            'mobile'                    => 'required|numeric|regex:/^01[3-9]\d{8}$/',
            'mobile'                    => 'required','regex:/^(01[3-9][0-9]{8}|০১[৩-৯][০-৯]{8})$/',
            'email'                     => 'required|email|max:200,'. $this->id,
//            'status'                    => 'sometimes|integer|in:0,1',
            'role_id'                   => 'sometimes|required|array|exists:roles,id',
            'committee_type'            => 'sometimes|required|integer|exists:lookups,id',
            'office_type'                 => ['sometimes', Rule::requiredIf($this->user_type == 1), 'exists:lookups,id'],
            'office_id'                 => ['sometimes', Rule::requiredIf(!!$this->office_type), 'exists:offices,id'],
            'division_id'               => 'sometimes|required_unless:division_id,null,id,deleted_at,NULL',
            'thana_id'                  => 'sometimes|required_unless:thana_id,exists:locations,id,deleted_at,NULL',



            'union_id'                  => [Rule::requiredIf($this->committee_type == 12)],
            'ward_id'                   => [Rule::requiredIf($this->committee_type == 13)],
            'upazila_id'                => [Rule::requiredIf($this->committee_type == 14)],
            'city_corpo_id'             => [Rule::requiredIf($this->committee_type == 15)],
            'paurashava_id'             => [Rule::requiredIf($this->committee_type == 16)],
            'district_id'               => [Rule::requiredIf($this->committee_type == 17)],
            'committee_id'              => [Rule::requiredIf((bool)$this->committee_type)],
        ];
    }



    public function messages()
    {
        return [
            'mobile.regex' => 'Enter a valid number eg. 013xxxxxxxx'
        ];
    }


    public function prepareForValidation()
    {
        $this->merge(
            [
                'programs_id' => $this->programs_id ?
                    explode(',', $this->programs_id) : null
            ]
        );
    }


}
