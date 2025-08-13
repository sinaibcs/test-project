<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use App\Models\AdditionalFields;

class ApplicationAllowanceValuePivot extends Pivot
{
    protected $table = 'application_allowance_values';

    public function additionalField()
    {
        return $this->belongsTo(AdditionalFields::class, 'allow_addi_fields_id');
    }

    public function getValueAttribute()
    {
        $value = $this->attributes['value'];
        if($value == null) return null;
        $additionalField = $this->additionalField;

        if ($additionalField && $additionalField->type === 'file') {
            return asset('cloud/' . $value);
        }

        return $value;
    }
}

