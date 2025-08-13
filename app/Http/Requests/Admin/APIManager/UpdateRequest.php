<?php

namespace App\Http\Requests\Admin\APIManager;

use App\Models\APIUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class UpdateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'url' => 'required|string|max:255',
            'table' => 'required|string|max:64',
            'method' => 'required|string|max:7',
            'status' => 'sometimes|boolean',
        ];
    }



    public function passedValidation()
    {
        if (APIUrl::whereNot('id', $this->api_url?->id)
            ->where($this->except('status'))
            ->exists()) {
            throw ValidationException::withMessages(['url' => 'Url already exists']);
        }
    }


}
