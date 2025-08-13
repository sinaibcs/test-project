<?php

namespace App\Http\Requests\Admin\Branch;

use Illuminate\Foundation\Http\FormRequest;

class BranchUpdateRequest extends FormRequest
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
            'id'                        => 'required|exists:branches,id',
            'branch_name'                     => 'required|string|unique:branches,branch_name,'.$this->id.',id|max:255',
            'address'                   => 'sometimes',
            'open_hour'                   => 'sometimes',
            'branch_admin_id'                     => 'sometimes|unique:branches,branch_admin_id,'.$this->id.',id',
            'zones'                   => 'required|array',
            'branch_phone'                           => 'required|string|unique:branches,branch_phone,'.$this->id.',id',
        ];
    }
}
