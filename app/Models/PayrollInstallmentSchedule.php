<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollInstallmentSchedule extends Model
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

    public function getStartDateAttribute($value)
    {
        $currentYear = now()->year;
        $paymentCycleParts = explode('-', $this->payment_cycle);
        $startMonth = (int)date_format(date_create($paymentCycleParts[0]), 'm');
        $startDay = 1;
        return date('Y-m-d', strtotime("$currentYear-$startMonth-$startDay"));
    }

    public function getEndDateAttribute($value)
    {
        $currentYear = now()->year;
        $paymentCycleParts = explode('-', $this->payment_cycle);
        $endMonth = (int)date_format(date_create($paymentCycleParts[1]), 'm');
        $endDay = date('t', strtotime("$currentYear-$endMonth-01")); // Last day of the month
        return date('Y-m-d', strtotime("$currentYear-$endMonth-$endDay"));
    }

    public function getDateRangeById(string $fiscalYearStart)
    {

        $installmentName = $this->installment_name;

        $fiscalStart = Carbon::parse($fiscalYearStart);

        if (preg_match('/\((.*?)\)/', $installmentName, $matches)) {
            $dateRange = $matches[1];
            $dates = explode(' - ', $dateRange); // Check if it's a range

            if (count($dates) === 2) {
                $start = Carbon::parse($dates[0] . ' ' . $fiscalStart->year);
                $end = Carbon::parse($dates[1] . ' ' . $fiscalStart->year);

                if ($start < $fiscalStart) {
                    $start->addYear();
                }
                if ($end < $start) {
                    $end->addYear();
                }

                return [
                    'start_date' => $start->startOfMOnth()->toDateTimeString(), // Convert to UTC
                    'end_date' => $end->endOfMonth()->toDateTimeString(), // Convert to UTC
                ];
            } else {
                $month = Carbon::parse($dateRange . ' ' . $fiscalStart->year);
                if ($month < $fiscalStart) {
                    $month->addYear();
                }

                return [
                    'start_date' => $month->startOfMonth()->toDateTimeString(), // Convert to UTC
                    'end_date' => $month->endOfMonth()->toDateTimeString(), // Convert to UTC
                ];
            }
        }

        return null; // Return null if parsing fails
    }

    public function installmentSettings(){
        return $this->hasMany(PayrollInstallmentSetting::class, 'installment_schedule_id');
    }
}
