<?php

namespace App\Http\Requests\Mobile\Geographic\District;

use App\Rules\UniqueDemoGraphicRule;
use Illuminate\Foundation\Http\FormRequest;

class DistrictRequest extends FormRequest
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
            'division_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'name_en'                              => ['required',new UniqueDemoGraphicRule($this->id, 'district')],
            'name_bn'                              => ['required',new UniqueDemoGraphicRule($this->id, 'district')],
            'code'                               => 'required|string|max:6|unique:locations,code,NULL,id,deleted_at,NULL'
        ];
    }
}
