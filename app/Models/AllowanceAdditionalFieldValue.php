<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowanceAdditionalFieldValue extends Model
{
    use HasFactory;

    protected $table = 'additional_field_values';

    protected $fillable = ['additional_field_id', 'value'];

}
