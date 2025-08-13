<?php

namespace App\Http\Requests\Admin\Training;

use App\Constants\TrainingLookUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExternalParticipantRequest extends FormRequest
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
            'username' => [
                'required',
                Rule::unique('users')
            ],
            'email' => [
                'required',
                'email',
                Rule::unique('users')
            ],
            'full_name' => [
                'required'
            ],
            'training_circular_id' => [
                'required',
                Rule::exists('training_circulars', 'id')
            ],
            'training_program_id' => [
                'required',
                Rule::exists('training_programs', 'id'),
            ],
//            'organization_id' => 'nullable|integer|min:0|max:16777215',
//            'designation' => 'nullable|string|max:255',
//            'document' => 'nullable|string|max:255',

        ];
    }


    public function messages()
    {
        return [
            'training_program_id.unique' => 'You are already registered in this program'
        ];
    }


}
