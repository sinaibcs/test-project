<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class API extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = "apis";

    protected $guarded = ['id'];






}
