<?php

namespace App\Http\Requests\Admin\Training;

use App\Constants\TrainingLookUp;
use App\Models\TrainingProgram;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
            'program_name' => [
                'required',
                'string',
                Rule::unique(TrainingProgram::class)
                    ->withoutTrashed()
                    ->ignore($this->program?->id)
            ],
            'training_circular_id' => 'required|exists:training_circulars,id',
            'circular_modules' => 'required|array',
            'circular_modules.*' => 'exists:lookups,id,type,' . TrainingLookUp::TRAINING_MODULE,
            'trainers' => 'required|array',
            'trainers.*' => 'exists:trainers,id',
            'users' => 'nullable|array',
            'users.*' => 'exists:users,id',
            'description' => 'nullable',
            'start_date' => 'required|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'exam_datetime' => 'nullable|date_format:Y-m-d H:i:s',
            'on_days' => 'required|array'
        ];
    }


}
