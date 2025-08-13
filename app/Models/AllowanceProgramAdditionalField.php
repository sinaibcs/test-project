<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AllowanceProgramAdditionalField
 *
 * @property int $id
 * @property int $allowanceProgramId
 * @property int $additionalFieldId
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField whereAdditionalFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField whereAllowanceProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField whereUpdatedAt($value)
 * @property int $fieldId
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AdditionalFields> $additionalfield
 * @property-read int|null $additionalfieldCount
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\AllowanceProgram> $allowanceprogram
 * @property-read int|null $allowanceprogramCount
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAdditionalField whereFieldId($value)
 * @mixin \Eloquent
 */
class AllowanceProgramAdditionalField extends Model
{
    use HasFactory;

    protected $table = "additional_fields_allowance_program";

    public function allowanceprogram()
    {
        return $this->hasMany(AllowanceProgram::class);
    }

    public function additionalfield()
    {
        return $this->hasMany(AdditionalFields::class);
    }
}
