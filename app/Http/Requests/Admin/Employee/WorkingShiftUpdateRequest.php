<?php

namespace App\Http\Requests\Admin\Employee;

use Illuminate\Foundation\Http\FormRequest;

class WorkingShiftUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'id'                        => 'required|exists:working_shifts,id',
            'name'                     => 'required|string|unique:working_shifts,name,'.$this->id.',id|max:255',
            'type'         => 'required|string',
            'start_at'         => 'sometimes|required',
            'end_at'         => 'sometimes|required',
            'weekdays' => 'required'
        ];
    }
}
