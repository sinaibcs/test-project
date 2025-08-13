<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TrainingParticipant extends Model
{
    use HasFactory;

    protected $guarded = ['id'];


    public function user()
    {
        return $this->belongsTo(User::class);
    }


    public function trainingCircular()
    {
        return $this->belongsTo(TrainingCircular::class);
    }



    public function trainingProgram()
    {
        return $this->belongsTo(TrainingProgram::class);
    }


    public function organization()
    {
        return $this->belongsTo(Lookup::class, 'organization_id', 'id');
    }



}
