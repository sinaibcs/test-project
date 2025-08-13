<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GlobalSetting extends Model
{
    use HasFactory;
     public function areaType()
    {
        return $this->belongsTo(Lookup::class, 'area_type');
    }

}
