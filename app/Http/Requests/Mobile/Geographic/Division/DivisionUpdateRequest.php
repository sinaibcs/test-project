<?php

namespace App\Http\Requests\Mobile\Geographic\Division;

use App\Rules\UniqueDemoGraphicRule;
use Illuminate\Foundation\Http\FormRequest;

class DivisionUpdateRequest extends FormRequest
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
        'id'    => 'required|exists:locations,id,deleted_at,NULL',
        'name_en'   => ['required',new UniqueDemoGraphicRule($this->id, 'division')],
        'name_bn'   => ['required',new UniqueDemoGraphicRule($this->id, 'division')],
        'code'  => 'required|string|max:6|unique:locations,code,'.$this->id.',id,deleted_at,NULL',
        ];
    }
}
