<?php

namespace App\Http\Requests\Mobile\Geographic;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class WardUpdateRequest extends FormRequest
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
            'division_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'district_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'thana_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'union_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'name_en' => [
                'required',
                'string',
                'max:50',
                function ($attribute, $value, $fail) {
                    if ($this->city_thana_id != null) {
                        if (
                            Location::where('name_en', $value)
                            ->where('id', '!=', $this->id)
                            ->where('parent_id', $this->city_thana_id)
                            ->exists()
                        ) {

                            $fail('The ' . $attribute . ' has already been taken.');
                        }
                    }
                    if ($this->district_pouro_id != null) {
                        if (
                            Location::where('name_en', $value)
                            ->where('id', '!=', $this->id)
                            ->where('parent_id', $this->district_pouro_id)
                            ->exists()
                        ) {
                            $fail('The ' . $attribute . ' has already been taken.');
                        }
                    }

                    if ($this->union_id != null) {
                        if (
                            Location::where('name_en', $value)
                            ->where('id', '!=', $this->id)
                            ->where('parent_id', $this->union_id)
                            ->exists()
                        ) {
                            $fail('The ' . $attribute . ' has already been taken.');
                        }
                    }
                },
            ],
            // 'name_bn' => [
            //     'required',
            //     'string',
            //     'max:50',
            //     function ($attribute, $value, $fail) {
            //         if ($this->city_thana_id != null) {
            //             if (
            //                 Location::where('name_bn', $value)
            //                 ->where('id', '!=', $this->id)
            //                 ->where('parent_id', $this->city_thana_id)
            //                 ->exists()
            //             ) {

            //                 $fail('The ' . $attribute . ' has already been taken.');
            //             }
            //         }
            //         if ($this->district_pouro_id != null) {
            //             if (
            //                 Location::where('name_bn', $value)
            //                 ->where('id', '!=', $this->id)
            //                 ->where('parent_id', $this->district_pouro_id)
            //                 ->exists()
            //             ) {
            //                 $fail('The ' . $attribute . ' has already been taken.');
            //             }
            //         }

            //         if ($this->union_id != null) {
            //             if (
            //                 Location::where('name_bn', $value)
            //                 ->where('id', '!=', $this->id)
            //                 ->where('parent_id', $this->union_id)
            //                 ->exists()
            //             ) {
            //                 $fail('The ' . $attribute . ' has already been taken.');
            //             }
            //         }
            //     },
            // ],

            // 'name_en'   => 'required|string|max:50|unique:locations,name_en,'.$this->id.',id,deleted_at,NULL',
            // 'name_bn'   => 'required|string|max:50|unique:locations,name_bn,'.$this->id.',id,deleted_at,NULL',
            // 'code'  => 'required|string|max:6|unique:locations,code,'.$this->id.',id,deleted_at,NULL',
        ];
    }
}
