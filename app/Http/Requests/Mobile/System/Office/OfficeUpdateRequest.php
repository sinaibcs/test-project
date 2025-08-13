<?php

namespace App\Http\Requests\Mobile\System\Office;

use App\Models\Office;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficeUpdateRequest extends FormRequest
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
            'division_id'         => 'sometimes|integer|exists:locations,id',
            'district_id'         => 'sometimes|integer|exists:locations,id',
            'thana_id'            => 'sometimes|integer|exists:locations,id',
            'city_corpo_id'            => 'sometimes|integer|exists:locations,id',
            'office_type'         => 'required|integer|exists:lookups,id',
            'office_address'      => 'required|string',
            'comment'             => 'string|max:120,Null',
            'status'              => 'required|boolean',

            'name_en'             => ['required', 'string', 'max:50',
                Rule::unique(Office::class)
                    ->where('assign_location_id', $this->location_id)
                    ->ignore($this->id),
            ],

            'name_bn'             => ['required', 'string', 'max:50',
                Rule::unique(Office::class)
                    ->where('assign_location_id', $this->location_id)
                    ->ignore($this->id),
            ],

        ];
    }




    protected function prepareForValidation()
    {
        $this->merge(
            [
                'location_id' => $this->getLocationId()
            ]
        );
    }



    public function getLocationId()
    {
        $officeType = (int) $this->office_type;

        return match ($officeType) {
            6 => $this->division_id,
            7 => $this->district_id,
            8,10,11 => $this->upazila_id,
            9 => $this->city_id,
            35 => $this->dist_pouro_id,
            default => null
        };

    }


}
