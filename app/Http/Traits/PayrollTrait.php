<?php

namespace App\Http\Traits;

use App\Models\AllowanceProgramAge;
use App\Models\AllowanceProgramAmount;
use App\Models\FinancialYear;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollInstallmentSchedule;
use App\Models\PayrollInstallmentSetting;
use App\Models\PayrollPaymentProcessor;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

trait PayrollTrait
{
    public function getCurrentInstallment($installmentId, $allowaneProgram)
    {
        // $currentDate = Carbon::now();
        // $currentYear = $currentDate->year;
        // //get current financial yer
        // $financialCurrentYear = FinancialYear::where('status', 1)->where('version', 1)->first();
        // // Fetch the installment setting
        // $installmentSetting = PayrollInstallmentSetting::with(['financialYear', 'installment'])->where('installment_schedule_id', $installmentId)
        //     ->whereHas('financialYear', function ($q) use ($financialCurrentYear, $currentYear) {
        //         $q->where('id', $financialCurrentYear->id);
        //         $q->whereYear('start_date', $currentYear);
        //     })
        //     ->whereHas('installment', function ($q) use ($paymentCycle) {
        //         $q->where('payment_cycle', $paymentCycle);
        //     })
        // ->first();
        // if (!$installmentSetting) {
        //     return null;
        // }

        $installmentSchedule = PayrollInstallmentSchedule::where('id', $installmentId)->get(['installment_name', 'installment_name_bn'])
            ->first();

        if (!$installmentSchedule) {
            return null;
        }
        $allowanceProgramAge = AllowanceProgramAge::where('allowance_program_id', $allowaneProgram->id)
            ->orderBy('gender_id', 'asc')->get(['gender_id', 'amount']);
        $allowanceProgramClass = AllowanceProgramAmount::where('allowance_program_id', $allowaneProgram->id)
            ->orderBy('type_id', 'asc')->get(['type_id', 'amount']);
        return [
            'installment' => $installmentSchedule,
            'amount' => $allowanceProgramAge->isEmpty() ? $allowanceProgramClass : $allowanceProgramAge,
        ];
    }

    public function getPaymentCycleDates($installmentScheduleId, $financial_year_id)
    {
        // Fetch the schedule record
        $schedule = DB::table('payroll_installment_schedules')
            ->select('payment_cycle', 'installment_number', 'installment_name')
            ->where('id', $installmentScheduleId)
            ->first();

        if (!$schedule) {
            return null;
        }

        $year = (int) explode('-', FinancialYear::find($financial_year_id)->financial_year)[0];
        $installmentNumber = (int)$schedule->installment_number;
        $paymentCycle = $schedule->payment_cycle;

        // Initialize start and end dates
        $startDate = null;
        $endDate = null;

        switch ($paymentCycle) {
            case 'Monthly':
                $startDate = Carbon::create($year, 7, 1)->addMonths($installmentNumber - 1);
                $endDate = $startDate->copy()->endOfMonth();
                break;

            case 'Quarterly':
                $quarterStartMonths = [7, 10, 1, 4];
                if ($installmentNumber >= 1 && $installmentNumber <= 4) {
                    $month = $quarterStartMonths[$installmentNumber - 1];
                    $adjustedYear = $month < 7 ? $year + 1 : $year;
                    $startDate = Carbon::create($adjustedYear, $month, 1);
                    $endDate = $startDate->copy()->addMonths(2)->endOfMonth();
                }
                break;

            case 'Half Yearly':
                if ($installmentNumber == 1) {
                    $startDate = Carbon::create($year, 7, 1);
                    $endDate = Carbon::create($year, 12, 31);
                } elseif ($installmentNumber == 2) {
                    $startDate = Carbon::create($year + 1, 1, 1);
                    $endDate = Carbon::create($year + 1, 6, 30);
                }
                break;

            case 'Yearly':
                if ($installmentNumber == 1) {
                    $startDate = Carbon::create($year, 7, 1);
                    $endDate = Carbon::create($year + 1, 6, 30);
                }
                break;
        }

        return (object)[
            'payment_cycle' => $paymentCycle,
            'installment_number' => $installmentNumber,
            'installment_name' => $schedule->installment_name,
            'start_date' => $startDate?->toDateString(),
            'end_date' => $endDate?->toDateString(),
        ];

    }

    public function getBeneficiaryTrackingStatus($query, $currentInstallment, $previousInstallment)
    {
        $currentYearStartDate = Carbon::now()->startOfYear()->addMonths(6)->toDateString(); // Starting from July 1st
        $currentYearEndDate = Carbon::now()->endOfYear()->addMonths(6)->subDay()->toDateString(); // Ending on June 30th of next year
        $currentEndDate = $currentInstallment->end_date;
        $previousStartDate = $previousInstallment->start_date;

        return $query->leftJoin('beneficiary_change_trackings', function ($join) use ($currentEndDate, $previousStartDate) {
            $join->on('beneficiaries.beneficiary_id', '=', 'beneficiary_change_trackings.beneficiary_id')
                ->where('beneficiary_change_trackings.change_type_id', 2)
                ->whereBetween('beneficiaries.approve_date', [$previousStartDate, $currentEndDate]);
        })
            ->selectRaw(
                'beneficiaries.*,
                 IF(beneficiary_change_trackings.id IS NOT NULL, true, false) as change_status,
                 IF(beneficiaries.approve_date BETWEEN ? AND ?, 1, 0) as isNew,
                 IF(beneficiaries.approve_date < ?, 1, 0) as isRegular',
                [$currentYearStartDate, $currentYearEndDate, $currentYearStartDate]
            );
    }

