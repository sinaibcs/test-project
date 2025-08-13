<?php

namespace App\Http\Requests\Admin\Geographic;

use App\Models\Location;
use Illuminate\Foundation\Http\FormRequest;

class WardRequest extends FormRequest
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
            'city_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'city_thana_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'district_pouro_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'division_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'district_id' => 'required|integer|exists:locations,id,deleted_at,NULL',
            'thana_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            'union_id' => 'sometimes|integer|exists:locations,id,deleted_at,NULL',
            // 'name_en' => 'required|string|max:50|unique:locations,name_en,NULL,id,deleted_at,NULL',
            // 'name_bn' => 'required|string|max:50|unique:locations,name_bn,NULL,id,deleted_at,NULL',

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
            // 'code' => 'required|string|max:6|unique:locations,code,NULL,id,deleted_at,NULL'
        ];
    }
}
