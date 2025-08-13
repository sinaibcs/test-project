<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GrievanceSubject extends Model
{
    use HasFactory;

    public function grievanceType()
    {
        return $this->belongsTo(GrievanceType::class,'grievance_type_id');
    }
}