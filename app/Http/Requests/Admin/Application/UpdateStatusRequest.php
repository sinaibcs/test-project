<?php

namespace App\Http\Requests\Admin\Application;

use App\Constants\ApplicationStatus;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateStatusRequest extends FormRequest
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
            // 'applications_id' => 'required|array|exists:applications,id',
            'committee_id' => [Rule::requiredIf($this->status == ApplicationStatus::FORWARD),
                'integer',
                'exists:committees,id',
            ],
            'remark' => 'nullable|string|max:1024',
            // 'status' => 'required|'. Rule::in(array_keys([...ApplicationStatus::ALL, 6])),
        ];
    }
    public function messages(): array
    {
        return [
            'applications_id.required' => 'You have to select at least one applicant.',
        ];
    }
}
