<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class Payroll extends Model
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

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function program()
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id', 'id');
    }

    public function installmentSchedule()
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class, 'installment_schedule_id', 'id');
    }
    public function installment()
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class, 'installment_schedule_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function allotment()
    {
        return $this->belongsTo(Allotment::class, 'allotment_id', 'id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollDetails()
    {
        return $this->hasMany(PayrollDetail::class, 'payroll_id', 'id');
    }

    public function paymentCycleDetails()
    {
        return $this->hasMany(PayrollPaymentCycleDetail::class, 'payroll_id', 'id');
    }

    /**
     * Get the approvedBy that owns the Payroll
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function approvedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by_id', 'id');
    }

    public function rejectedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'rejected_by_id', 'id');
    }

    public function submittedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'submitted_by_id', 'id');
    }

    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class, 'office_id', 'id');
    }
}
