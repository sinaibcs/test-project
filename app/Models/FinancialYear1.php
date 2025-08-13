<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FinancialYear1 extends Model
{
    use HasFactory;
     protected $fillable = [
        'financial_year','start_date','end_date','status'
        // Add other fillable attributes here if any
    ];
}
