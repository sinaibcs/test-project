<?php

namespace App\Http\Requests\Admin\Training;

use App\Constants\TrainingLookUp;
use Illuminate\Foundation\Http\FormRequest;

class TimeSlotRequest extends FormRequest
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
            'time' => 'required|string|max:20|unique:time_slots,time,'. $this->time_slot?->id,
        ];
    }


}
