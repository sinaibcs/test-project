<?php

namespace App\Http\Requests\Client\Beneficiary;

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
            'nominee_name' => 'nullable',
            'account_number' => 'nullable',
            'nid' => 'nullable',
            'status' => 'nullable',

            //Get id from division list API
            'division_id' => 'nullable|integer',
            //Get id from district list API
            'district_id' => 'nullable|integer',
            //Get id from city corporation list API
            'city_corp_id' => 'nullable|integer',
            //Get id from district porashava list API
            'district_pourashava_id' => 'nullable|integer',
            //Get id from upazila list API
            'upazila_id' => 'nullable|integer',
            //Get id from porashava list API
            'pourashava_id' => 'nullable|integer',
            //Get id from thana list API
            'thana_id' => 'nullable|integer',
            //Get id from union list API
            'union_id' => 'nullable|integer',
            //Get id from ward list API
            'ward_id' => 'nullable|integer',
            'perPage' => 'nullable|integer',
            'page' => 'nullable|integer',

        ];
    }
}
