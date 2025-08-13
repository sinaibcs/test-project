<?php

namespace App\Http\Requests\Admin\API;

use App\Models\ApiPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiDataReceiveRequest extends FormRequest
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
            'organization_name' => 'required|string|max:255',
            'organization_phone' => 'required|digits_between:7,14',
            'organization_email' => 'required|email|max:255',
            'responsible_person_email' => 'required|email|max:255',
            'responsible_person_nid' => 'required|digits_between:10,17',
            'username' => 'required|string|max:60|unique:api_data_receives,username,' . $this->api_data_receive?->id,
            'whitelist_ip' => 'nullable|max:45',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date',
            'api_list' => 'required|array',
            'api_list.*' => 'exists:api_lists,id',
        ];
    }


}
