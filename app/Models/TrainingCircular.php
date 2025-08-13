<?php

namespace App\Models;

use App\Constants\TrainingLookUp;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TrainingCircular extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];


    public function modules()
    {
        return $this->belongsToMany(Lookup::class, TrainingModule::class, 'training_circular_id', 'module_id')
            ->where('type', TrainingLookUp::TRAINING_MODULE);
    }


    public function circularType()
    {
        return $this->belongsTo(Lookup::class, 'circular_type_id', 'id');
    }


    public function trainingType()
    {
        return $this->belongsTo(Lookup::class, 'training_type_id', 'id');
    }


    public function status()
    {
        return $this->belongsTo(Lookup::class, 'status_id', 'id');
    }


    public function programs()
    {
        return $this->hasMany(TrainingProgram::class, 'training_circular_id', 'id');
    }




}
