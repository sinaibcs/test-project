<?php

namespace App\Http\Requests\Admin\Role;

use Illuminate\Foundation\Http\FormRequest;

class RoleUpdateRequest extends FormRequest
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
            'id'                          =>'required|exists:roles,id',
            'name_en'                     => 'required|string|max:50',
            'name_bn'                     => 'required|string|max:50',
            'status' => 'sometimes',
            'comment' => 'sometimes|string|max:120',
            'code' => 'required|string|max:6'
        ];
    }
}
