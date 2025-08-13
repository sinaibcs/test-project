<?php

namespace App\Http\Requests\Mobile\PMTScore;

use Illuminate\Foundation\Http\FormRequest;

class PMTScoreRequest extends FormRequest
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
            'type'          => 'required|integer',
            'location_id'   => 'required|integer',
            'score'         => 'required|numeric',
        ];
    }
}
