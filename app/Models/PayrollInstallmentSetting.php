<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class PayrollInstallmentSetting extends Model
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
     * Get the allowance that owns the PayrollInstallmentSetting
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function allowance(): BelongsTo
    {
        return $this->belongsTo(AllowanceProgram::class, 'program_id', 'id');
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
