<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Allotment extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function budget()
    {
        return $this->belongsTo(Budget::class, 'budget_id', 'id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
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
        return $this->belongsTo(Location::class, 'location_id', 'id');
    }
    
    public function thana()
    {
        return $this->belongsTo(Location::class, 'thana_id', 'id');
    }
    
    public function union()
    {
        return $this->belongsTo(Location::class, 'union_id', 'id');
    }
    public function pourashava()
    {
        return $this->belongsTo(Location::class, 'pourashava_id', 'id');
    }
    
    public function ward()
    {
        return $this->belongsTo(Location::class, 'ward_id', 'id');
    }

    public function type()
    {
        return $this->belongsTo(Lookup::class, 'type_id', 'id');
    }

    /**
     * Get the office that owns the Allotment
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }
}
