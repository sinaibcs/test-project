<?php

namespace App\Http\Requests\Mobile\API;

use App\Models\ApiPurpose;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ApiListRequest extends FormRequest
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
            'api_purpose_id' => 'required|exists:api_purposes,id',
            'api_unique_id' => ['required',
                Rule::exists(ApiPurpose::class)
                    ->where('id', $this->api_purpose_id)
            ],
            'name' => 'required',
            'selected_columns' => 'nullable|array',
        ];
    }
}
