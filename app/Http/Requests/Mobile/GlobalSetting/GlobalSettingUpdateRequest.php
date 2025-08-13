<?php

namespace App\Http\Requests\Mobile\GlobalSetting;

use Illuminate\Foundation\Http\FormRequest;

class GlobalSettingUpdateRequest extends FormRequest
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
               'id'               => 'required|exists:global_settings,id',
               'area_type'        => 'required|integer|exists:lookups,id',
               'value'            => 'required|string|max:50',
        ];
    }
}
