<?php

namespace App\Http\Requests\Mobile\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchRequest extends FormRequest
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
            'branch_name'                     => 'required|string|unique:branches,branch_name|max:255',
            'address'         => 'sometimes',
            'open_hour'         => 'sometimes',
            'branch_admin_id'         => 'sometimes',
            'zones'         => 'required|array',
            'branch_phone' => 'required|string|unique:branches,branch_phone|max:14',
        ];
    }
}
