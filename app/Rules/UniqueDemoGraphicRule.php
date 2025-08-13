<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class UniqueDemoGraphicRule implements ValidationRule
{
    protected $id;
    protected $typeId;

    public function __construct($id, $typeId)
    {
        $this->id = $id;
        $this->typeId = $typeId;
    }
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
// name_en, name_bn, validate unique with id, type, location_type and deleted_at is null
        $model = 'App\Models\Location';
        $model = new $model;
        $model = $model->where($attribute, $value)
            ->where('type', $this->typeId)
            ->where('deleted_at', null);
            if($this->typeId == 'city'){
                // location_type => 2 = upazila, 3 = city corporation, 1 = district paurashava
                if(request()->has('location_type') && request()->location_type==2 || request()->location_type==3 || request()->location_type==1){
                    $model = $model->where('location_type', request()->location_type);
                }
            }
            if ($this->id) {
            $model = $model->where('id', '!=', $this->id);
        }
        $model = $model->first();
        if ($model) {
            $fail("The $attribute has already been taken.");
        }

    }
}
