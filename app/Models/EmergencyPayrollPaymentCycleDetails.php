<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyPayrollPaymentCycleDetails extends Model
{
    use HasFactory;

    protected $table = 'emergency_payroll_payment_cycle_details';
    protected $guarded = ['id'];


    public function payroll(): BelongsTo
    {
        return $this->belongsTo(EmergencyPayroll::class, 'emergency_payroll_id');
    }

    public function beneficiaries(): BelongsTo
    {
        return $this->belongsTo(EmergencyBeneficiary::class, 'emergency_beneficiary_id', 'beneficiary_id');
    }
    
    public function beneficiary(): BelongsTo
    {
        return $this->belongsTo(EmergencyBeneficiary::class, 'emergency_beneficiary_id', 'beneficiary_id');
    }


    /**
     * Get the EmergencyBeneficiary that owns the EmergencyPayrollPaymentCycleDetails
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function EmergencyBeneficiary(): BelongsTo
    {
        return $this->belongsTo(EmergencyBeneficiary::class, 'emergency_beneficiary_id', 'id');
    }

    public function EmergencyPayroll(): BelongsTo
    {
        return $this->belongsTo(EmergencyPayroll::class, 'emergency_payroll_id', 'id');
    }

    /**
     * Get the status that owns the EmergencyPayrollPaymentCycleDetails
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentStatus::class, 'status_id', 'id');
    }
      public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_name', 'id');
    }
    public function branch(): BelongsTo
    {
        return $this->belongsTo(BankBranch::class, 'branch_name', 'id');
    }

    public function mfs(): BelongsTo
    {
        return $this->belongsTo(Mfs::class, 'mfs_id', 'id');
    }
}