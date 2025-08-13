<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollPaymentCycleDetail extends Model
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
    public function payrollPaymentCycle()
    {
        return $this->belongsTo(PayrollPaymentCycle::class, 'payroll_payment_cycle_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function payroll()
    {
        return $this->belongsTo(Payroll::class, 'payroll_id');
    }
    public function beneficiary()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id');
    }

    public function beneficiaries()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id','beneficiary_id');
    }
    
    public function beneficiaryByBenId()
    {
        return $this->belongsTo(Beneficiary::class, 'beneficiary_id','beneficiary_id');
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
    
    public function paymentStatus(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentStatus::class, 'status_id', 'id');
    }

    // In PayrollPaymentCycleDetail model
   public function payrollDetail() {
    return $this->belongsTo(PayrollDetail::class, 'payroll_detail_id');
    }

     public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(BankBranch::class, 'bank_branch_id', 'id');
    }

    public function mfs(): BelongsTo
    {
        return $this->belongsTo(Mfs::class, 'mfs_id', 'id');
    }  
    public function beneficiaryChangeTracking(): BelongsTo
    {
        return $this->belongsTo(BeneficiaryChangeTracking::class, 'beneficiary_id', 'beneficiary_id')->where('change_type_id',1);
    }
}