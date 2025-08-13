<?php

namespace App\Http\Requests\Admin\Allotment;

use Illuminate\Foundation\Http\FormRequest;

class AlotmentRequest extends FormRequest
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
        if (\Request::routeIs('allotment.store'))
        {
            return [
                'program_id' => 'required',
                'location_id' => 'required',
                'financial_year_id' => 'required',
            ];
        }

        if (\Request::routeIs('allotment.update'))
        {
            return [
                'program_id' => 'required',
                'location_id' => 'required',
                'financial_year_id' => 'required',
            ];
        }

    }
}
