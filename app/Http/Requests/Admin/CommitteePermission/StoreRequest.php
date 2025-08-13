<?php

namespace App\Http\Requests\Admin\CommitteePermission;

use App\Models\Lookup;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreRequest extends FormRequest
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
            'committee_type_id' => ['required', Rule::exists(Lookup::class, 'id')->where('type', 17)],
            'approve' => 'nullable|boolean',
            'forward' => 'nullable|boolean',
            'reject' => 'nullable|boolean',
            'waiting' => 'nullable|boolean',
        ];
    }
}
