<?php

namespace App\Http\Requests\Admin\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class SubmitEmergencyPayrollRequest extends FormRequest
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
            'payroll_details.*.id' => 'required|integer|exists:emergency_payroll_details,id',
            'payroll_details.*.allotment_id' => 'required|integer|exists:emergency_allotments,id',
            'payroll_details.*.payroll_id' => 'required|integer|exists:emergency_payrolls,id',
        ];
    }
}
