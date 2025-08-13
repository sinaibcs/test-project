<?php

namespace App\Http\Services\Admin\Payment;

use Carbon\Carbon;
use App\Models\PayrollPaymentCycle;
use Illuminate\Support\Facades\Http;
use App\Models\PayrollPaymentCycleDetail;
use App\Models\PayrollInstallmentSchedule;

class IbusDataService
{
    public function genPayrollPaymentCycleData(PayrollPaymentCycle $payrollPaymentCycle){
        $year = $payrollPaymentCycle->financialYear;
        $dateRange = $this->getDateRangeById($payrollPaymentCycle->installment ,$year->start_date, $year->end_date);
        $parentProgram = $payrollPaymentCycle->program->parent;
        if($parentProgram){
            $ministryCode = (string) $parentProgram->ministry_code;
            $schemeCode = (string) $parentProgram->scheme_code;
        }else{
            $ministryCode = (string) $payrollPaymentCycle->program->ministry_code;
            $schemeCode = (string) $payrollPaymentCycle->program->scheme_code;
        }
        $data = [
            "cycle" => [
                "allowanceAmount" =>(int) floor($payrollPaymentCycle->total_amount),
                "approvalComment" => "approved",
                "approvalStatus" => 1,
                "cycleName" => $payrollPaymentCycle->name_en,
                "startDate" => $dateRange['start_date'],
                "endDate" => $dateRange['end_date'],
                "fiscalYear" => $year->financial_year,
                "id" => $payrollPaymentCycle->id,
                "ministryCode" => $ministryCode,
                "schemeCode" => $schemeCode,
                "totalBeneficiary" => $payrollPaymentCycle->total_beneficiaries
            ]
        ];
        return $data;
    }

    public function genPayrollPaymentData(PayrollPaymentCycle $payrollPaymentCycle){
        $details = $payrollPaymentCycle->PaymentCycleDetails()->with([
            'beneficiaryByBenId' => function ($q) {
                $q->select([
                    'id', 'account_name', 'name_en', 'account_number', 'account_type', 'mobile', 
                    'verification_type', 'verification_number', 'beneficiary_id',
                    'bank_id', 'bank_branch_id', 'mfs_id',
                    'permanent_division_id', 'permanent_district_id', 
                    'permanent_upazila_id', 'permanent_thana_id', 'permanent_union_id'
                ])->with([
                    'bank:id,name_en',
                    'branch:id,name_en,routing_number',
                    'mfs:id,name_en,routing_number',
                    'permanentDivision:id,code',
                    'permanentDistrict:id,code',
                    'permanentUpazila:id,code',
                    'permanentThana:id,code',
                    'permanentUnion:id,code'
                ]);
            }
        ])->select(['id', 'total_amount', 'beneficiary_id'])->where('status_id', 4)->get();

        $parentProgram = $payrollPaymentCycle->program->parent;
        if($parentProgram){
            $ministryCode = (string) $parentProgram->ministry_code;
            $schemeCode = (string) $parentProgram->scheme_code;
        }else{
            $ministryCode = (string) $payrollPaymentCycle->program->ministry_code;
            $schemeCode = (string) $payrollPaymentCycle->program->scheme_code;
        }

        $data = $details->map(function ($detail) use ($payrollPaymentCycle, $ministryCode, $schemeCode) {
            $beneficiary = $detail->beneficiaryByBenId;
            return [
                "accountName"=> preg_replace('/[^a-zA-Z\s]/', '', trim($beneficiary->account_name?? $beneficiary->name_en)),
                "accountNumber"=> (string) $beneficiary->account_number,
                "accountType"=> $beneficiary->account_type == 1? "Savings Account" : "Mobile Banking",
                "allowanceAmount"=> (int) floor($detail->total_amount),
                "bankName"=> $beneficiary->account_type == 1? $beneficiary->bank->name_en : $beneficiary->mfs->name_en,
                "beneficiaryName"=> preg_replace('/[^a-zA-Z\s]/', '', trim($beneficiary->name_en)),
                "branchName"=> $beneficiary->account_type == 1? $beneficiary->branch->name_en : "",
                "cycleId"=> $payrollPaymentCycle->id,
                "district"=> $this->addZeroIfRequired($beneficiary->permanentDistrict->code),
                "division"=> $this->addZeroIfRequired($beneficiary->permanentDivision->code),
                "eunion"=> $this->addZeroIfRequired($beneficiary->permanentUnion?->code),
                "upozila"=> $this->addZeroIfRequired($beneficiary->permanentUpazila?->code?? $beneficiary->permanentThana->code),
                "id"=> $detail->id,
                "mobile"=> $beneficiary->mobile,
                "nid"=> $beneficiary->verification_number,
                "paymentStatus"=> "1",
                "paymentType"=> $beneficiary->account_type == 1? "BANK" : "MOBILE",
                "referenceNo"=> $beneficiary->beneficiary_id,
                "routingNumber"=> (string) ($beneficiary->account_type == 1? $beneficiary->branch->routing_number : $beneficiary->mfs->routing_number),
                "ministryCode" => $ministryCode,
                "schemeCode" => $schemeCode,
            ];
        })->toArray();
        return [
            "info"=> $data,
        ];
    }

