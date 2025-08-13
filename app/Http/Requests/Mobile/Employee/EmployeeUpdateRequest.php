<?php

namespace App\Http\Requests\Mobile\Employee;

use Illuminate\Foundation\Http\FormRequest;

class EmployeeUpdateRequest extends FormRequest
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
            'id'                        => 'required|exists:users,id',
            'full_name'                     => 'required|string|unique:users,full_name,'.$this->id.',id|max:255',
            'email'                     => 'required|email|unique:users,email,'.$this->id.',id',
            'department_id'         => 'required|integer',
            'branch_id'         => 'nullable|integer',
            'employee_shift_id'         => 'nullable|integer',
            'phone'                           => 'required|string|unique:users,phone,'.$this->id.',id',
            'date_of_birth'                   => 'nullable|date',
            'join_date'                   => 'required|date',
            'permanent_address'                       => 'nullable|string',
            'present_address'                       => 'nullable|string',
            'gender'                       => 'nullable|string',
            'salary'                       => 'nullable|integer',
        ];
    }
}
