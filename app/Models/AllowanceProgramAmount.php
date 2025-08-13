<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\AllowanceProgramAmount
 *
 * @property int $id
 * @property int $allowanceProgramId
 * @property int|null $typeId
 * @property float $amount
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount query()
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount whereAllowanceProgramId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount whereAmount($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount whereTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AllowanceProgramAmount whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class AllowanceProgramAmount extends Model
{
    use HasFactory;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function type()
    {
        return $this->belongsTo(Lookup::class, 'type_id','id');
    }
}