    public function genPayrollSupplementaryPaymentData($paymentcycleId, $paymentCycleDetailsIds){
        $payrollPaymentCycle = PayrollPaymentCycle::find($paymentcycleId);
        $details = PayrollPaymentCycleDetail::whereIn('id',$paymentCycleDetailsIds)->with([
            'beneficiaryByBenId' => function ($q) {
                $q->select([
                    'id', 'account_name', 'name_en', 'account_number', 'account_type', 'mobile', 
                    'verification_type', 'verification_number', 'beneficiary_id',
                    'bank_id', 'bank_branch_id', 'mfs_id',
                    'permanent_division_id', 'permanent_district_id', 
                    'permanent_upazila_id', 'permanent_thana_id', 'permanent_union_id'
                ])->with([
                    'bank:id,name_en',
                    'branch:id,name_en,routing_number',
                    'mfs:id,name_en,routing_number',
                    'permanentDivision:id,code',
                    'permanentDistrict:id,code',
                    'permanentUpazila:id,code',
                    'permanentThana:id,code',
                    'permanentUnion:id,code'
                ]);
            }
        ])->select(['id', 'total_amount', 'beneficiary_id'])->get();

        $parentProgram = $payrollPaymentCycle->program->parent;
        if($parentProgram){
            $ministryCode = (string) $parentProgram->ministry_code;
            $schemeCode = (string) $parentProgram->scheme_code;
        }else{
            $ministryCode = (string) $payrollPaymentCycle->program->ministry_code;
            $schemeCode = (string) $payrollPaymentCycle->program->scheme_code;
        }

        $data = $details->map(function ($detail) use ($payrollPaymentCycle, $ministryCode, $schemeCode) {
            $beneficiary = $detail->beneficiaryByBenId;
            return [
                "accountName"=> preg_replace('/[^a-zA-Z\s]/', '', trim($beneficiary->account_name?? $beneficiary->name_en)),
                "accountNumber"=> (string) $beneficiary->account_number,
                "accountType"=> $beneficiary->account_type == 1? "Savings Account" : "Mobile Banking",
                "allowanceAmount"=> (int) floor($detail->total_amount),
                "bankName"=> $beneficiary->account_type == 1? $beneficiary->bank->name_en : $beneficiary->mfs->name_en,
                "beneficiaryName"=> preg_replace('/[^a-zA-Z\s]/', '', trim($beneficiary->name_en)),
                "branchName"=> $beneficiary->account_type == 1? $beneficiary->branch->name_en : "",
                "cycleId"=> $payrollPaymentCycle->id,
                "district"=> $this->addZeroIfRequired($beneficiary->permanentDistrict->code),
                "division"=> $this->addZeroIfRequired($beneficiary->permanentDivision->code),
                "eunion"=> $this->addZeroIfRequired($beneficiary->permanentUnion?->code),
                "upozila"=> $this->addZeroIfRequired($beneficiary->permanentUpazila?->code?? $beneficiary->permanentThana->code),
                "id"=> $detail->id,
                "mobile"=> $beneficiary->mobile,
                "nid"=> $beneficiary->verification_number,
                "paymentStatus"=> "1",
                "paymentType"=> $beneficiary->account_type == 1? "BANK" : "MOBILE",
                "referenceNo"=> $beneficiary->beneficiary_id,
                "routingNumber"=> (string) ($beneficiary->account_type == 1? $beneficiary->branch->routing_number : $beneficiary->mfs->routing_number),
                "ministryCode" => $ministryCode,
                "schemeCode" => $schemeCode,
            ];
        })->toArray();
        return [
            "info"=> $data,
        ];
    }

