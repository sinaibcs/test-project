<?php

namespace App\Models;

use Process;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class PayrollPaymentProcessor extends Model
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

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class, 'bank_id', 'id');
    }
    public function branch(): HasMany
    {
        return $this->hasMany(ProcessorBranch::class, 'processor_id', 'id');
    }

    /**
     * The branches that belong to the PayrollPaymentProcessor
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(BankBranch::class, 'processor_branches', 'processor_id', 'branch_id');
    }

    public function mfs(): BelongsTo
    {
        return $this->belongsTo(Mfs::class, 'mfs_id', 'id');
    }

    /**
     * Get the ProcessorArea associated with the PayrollPaymentProcessor
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
      public function ProcessorArea(): HasOne
    {
        return $this->hasOne(PayrollPaymentProcessorArea::class, 'payment_processor_id', 'id');
    }


}
