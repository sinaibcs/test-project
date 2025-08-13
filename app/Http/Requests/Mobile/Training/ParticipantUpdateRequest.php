<?php

namespace App\Http\Requests\Mobile\Training;

use App\Constants\TrainingLookUp;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ParticipantUpdateRequest extends FormRequest
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
            'full_name' => [Rule::requiredIf($this->participant->is_by_poll)],
            'training_circular_id' => [
                'required',
                Rule::exists('training_circulars', 'id')
            ],
            'training_program_id' => [
                'required',
                Rule::exists('training_programs', 'id'),
                Rule::unique('training_participants', 'training_program_id')
                    ->where('training_circular_id', $this->training_circular_id)
                    ->when($this->participant->is_by_poll, function ($q) {
                        $q->where('email', $this->participant->email);
                    }, function ($q) {
                        $q->where('user_id', $this->participant->user_id);
                    })
                    ->ignore($this->participant->id)
            ],
            'organization_id' => 'nullable|integer|min:0|max:16777215',
            'designation' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:255',

        ];
    }


}
