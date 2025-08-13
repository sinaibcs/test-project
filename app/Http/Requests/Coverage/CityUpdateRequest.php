<?php

namespace App\Http\Requests\Coverage;

use Illuminate\Foundation\Http\FormRequest;

class CityUpdateRequest extends FormRequest
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
            'id'                        => 'required|exists:cities,id',
            'name'                     => 'required|string|unique:cities,name,'.$this->id.',id|max:255',
            'division_id'         => 'required',
            'post_code'                   => 'required',
        ];
    }
}
