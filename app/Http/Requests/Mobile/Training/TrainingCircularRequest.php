<?php

namespace App\Http\Requests\Mobile\Training;

use App\Constants\TrainingLookUp;
use Illuminate\Foundation\Http\FormRequest;

class TrainingCircularRequest extends FormRequest
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
            'circular_name' => 'required|string|max:255',
            'circular_type_id' => 'required|exists:lookups,id,type,' . TrainingLookUp::CIRCULAR_TYPE,
            'training_type_id' => 'required|exists:lookups,id,type,' . TrainingLookUp::TRAINING_TYPE,
            'status_id' => 'required|exists:lookups,id,type,' . TrainingLookUp::CIRCULAR_STATUS,
            'module_id' => 'required|array',
            'module_id.*' => 'exists:lookups,id,type,' . TrainingLookUp::TRAINING_MODULE,
            'no_of_participant' => 'nullable|integer|min:0',
            'no_of_participant_open' => 'nullable|integer|min:0',
            'no_of_participant_selected' => 'nullable|integer|min:0',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'class_duration' => 'nullable|string|max:10',
            'description' => 'nullable|string',
        ];
    }


    protected function prepareForValidation()
    {
        if ($this->circular_type_id == TrainingLookUp::CIRCULAR_TYPE_OPEN_ID) {
            $this->merge(
                [
                    'no_of_participant' => $this->no_of_participant_open + $this->no_of_participant_selected
                ]
            );
        }
    }
}
