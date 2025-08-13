<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPaymentCycle extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [];
    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    protected $guarded = ['id'];

     public function program()
    {
        return $this->belongsTo(AllowanceProgram::class,'program_id','id');
    }

    public function installment()
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class,'installment_schedule_id','id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class,'financial_year_id','id');
    }

    public function PaymentCycleDetails(){
        return $this->hasMany(PayrollPaymentCycleDetail::class,'payroll_payment_cycle_id');
    }
}
