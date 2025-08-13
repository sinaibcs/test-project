<?php

namespace App\Http\Requests\Admin\Beneficiary;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountInfoRequest extends FormRequest
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
            'account_type' => 'required|integer|in:1,2',
            'bank_id' => 'required_if:account_type,1|nullable|integer|exists:banks,id',
            'mfs_id' => 'required_if:account_type,2|nullable|integer|exists:mfs,id',
            'bank_branch_id' => 'required_if:account_type,1|nullable|integer|exists:bank_branches,id',
            'account_name' => 'required',
            'account_owner' => 'required',
            'account_number' => 'required',
//            'financial_year_id' => 'required',
//            'monthly_allowance' => 'required'
        ];
    }
}
