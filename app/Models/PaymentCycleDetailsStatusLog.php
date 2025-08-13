<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentCycleDetailsStatusLog extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    /**
     * Get the paymentCycleDetails that owns the PaymentCycleDetailsStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentCycleDetails(): BelongsTo
    {
        return $this->belongsTo(PayrollPaymentCycleDetail::class, 'payment_cycle_details_id', 'id');
    }

    /**
     * Get the paymentCycleStatus that owns the PaymentCycleDetailsStatusLog
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function paymentCycleStatus(): BelongsTo
    {
        return $this->belongsTo(PaymentCycleDetailsStatus::class, 'status_id', 'id');
    }
}
