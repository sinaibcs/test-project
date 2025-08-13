<?php

namespace App\Http\Requests\Admin\Geographic\Village;

use Illuminate\Foundation\Http\FormRequest;

class VillageUpdateRequest extends FormRequest
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
            'id'    => 'required|exists:locations,id,deleted_at,NULL',
            'division_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'district_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'thana_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'union_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'ward_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'name_en'   => 'required|string|max:50|unique:locations,name_en,'.$this->id.',id,deleted_at,NULL',
            'name_bn'   => 'required|string|max:50|unique:locations,name_bn,'.$this->id.',id,deleted_at,NULL',
            'code'  => 'required|string|max:6|unique:locations,code,'.$this->id.',id,deleted_at,NULL',
        ];
    }
}
