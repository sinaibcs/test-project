<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AllowanceProgramAge
 *
 * @property int $id
 * @property int $allowanceProgramId
 * @property int|null $genderId
 * @property int|null $minAge
 * @property int|null $maxAge
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereAllowanceProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereGenderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereMaxAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereMinAge($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereUpdatedAt($value)
 * @property float|null $amount
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAge whereAmount($value)
 * @mixin \Eloquent
 */
class AllowanceProgramAge extends Model
{
    use HasFactory;

    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)
            ->orderBy('min_age', 'asc');
    }

    public static function getName($program_id)
    {

        $permanentLocation_Division = AllowanceProgram::find($program_id);


        return $permanentLocation_Division->name_en;
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender()
    {
        return $this->belongsTo(Lookup::class, 'gender_id');
    }
}
