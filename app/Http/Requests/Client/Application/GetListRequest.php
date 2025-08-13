<?php

namespace App\Http\Requests\Client\Application;

use Illuminate\Foundation\Http\FormRequest;

class GetListRequest extends FormRequest
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
            //Auth key
            'auth_key' => 'required',
            //Secret key
            'auth_secret' => 'required',
            //Get id from program list API
            'program_id' => 'nullable|integer',
            'searchText' => 'nullable',
            //Application tracking id
            'application_id' => 'nullable',
            'nominee_name' => 'nullable',
            'account_no' => 'nullable',
            'nid_no' => 'nullable|integer',
            'perPage' => 'nullable|integer',
            'page' => 'nullable|integer',

        ];
    }
}
