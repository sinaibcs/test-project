<?php

namespace App\Http\Requests\Admin\Emergency;

use Illuminate\Foundation\Http\FormRequest;

class EmergencyPayrollRequest extends FormRequest
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
            'allotment_id' => 'required|integer|exists:emergency_allotments,id',
            'financial_year_id' => 'required|integer|exists:financial_years,id',
            'office_id' => 'nullable|integer|exists:offices,id',
            'installment_schedule_id' => 'required|integer|exists:payroll_installment_schedules,id',
            'payroll_details.*.emergency_beneficiary_id' => 'required|integer|exists:emergency_beneficiaries,beneficiary_id',
            'payroll_details.*.amount' => 'required|numeric',
            'payroll_details.*.charge' => 'required|numeric',
            'payroll_details.*.total' => 'required|numeric',
        ];
    }
}
