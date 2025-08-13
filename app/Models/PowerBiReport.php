<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PowerBiReport extends Model
{
    use HasFactory, SoftDeletes;
    protected $guarded = ['id'];

//    protected function image(): Attribute
//    {
//        return new Attribute(
//            get: fn($value) => $value ? asset('storage/' . $value) : null
//        );
//    }
}
