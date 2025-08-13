<?php

namespace App\Http\Requests\Coverage;

use Illuminate\Foundation\Http\FormRequest;

class DivisionUpdateRequest extends FormRequest
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
            //

            'id'                        => 'required|exists:divisions,id',
            'name'                     => 'required|string|unique:divisions,name,'.$this->id.',id|max:255',
            'details'         => 'sometimes|string'
        ];
    }
}
