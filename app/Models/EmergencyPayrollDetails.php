<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmergencyPayrollDetails extends Model
{
    use HasFactory;

    protected $table = 'emergency_payroll_details';

    protected $guarded = ['id'];

    /**
     * Get the status that owns the EmergencyPayrollDetails
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function status(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentStatus::class, 'status_id', 'id');
    }

    public function emergencyBeneficiary(): BelongsTo
    {
        return $this->belongsTo(EmergencyBeneficiary::class, 'emergency_beneficiary_id', 'id');

    }
}
