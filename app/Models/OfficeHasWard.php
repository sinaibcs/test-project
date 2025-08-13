<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class OfficeHasWard extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'office_has_wards';

    protected $guarded = ['id'];

    public function parent()
    {
        return $this->belongsTo(Location::class, 'ward_id');
    }
    public function division()
    {
        return $this->belongsTo(Location::class, 'division_id');
    }
    public function district()
    {
        return $this->belongsTo(Location::class, 'district_id');
    }
    public function city()
    {
        return $this->belongsTo(Location::class, 'city_id');
    }
    public function thana()
    {
        return $this->belongsTo(Location::class, 'thana_id');
    }
    public function union()
    {
        return $this->belongsTo(Location::class, 'union_id');
    }
    public function pouro()
    {
        return $this->belongsTo(Location::class, 'pouro_id');
    }

    public function office()
    {
        return $this->belongsTo(Office::class, 'office_id');
    }
}
