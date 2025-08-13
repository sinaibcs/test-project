<?php

namespace App\Http\Requests\Admin\Training;

use App\Constants\TrainingLookUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticipantRequest extends FormRequest
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
            'full_name' => [
                Rule::requiredIf($this->is_external == 1),
            ],
            'mobile' => [
                Rule::requiredIf($this->is_external == 1),
                'numeric',
                'regex:/^01[3-9]\d{8}$/'
            ],
            'username' => [
                Rule::excludeIf(!$this->is_external),
                'required',
                'unique:users,username'
            ],
            'email' => [
                Rule::excludeIf(!$this->is_external),
                'required',
                'email',
                'unique:users,email'
            ],
            'users' => [
                'array',
                Rule::exists('users', 'id'),
            ],
            'training_circular_id' => [
                'required',
                Rule::exists('training_circulars', 'id')
            ],
            'training_program_id' => [
                'required',
                Rule::exists('training_programs', 'id'),
            ],

        ];
    }


}
