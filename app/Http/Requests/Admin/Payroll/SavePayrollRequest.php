<?php

namespace App\Http\Requests\Admin\Payroll;

use Illuminate\Foundation\Http\FormRequest;

class SavePayrollRequest extends FormRequest
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
            'program_id' => 'required|integer|exists:allowance_programs,id',
            'financial_year_id' => 'required|integer|exists:financial_years,id',
            'office_id' => 'nullable|integer|exists:offices,id',
            'allotment_id' => 'required|integer|exists:allotments,id',
            'installment_schedule_id' => 'required|integer|exists:payroll_installment_schedules,id',
            'payroll_details.*.beneficiary_id' => 'required|integer|exists:beneficiaries,beneficiary_id',
            'payroll_details.*.amount' => 'required|numeric',
            'payroll_details.*.charge' => 'required|numeric',
            'payroll_details.*.total' => 'required|numeric',
        ];
    }
}
