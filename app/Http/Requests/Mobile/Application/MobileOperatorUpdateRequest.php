<?php

namespace App\Http\Requests\Mobile\Application;

use Illuminate\Foundation\Http\FormRequest;

class MobileOperatorUpdateRequest extends FormRequest
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
            'id'                        =>'required|exists:mobile_operators,id',
             'operator' => 'required|regex:/^01[0-9]{1}$/|unique:mobile_operators,operator,'.$this->id.'',


        ];
    }
}