    private function previousAllPayment($installmentId, $allotment_id, $program_id, $financialYearId)
    {
        $payrollIds = Payroll::where('program_id', $program_id)
            ->where('financial_year_id', $financialYearId)
            ->where('allotment_id', $allotment_id)
            ->where('installment_schedule_id', $installmentId)
            ->where('is_rejected', 0)
            ->get()
            ->pluck('id')
            ->toArray();
        return PayrollDetail::whereIn('payroll_id', $payrollIds)->where('is_set', 1)->sum('amount');
    }

    private function calculateAmount($amount, $allotment, $program_id, $financialYearId, $payment_cycle): array
    {
        $totalAmount = 0;
        if ($payment_cycle == "Monthly") {
            $payroll_eligible_amount = $allotment->total_amount / 12;
        } else if ($payment_cycle == "Quarterly") {
            $payroll_eligible_amount = $allotment->total_amount / 4;
        } else if ($payment_cycle == "Half Yearly") {
            $payroll_eligible_amount = $allotment->total_amount / 2;
        } else {
            $payroll_eligible_amount = $allotment->total_amount;
        }

        return [
            'allowance_amount' => $totalAmount,
            'reserved' => $payroll_eligible_amount,
            'payroll_eligible_amount' => $payroll_eligible_amount,
        ];
    }

    private function query($query, $currentInstallment)
    {
        $query->selectRaw(
            'beneficiaries.*,
    allowance_program_ages.amount as gender_wise_amount,
    allowance_program_amounts.amount as type_wise_amount,
    -- Calculate the allowance amount
     IFNULL(ROUND(
        CASE
            WHEN beneficiaries.payment_start_date = ? THEN
                CASE allowance_programs.payment_cycle
                    WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                    WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                    WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                    WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                    ELSE 0
                END
            WHEN beneficiaries.payment_start_date > ? THEN
                FLOOR(DATEDIFF(?, beneficiaries.payment_start_date) / 30) * (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
            WHEN beneficiaries.payment_start_date = ? THEN
                (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
            WHEN beneficiaries.payment_start_date < ? THEN
                CASE allowance_programs.payment_cycle
                    WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                    WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                    WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                    WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                    ELSE 0
                END
        END, 2),0) as amount,

    -- Calculate the charge based on the amount
    IFNULL(ROUND(
        (
            CASE
            WHEN beneficiaries.payment_start_date = ? THEN
                CASE allowance_programs.payment_cycle
                    WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                    WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                    WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                    WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                    ELSE 0
                END
            WHEN beneficiaries.payment_start_date > ? THEN
                FLOOR(DATEDIFF(?, beneficiaries.payment_start_date) / 30) * (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
            WHEN beneficiaries.payment_start_date = ? THEN
                (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
            WHEN beneficiaries.payment_start_date < ? THEN
                CASE allowance_programs.payment_cycle
                    WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                    WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                    WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                    WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                    ELSE 0
                END
            END * (IFNULL(banks.charge, mfs.charge) / 100)
        ), 2),0) as charge,

    -- Calculate the total allowance amount as the sum of amount and charge
     IFNULL(ROUND(
        (
            CASE
            WHEN beneficiaries.payment_start_date = ? THEN
                CASE allowance_programs.payment_cycle
                    WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                    WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                    WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                    WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                    ELSE 0
                END
            WHEN beneficiaries.payment_start_date > ? THEN
                FLOOR(DATEDIFF(?, beneficiaries.payment_start_date) / 30) * (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
            WHEN beneficiaries.payment_start_date = ? THEN
                (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
            WHEN beneficiaries.payment_start_date < ? THEN
                CASE allowance_programs.payment_cycle
                    WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                    WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                    WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                    WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                    ELSE 0
                END
            END
        ), 2) + ROUND(
            (
                CASE
                WHEN beneficiaries.payment_start_date = ? THEN
                    CASE allowance_programs.payment_cycle
                        WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                        WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                        WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                        WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                        ELSE 0
                    END
                WHEN beneficiaries.payment_start_date > ? THEN
                    FLOOR(DATEDIFF(?, beneficiaries.payment_start_date) / 30) * (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                WHEN beneficiaries.payment_start_date = ? THEN
                    (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                WHEN beneficiaries.payment_start_date < ? THEN
                    CASE allowance_programs.payment_cycle
                        WHEN "Monthly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0))
                        WHEN "Quarterly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 3
                        WHEN "Half Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 6
                        WHEN "Yearly" THEN (IFNULL(allowance_program_ages.amount,0) + IFNULL(allowance_program_amounts.amount, 0)) * 12
                        ELSE 0
                    END
                END * (IFNULL(banks.charge, mfs.charge) / 100)
            ), 2) ,0) as total_allowance_amount',
            [
                $currentInstallment->start_date,
                $currentInstallment->start_date,
                $currentInstallment->end_date,
                $currentInstallment->start_date,
                $currentInstallment->start_date,

                $currentInstallment->start_date,
                $currentInstallment->start_date,
                $currentInstallment->end_date,
                $currentInstallment->start_date,
                $currentInstallment->start_date,

                $currentInstallment->start_date,
                $currentInstallment->start_date,
                $currentInstallment->end_date,
                $currentInstallment->start_date,
                $currentInstallment->start_date,

                $currentInstallment->start_date,
                $currentInstallment->start_date,
                $currentInstallment->end_date,
                $currentInstallment->start_date,
                $currentInstallment->start_date,
            ]
        )
            ->leftJoin('banks', 'banks.id', '=', 'beneficiaries.bank_id')
            ->leftJoin('mfs', 'mfs.id', '=', 'beneficiaries.mfs_id')
            ->addSelect([
                DB::raw('IFNULL(banks.charge, mfs.charge) as bank_or_mfs_charge')
            ])
            ->orderBy('beneficiaries.id')
            ->groupBy('beneficiaries.id');

        return $query->get();
    }


}
