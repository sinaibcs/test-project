<?php

namespace App\Http\Requests\Coverage;

use Illuminate\Foundation\Http\FormRequest;

class ZoneUpdateRequest extends FormRequest
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
            'id'                        => 'required|exists:zones,id',
            'name'                     => 'required|string|unique:zones,name,'.$this->id.',id|max:255',
            'area_id'         => 'required|integer',
            'home_delivery'         => 'sometimes|integer',
            'charge_one_kg'         => 'sometimes|integer',
            'charge_two_kg'         => 'sometimes|integer',
            'charge_three_kg'         => 'sometimes|integer',
            'charge_extra_per_kg'         => 'sometimes|integer',
            'cod_charge'         => 'sometimes|integer'
        ];
    }
}
