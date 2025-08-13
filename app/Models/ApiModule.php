<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ApiModule extends Model
{
    use HasFactory;



    public function purposes()
    {
        return $this->hasMany(ApiPurpose::class);
    }

}
