<?php

namespace App\Http\Requests\Admin\Training;

use App\Constants\TrainingLookUp;
use App\Models\TrainingProgramParticipant;
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
            'training_circular_id' => [
                'required',
                Rule::exists('training_circulars', 'id')
            ],
            'training_program_id' => [
                'required',
                Rule::exists('training_programs', 'id'),
                Rule::unique(TrainingProgramParticipant::class, 'training_program_id')
                    ->where('training_circular_id', $this->training_circular_id)
                    ->where('user_id', $this->participant->user_id)
                    ->ignore($this->participant->id)
            ],
            'status' => 'nullable',
            'passing_date' => 'nullable|date'

        ];
    }


    public function prepareForValidation()
    {
        $this->merge(
            [
                'passing_date' => $this->status == 1 ? now() : null
            ]
        );
    }


}
