<?php

namespace App\Models;

use App\Models\PayrollPaymentProcessor;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Bank extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * Get all of the payrollPaymentProcessors for the Bank
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function payrollPaymentProcessors(): HasMany
    {
        return $this->hasMany(PayrollPaymentProcessor::class, 'bank_id', 'id');
    }

}
