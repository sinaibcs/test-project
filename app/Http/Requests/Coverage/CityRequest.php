<?php

namespace App\Http\Requests\Coverage;

use Illuminate\Foundation\Http\FormRequest;

class CityRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name'                     => 'required|string|unique:cities,name,NULL,id|max:255',
            'post_code'         => 'required',
            'division_id'         => 'required'
        ];
    }
}
