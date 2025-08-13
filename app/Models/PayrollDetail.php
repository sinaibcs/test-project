<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollDetail extends Model
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
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id', 'beneficiary_id');
    }

    public function paymentCycleDetails()
    {
        return $this->hasOne(PayrollPaymentCycleDetail::class, 'payroll_detail_id', 'id');
    }

    public function updatedBy()
    {
        return $this->belongsTo(user::class, 'updated_by_id', 'id');
    }

    /**
     * Get the status that owns the PayrollDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentStatus::class, 'status_id', 'id');
    }
    /**
     * Get all of the beneficiaryPayrollPaymentStatusLog for the PayrollDetail
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function beneficiaryPayrollPaymentStatusLog(): HasMany
    {
        return $this->hasMany(BeneficiaryPayrollPaymentStatusLog::class, 'payroll_details_id', 'id');
    }
}
