<?php

namespace App\Http\Requests\Mobile\Geographic\Thana;

use Illuminate\Foundation\Http\FormRequest;

class ThanaRequest extends FormRequest
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
            'location_type' => 'required|integer|exists:lookups,id',
            'city_corporation_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'division_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'district_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'name_en' => 'required|string|max:50|unique:locations,name_en,NULL,id,deleted_at,NULL',
            'name_bn' => 'required|string|max:50|unique:locations,name_bn,NULL,id,deleted_at,NULL',
            'code' => 'required|string|max:6|unique:locations,code,NULL,id,deleted_at,NULL'
        ];
    }
}
