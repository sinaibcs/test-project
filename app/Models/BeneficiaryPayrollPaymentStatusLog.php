<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BeneficiaryPayrollPaymentStatusLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the program that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program(): BelongsTo
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id', 'id');
    }
    /**
     * Get the installmentSchedule that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function installmentSchedule(): BelongsTo
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class, 'installment_schedule_id', 'id');
    }
    /**
     * Get the financialYear that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialYear(): BelongsTo
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id', 'id');
    }
    /**
     * Get the beneficiary that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id', 'id');
    }
    /**
     * Get the payroll that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payroll(): BelongsTo
    {
        return $this->belongsTo(Payroll::class, 'payroll_id', 'id');
    }
    /**
     * Get the payrollDetails that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payrollDetails(): BelongsTo
    {
        return $this->belongsTo(PayrollDetail::class, 'payroll_details_id', 'id');
    }
    /**
     * Get the paymentCycle that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentCycle(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentCycle::class, 'payment_cycle_id', 'id');
    }
    /**
     * Get the paymentCycleDetails that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentCycleDetails(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentCycleDetail::class, 'payment_cycle_details_id', 'id');
    }
    /**
     * Get the status that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentStatus::class, 'status_id', 'id');
    }
    /**
     * Get the user that owns the BeneficiaryPayrollPaymentStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by', 'id');
    }
}
