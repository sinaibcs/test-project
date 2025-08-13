<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyBeneficiary extends Model
{
    use HasFactory;
    // use SoftDeletes;
    protected $guarded = ['id'];
    protected $table = 'emergency_beneficiaries';
    public static function permanentDistrictGeoCode($location_id)
    {
        $permanentLocation = Location::find($location_id);
        while ($permanentLocation->type != 'district') {
            $permanentLocation = Location::find($permanentLocation->parent_id);
        }
        return $permanentLocation;
    }
    public function emergencyAllotment(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(EmergencyAllotment::class, 'allotment_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function gender(): BelongsTo
    {
        return $this->belongsTo(Lookup::class, 'gender_id');
    }

    public function religion()
    {
        return $this->belongsTo(Lookup::class, 'religion');
    }

    public function education()
    {
        return $this->belongsTo(Lookup::class, 'education_status');
    }

    public function profession()
    {
        return $this->belongsTo(Lookup::class, 'profession');
    }

    public function maritialStatus()
    {
        return $this->belongsTo(Lookup::class, 'marital_status');
    }

    public function nationality()
    {
        return $this->belongsTo(Lookup::class, 'marital_status');
    }

    /**
     * Get the program that owns the EmergencyBeneficiary
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentDivision(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_division_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentDistrict(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_district_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentCityCorporation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_city_corp_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentDistrictPourashava(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_district_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentUpazila(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_upazila_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentPourashava(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentThana(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_thana_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentUnion(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_union_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function currentWard(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'current_ward_id', 'id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentDivision(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_division_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentDistrict(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_district_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentCityCorporation(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_city_corp_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentDistrictPourashava(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_district_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentUpazila(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_upazila_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentPourashava(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_pourashava_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentThana(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_thana_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentUnion(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_union_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function permanentWard(): BelongsTo
    {
        return $this->belongsTo(Location::class, 'permanent_ward_id', 'id');
    }
}
