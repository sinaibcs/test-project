<?php

namespace App\Http\Requests\Mobile\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UserRequest extends FormRequest
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
            'username'                  => 'required|string|unique:users,username,deleted_at',
            'mobile'                    => 'required|numeric|regex:/^01[3-9]\d{8}$/',
            'email'                     => 'required|email|unique:users,email,deleted_at',
            'status'                    => 'sometimes|integer|in:0,1',
            'role_id'                   => 'sometimes|required|array|exists:roles,id',
            'committee_type'            => 'sometimes|required|integer|exists:lookups,id',
            'office_type'               => 'sometimes|required|integer|exists:lookups,id',
            'office_id'                 => 'sometimes|integer|exists:offices,id',
            'division_id'               => 'sometimes|required_unless:division_id,null,id,deleted_at,NULL',
            'thana_id'                  => 'sometimes|required_unless:thana_id,exists:locations,id,deleted_at,NULL',



            'union_id'             => [Rule::requiredIf($this->committee_type == 12)],
            'ward_id'             => [Rule::requiredIf($this->committee_type == 13)],
            'upazila_id'             => [Rule::requiredIf($this->committee_type == 14)],
            'city_corpo_id'             => [Rule::requiredIf($this->committee_type == 15)],
            'paurashava_id'             => [Rule::requiredIf($this->committee_type == 16)],
            'district_id'             => [Rule::requiredIf($this->committee_type == 17)],
            'committee_id'             => [Rule::requiredIf((bool)$this->committee_type)],

        ];
    }


    public function messages()
    {
        return [
            'mobile.regex' => 'Enter a valid number eg. 013xxxxxxxx'
        ];
    }






}
