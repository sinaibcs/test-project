<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ApplicationAllowanceValues
 *
 * @property int $id
 * @property int $applicationId
 * @property int $allowAddiFieldsId
 * @property int|null $allowAddiFieldValuesId
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereAllowAddiFieldValuesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereAllowAddiFieldsId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationAllowanceValues whereValue($value)
 * @mixin \Eloquent
 */
class ApplicationAllowanceValues extends Model
{
    use HasFactory;

 public function application()
    {
        return $this->belongsTo(Application::class, 'application_id');
    }

    public function variable()
    {
        return $this->belongsTo(Variable::class, 'variable_id');
    }

    public function subVariable()
    {
        return $this->belongsTo(Variable::class, 'sub_variable_id');
    }

    public function additionalField()
    {
        return $this->belongsTo(AdditionalFields::class, 'allow_addi_fields_id');
    }

    public function additionalFieldValues(){
        return $this->belongsTo(AdditionalFieldValues::class, 'additional_field_values');
    }
    
    public function additionalFieldValue(){
        return $this->belongsTo(AdditionalFieldValues::class, 'allow_addi_field_values_id');
    }

    public function getValueAttribute()
    {
        $value = $this->attributes['value'];
        $additionalField = $this->additionalField;

        if ($additionalField && $additionalField->type === 'file') {
            return asset('cloud/' . $value);
        }

        return $value;
    }
}
