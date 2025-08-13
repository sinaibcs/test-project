<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Schema;

class Trainer extends Model
{
    use HasFactory, SoftDeletes;


    protected $guarded = ['id'];



    protected function image(): Attribute
    {
        return new Attribute(
            get: fn($value) => $value ? asset('storage/' . $value) : null
        );
    }

    public function designation()
    {
        return $this->belongsTo(Lookup::class, 'designation_id', 'id');
    }


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function programs()
    {
        return $this->belongsToMany(TrainingProgram::class, TrainingProgramTrainer::class, 'trainer_id', 'training_program_id');
    }


    public function ratings()
    {
        return $this->hasMany(TrainingRating::class);
    }


}
