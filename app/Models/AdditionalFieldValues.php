<?php

namespace App\Models;

use App\Models\AdditionalFields;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\AdditionalFieldValues
 *
 * @property int $id
 * @property int $additionalFieldId
 * @property string|null $value
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues query()
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues whereAdditionalFieldId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|AdditionalFieldValues whereValue($value)
 * @mixin \Eloquent
 */
class AdditionalFieldValues extends Model
{
    use HasFactory;
    public function newQuery($excludeDeleted = true)
    {
        return parent::newQuery($excludeDeleted)
            ->orderBy('value', 'asc');
    }
    
     public function allowAddiField()
    {
        return $this->belongsTo(AdditionalFields::class, 'additional_field_id');
    }
}
