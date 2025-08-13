<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AllowanceAdditionalField extends Model
{
    use HasFactory;
    
    protected $table = 'additional_fields';

    public function preAssignedPrograms(){
        return $this->belongsToMany(AllowanceProgram::class, 'program_additional_field_pre_assignments', 'additional_field_id', 'allowance_program_id','id','id');
    }
}
