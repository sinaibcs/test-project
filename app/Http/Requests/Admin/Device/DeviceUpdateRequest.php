<?php

namespace App\Http\Requests\Admin\Device;

use Illuminate\Foundation\Http\FormRequest;

class DeviceUpdateRequest extends FormRequest
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
            'id'    => 'required|exists:devices,id',
            'user_id' => "required|exists:users,user_id",
//            'ip_address' => "required|ip",
            'name' => "required",
            'device_id' => "required",
            'device_type' => "required",
            'purpose_use' => "sometimes",
        ];
    }
}
