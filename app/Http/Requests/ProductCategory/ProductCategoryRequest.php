<?php

namespace App\Http\Requests\ProductCategory;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProductCategoryRequest extends FormRequest
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
            'id'                     => [Rule::when(request()->isMethod('insertProductCategory'),'optional'),Rule::when(request()->isMethod('UpdateProductCategory'),'required|exists:product_categories,id')],
            'name'                     => ['required','string','unique:product_categories,name'],
            'description'         => 'nullable|string',
        ];
    }
}
