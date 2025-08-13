<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class EmergencyPayrollSetting extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    public function allowance(): BelongsTo
    {
        return $this->belongsTo(EmergencyAllotment::class, 'allotment_id', 'id');
    }

    /**
     * Get the installment that owns the PayrollInstallmentSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function installment(): BelongsTo
    {
        return $this->belongsTo(PayrollInstallmentSchedule::class, 'installment_schedule_id', 'id');
    }

    public function financialYear()
    {
        return $this->belongsTo(FinancialYear::class, 'financial_year_id');
    }
}