    public function genPayrollReconciliationPullData(PayrollPaymentCycle $payrollPaymentCycle){
        $year = $payrollPaymentCycle->financialYear;
        $parentProgram = $payrollPaymentCycle->program->parent;
        if($parentProgram){
            $ministryCode = (string) $parentProgram->ministry_code;
            $schemeCode = (string) $parentProgram->scheme_code;
        }else{
            $ministryCode = (string) $payrollPaymentCycle->program->ministry_code;
            $schemeCode = (string) $payrollPaymentCycle->program->scheme_code;
        }
        $data = [
            "fiscalYear" => $year->financial_year,
            "ministryCode" => $ministryCode,
            "schemeCode" => $schemeCode,
            "paymentCycle" => $payrollPaymentCycle->name_en,
        ];
        return $data;
    }

    private function addZeroIfRequired(?string $code): string{
        $newCode = $code;
        if ($code == null) {
            $newCode = "00";
        }
        if(strlen($code) == 1){
            $newCode = "0$code";
        }
        return (string) $newCode;
    }

     /**
     * Get the date range for a given installment schedule ID within a fiscal year.
     *
     * @param PayrollInstallmentSchedule $schedule
     * @param string $fiscalYearStart Start date of fiscal year (Y-m-d, e.g., '2024-08-01')
     * @param string $fiscalYearEnd End date of fiscal year (Y-m-d, e.g., '2025-07-30')
     * @return array|null
     */
    public function getDateRangeById(PayrollInstallmentSchedule $schedule, string $fiscalYearStart, string $fiscalYearEnd): ?array
    {

        if (!$schedule) {
            return null;
        }

        // Extract the installment_name to parse date range
        $installmentName = $schedule->installment_name;

        // Parse fiscal year start and end
        $fiscalStart = Carbon::parse($fiscalYearStart, 'Asia/Dhaka'); // Server is in BST (Asia/Dhaka)
        $fiscalEnd = Carbon::parse($fiscalYearEnd, 'Asia/Dhaka'); // Server is in BST (Asia/Dhaka)

        // Match the months or date ranges using regex
        if (preg_match('/\((.*?)\)/', $installmentName, $matches)) {
            $dateRange = $matches[1];
            $dates = explode(' - ', $dateRange); // Check if it's a range

            if (count($dates) === 2) {
                // Parse start and end months in the local time zone (Asia/Dhaka)
                $start = Carbon::parse($dates[0] . ' ' . $fiscalStart->year, 'Asia/Dhaka');
                $end = Carbon::parse($dates[1] . ' ' . $fiscalStart->year, 'Asia/Dhaka');

                // Adjust the year for the fiscal range
                if ($start < $fiscalStart) {
                    $start->addYear();
                }
                if ($end < $start) {
                    $end->addYear();
                }

                // Convert to UTC before formatting as ISO 8601 with Z
                return [
                    'start_date' => $start->startOfMonth()->utc()->format('Y-m-d\TH:i:s.v\Z'), // Convert to UTC
                    'end_date' => $end->endOfMonth()->utc()->format('Y-m-d\TH:i:s.v\Z'), // Convert to UTC
                ];
            } else {
                // Single month
                $month = Carbon::parse($dateRange . ' ' . $fiscalStart->year, 'Asia/Dhaka');

                // Adjust year for fiscal range
                if ($month < $fiscalStart) {
                    $month->addYear();
                }

                // Convert to UTC before formatting as ISO 8601 with Z
                return [
                    'start_date' => $month->startOfMonth()->utc()->format('Y-m-d\TH:i:s.v\Z'), // Convert to UTC
                    'end_date' => $month->endOfMonth()->utc()->format('Y-m-d\TH:i:s.v\Z'), // Convert to UTC
                ];
            }
        }

        return null; // Return null if parsing fails
    }

    
}