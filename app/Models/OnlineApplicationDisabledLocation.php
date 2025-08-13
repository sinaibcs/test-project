<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OnlineApplicationDisabledLocation extends Model
{
    use HasFactory;

    protected $fillable = ['location_id', 'allowance_program_id'];

    public $timestamps = false;
}
