<?php

namespace App\Http\Requests\Admin\Systemconfig\Allowance\AllowanceAdditionalField;

use Illuminate\Foundation\Http\FormRequest;

class AllowanceAdditionalFieldRequest extends FormRequest
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
            // ----- Field Value
            'name_en'          =>'required|string|max:150,deleted_at,NULL',
            'name_bn'          =>'required|string|max:150,deleted_at,NULL',
            'type'      =>'required',
            // ----- End Field Value

        ];


    }

}
