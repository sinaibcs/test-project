<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class EmergencyPayrollPaymentCycle extends Model
{
    use HasFactory;

    protected $table = "emergency_payroll_payment_cycles";
    protected $guarded = ['id'];

    /**
     * Get all of the CycleDetails for the EmergencyPayrollPaymentCycle
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function CycleDetails(): HasMany
    {
        return $this->hasMany(EmergencyPayrollPaymentCycleDetails::class, 'emergency_cycle_id', 'id');
    } 
     public function PaymentCycleDetails(): HasMany
    {
        return $this->hasMany(EmergencyPayrollPaymentCycleDetails::class, 'emergency_cycle_id', 'id');
    }

     public function payroll()
    {
        return $this->belongsTo(EmergencyPayroll::class, 'emergency_payroll_id','id');
    }


    /**
     * Get the installment that owns the EmergencyPayrollPaymentCycle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class, 'installment_schedule_id', 'id');
    }

    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id', 'id');
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id', 'id');
    }

    // public function allotment(): BelongsTo
    // {
    //     return $this->belongsTo(AllowanceProgram::class, 'allotment_id', 'id');
    // }

}