<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyAllotment extends Model
{
    use HasFactory;

    use SoftDeletes;

    public $primaryKey = 'id';
    public $timestamps = true;
    protected $table = 'emergency_allotments';
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function programs(): BelongsToMany
    {
        return $this->belongsToMany(AllowanceProgram::class, 'allowance_program_emergency_allotment', 'emergency_allotment_id', 'allowance_program_id');
    }


    public function division()
    {
        return $this->belongsTo(Location::class, 'division_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function district()
    {
        return $this->belongsTo(Location::class, 'district_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function upazila()
    {
        return $this->belongsTo(Location::class, 'upazila_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function cityCorporation()
    {
        return $this->belongsTo(Location::class, 'city_corp_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function districtPourosova()
    {
        return $this->belongsTo(Location::class, 'district_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function location()
    {
        return $this->belongsTo(Location::class, 'union_id', 'id');
    }

    public function emergencyPayrolls()
    {
        return $this->hasMany(EmergencyPayroll::class, 'emergency_allotment_id');
    }
}
