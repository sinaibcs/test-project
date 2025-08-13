<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ApplicationPovertyValues;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * App\Models\Variable
 *
 * @property int $id
 * @property int|null $parentId
 * @property string $nameEn
 * @property float|null $score
 * @property int $fieldType
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @property string|null $deletedAt
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Variable> $children
 * @property-read int|null $childrenCount
 * @property-read Variable|null $parent
 * @method static \Illuminate\Database\Eloquent\Builder|Variable newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variable newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Variable query()
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereFieldType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereNameEn($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereScore($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Variable whereUpdatedAt($value)
 * @mixin \Eloquent
 */
class Variable extends Model
{
    use HasFactory,SoftDeletes;

    protected $fillable = [
        'name_en',
        'score',
    ];

    // public function newQuery($excludeDeleted = true)
    // {
    //     return parent::newQuery($excludeDeleted)
    //         ->orderBy('name_en', 'asc');
    // }

    public function parent()
    {
        return $this->belongsTo(Variable::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(Variable::class, 'parent_id');
    }
     public function subVariables()
    {
        return $this->hasMany(Variable::class, 'parent_id');
    }

    public function povertyValues()
    {
        return $this->hasMany(ApplicationPovertyValues::class, 'variable_id');
    }
     public function allowAddiFieldValues()
    {
        return $this->belongsToMany(AdditionalFieldValues::class, 'application_allowance_values', 'allow_addi_fields_id', 'allow_addi_field_values_id')
            ->withPivot('value');
    }
  
}
