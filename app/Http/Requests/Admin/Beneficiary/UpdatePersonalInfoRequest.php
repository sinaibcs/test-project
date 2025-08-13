<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

class UpdatePersonalInfoRequest extends FormRequest
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
            'name_en' => 'required',
            'name_bn' => 'required',
            'mother_name_en' => 'required',
            'mother_name_bn' => 'required',
            'father_name_en' => 'required',
            'father_name_bn' => 'required',
            'spouse_name_en' => 'required',
            'spouse_name_bn' => 'required',
            'identification_mark' => 'required',
            'nationality' => 'required',
            'gender_id' => 'required',
            'religion' => 'required',
            'marital_status' => 'required',
            'education_status' => 'nullable',
            'profession' => 'nullable',
//            'date_of_birth' => 'required',
            'image' => 'nullable|mimes:jpeg,jpg,png|max:2048',
            'signature' => 'nullable|mimes:jpeg,jpg,png|max:2048',
        ];
    }
}
