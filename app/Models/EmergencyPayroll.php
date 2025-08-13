<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class EmergencyPayroll extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $table = 'emergency_payrolls';

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id');

    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id');

    }

    /**
     * Get the FinancialYear that owns the EmergencyPayrollPaymentCycle
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function FinancialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id', 'id');
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

    /**
     * Get all of the emergencyPayrollDetails for the EmergencyPayroll
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function emergencyPayrollDetails(): HasMany
    {
        return $this->hasMany(EmergencyPayrollDetails::class, 'emergency_payroll_id', 'id');

    }

    public function installmentSchedule(): BelongsTo
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class, 'installment_schedule_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function emergencyAllotment(): BelongsTo
    {
        return $this->belongsTo(EmergencyAllotment::class, 'emergency_allotment_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollDetails(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(EmergencyPayrollDetails::class, 'emergency_payroll_id', 'id');
    }

    public function beneficiaries(): BelongsTo
    {
        return $this->belongsTo(EmergencyBeneficiary::class, 'emergency_beneficiary_id', 'id');
    }

}
