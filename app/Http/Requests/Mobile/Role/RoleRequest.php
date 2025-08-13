<?php

namespace App\Http\Requests\Mobile\Role;

use Illuminate\Foundation\Http\FormRequest;

class RoleRequest extends FormRequest
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
            'name_en'                     => 'required|string|max:50|unique:roles,name_en',
            'name_bn'                     => 'required|string|max:50|unique:roles,name_bn',
            // 'permissions'              => 'required|array'
            'status' => 'sometimes',
            'comment' => 'sometimes|string|max:120',
            'code' => 'required|string|max:6|unique:roles,code'
        ];
    }
}
