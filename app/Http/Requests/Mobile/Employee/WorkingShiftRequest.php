<?php

namespace App\Http\Requests\Mobile\Employee;

use Illuminate\Foundation\Http\FormRequest;

class WorkingShiftRequest extends FormRequest
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
            'name'                     => 'required|string|unique:working_shifts,name|max:255',
            'type'         => ['required','string','in:regular,scheduled'],
            'start_at'         => ['required_if:type,==,regular','nullable','date_format:H:i'],
            'end_at'         => ['required_if:type,==,regular','nullable','date_format:H:i'],
            'weekdays' => ['required','array','min:7','max:7'],
            'weekdays.*'=>['array'],
            'weekdays.*.weekday'=>['string','required'],
            'weekdays.*.start_at'=>['required_if:type,==,scheduled','string','nullable'],
            'weekdays.*.end_at'=>['required_if:type,==,scheduled','string','nullable'],
            'weekdays.*.is_weekend'=>['integer','required'],
        ];
    }
    public function messages()
    {
        return [
            'name.required'=>'please enter shift name.',
            'name.unique'=>'please enter different shift name.',
            'type'=>'please select valid type.',
            'weekdays.*.weekday'=>'invalid weekday name at :index',
            'weekdays.*.start_at'=>'invalid weekday start time at :index',
            'weekdays.*.end_at'=>'invalid weekday end time at :index',
            'weekdays.*.is_weekend'=>'invalid weekend at :index',
            'weekdays.*.is_weekend.*.required'=>'please fill weekend at :index',

        ];
    }
}
