<?php

namespace App\Http\Requests\Mobile\Training;

use App\Constants\TrainingLookUp;
use Illuminate\Foundation\Http\FormRequest;

class TrainingProgramRequest extends FormRequest
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
            'program_name' => 'required|string|unique:training_programs,program_name,'. $this->program?->id,
            'training_circular_id' => 'required|exists:training_circulars,id',
            'circular_modules' => 'required|array',
            'circular_modules.*' => 'exists:lookups,id,type,' . TrainingLookUp::TRAINING_MODULE,
            'trainers' => 'required|array',
            'trainers.*' => 'exists:trainers,id',
            'description' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'on_days' => 'required|array'
        ];
    }


}
