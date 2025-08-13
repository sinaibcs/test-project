<?php

namespace App\Http\Requests\Mobile\Systemconfig\Allowance;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AllowanceUpdateRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array|string>
     */
    public function rules(): array
    {
        return [
            'name_en'        => 'required|string|max:50',
            'name_bn'        => 'required|string|max:50',
            'is_active'      => 'sometimes',
            'is_age_limit'      => 'required',
            'age_limit'      => 'required|array',
            'age_limit.*.gender_id'      => 'required|exists:lookups,id',

            'age_limit.*.min_age'      => [Rule::excludeIf(!$this->is_age_limit), 'numeric', 'min:5', 'max:115', 'different:age_limit.*.max_age'],
            'age_limit.*.max_age'      => [Rule::excludeIf(!$this->is_age_limit), 'numeric', 'different:age_limit.*.min_age'],

            'amount.*.type_id'        => [Rule::excludeIf(!$this->is_disable_class), 'exists:lookups,id'],
            'amount.*.amount'        => [Rule::excludeIf(!$this->is_disable_class), 'numeric', 'min:0']
        ];
    }



    protected function prepareForValidation()
    {
        $this->merge(
            [
                'is_disable_class' => $this->is_disable_class == 'true',
                'is_age_limit' => $this->is_age_limit == 'true',
                'is_marital' => $this->is_marital == 'true',
                'is_active' => $this->is_active == 'true',
            ]
        );

    }


    public function messages()
    {
        return [
            'age_limit.*.min_age' => 'Minimum age will not use any negative number or character',
            'age_limit.*.max_age' => 'Maximum age will not use any negative number or character',
        ];
    }
}
