<?php

namespace App\Http\Requests\Mobile\APIManager;

use App\Models\APIUrl;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\ValidationException;

class StoreRequest extends FormRequest
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
        ];
    }

    public function passedValidation()
    {
        if (APIUrl::where($this->all())->exists()) {
            throw ValidationException::withMessages(['url' => 'Url already exists']);
        }
    }
}
