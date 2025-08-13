<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * App\Models\ApplicationPovertyValues
 *
 * @property int $id
 * @property int $applicationId
 * @property int $variableId
 * @property int|null $subVariableId
 * @property \Illuminate\Support\Carbon|null $createdAt
 * @property \Illuminate\Support\Carbon|null $updatedAt
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues whereApplicationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues whereSubVariableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApplicationPovertyValues whereVariableId($value)
 * @mixin \Eloquent
 */
class ApplicationPovertyValues extends Model
{
    use HasFactory;

    public function sub_variable() 
    {
        return $this->belongsTo(Variable::class, 'sub_variable_id', 'id');
        // return $this->belongsTo(Variable::class, 'sub_variable_id', 'id');
    }
    public function variable()//chatgpt
    {
        return $this->belongsTo(Variable::class, 'variable_id', 'id');
    }
  
    

}
