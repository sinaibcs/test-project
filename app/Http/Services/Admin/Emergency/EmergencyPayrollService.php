<?php

namespace App\Http\Services\Admin\Emergency;

use App\Http\Requests\Admin\Emergency\EmergencyPayrollRequest;
use App\Http\Requests\Admin\Emergency\SubmitEmergencyPayrollRequest;
use App\Models\AllowanceProgram;
use App\Models\AllowanceProgramAge;
use App\Models\AllowanceProgramAmount;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyBeneficiaryPayrollPaymentStatusLog;
use App\Models\EmergencyPayroll;
use App\Models\EmergencyPayrollDetails;
use App\Models\EmergencyPayrollSetting;
use App\Models\FinancialYear;
use App\Models\PayrollInstallmentSchedule;
use App\Models\PayrollPaymentProcessor;
use Arr;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\Request;


class EmergencyPayrollService
{

    public function getProgramInfo($program_id)
    {
        $allowance = AllowanceProgram::findOrFail($program_id);
        $allowance_age = AllowanceProgramAge::where('allowance_program_id', $program_id)->with('gender')->get();
        $allowance_amount = AllowanceProgramAmount::where('allowance_program_id', $program_id)->with('type')->get();

        return [
            'allowance_program' => $allowance,
            'age_limit_wise_allowance' => $allowance_age,
            'type_wise_allowance' => $allowance_amount
        ];
    }

    public function getActiveInstallments($request, $allotment_id)
    {
        return EmergencyPayrollSetting::query()
            ->join('payroll_installment_schedules', 'payroll_installment_schedules.id', '=', 'emergency_payroll_settings.installment_schedule_id')
            ->where('allotment_id', $allotment_id)
            ->get(['emergency_payroll_settings.*', 'payroll_installment_schedules.*']);
    }
    public function beneficiaryDelete($payroll_details_id)
    {
        try {
            $p_detail = EmergencyPayrollDetails::where('id', $payroll_details_id)->first();
            if ($p_detail != null) {
                $payroll = EmergencyPayroll::where('id', $p_detail->emergency_payroll_id)->first();
                $payroll->total_beneficiaries -= 1;
                $payroll->total_charge -= $p_detail->charge;
                $payroll->sub_total_amount -= $p_detail->amount;
                $payroll->total_amount -= $p_detail->total_amount;
                $payroll->save();
                $p_detail->amount = 0.00;
                $p_detail->charge = 0.00;
                $p_detail->total_amount = 0.00;
                $p_detail->status = "Rejected";
                $p_detail->status_id = 3;
                $p_detail->is_set = 0;
                $p_detail->save();
                EmergencyBeneficiaryPayrollPaymentStatusLog::where('emergency_payroll_details_id', $payroll_details_id)->update(['status_id' => 3]);
                //                $p_detail->forceDelete();
            }
            return $p_detail;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

    public function getAllotmentAreaList(Request $request)
    {

        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $allotment_id = $request->query('allotment_id');
        $perPage = $request->query('perPage', 100);
        $installment_id = $request->query('installment_schedule_id');
        $query = EmergencyAllotment::query()
            ->leftJoin('emergency_payrolls', 'emergency_allotments.id', '=', 'emergency_payrolls.emergency_allotment_id');

        $query = $query->where(function ($query) {
            return $query->where('emergency_payrolls.is_checked', 0)
                ->orWhere('emergency_payrolls.is_checked', null)
                ->orWhere('emergency_payrolls.is_rejected', 1);
        });

        if ($allotment_id) {
            $query = $query->where('emergency_allotments.id', $allotment_id);
        }

        if ($program_id) {
            $query->whereHas('programs', function ($q) use ($program_id) {
                $q->where('allowance_program_emergency_allotment.allowance_program_id', $program_id);
            });
        }

        if ($financial_year_id) {
            $query = $query->where('emergency_allotments.financial_year_id', $financial_year_id);
        }

        $query = $this->applyLocationFilter($query, $request);

        $query = $query->selectRaw('emergency_allotments.*,
                        emergency_payrolls.emergency_allotment_id,
                        emergency_payrolls.total_beneficiaries as saved_beneficiaries')
            ->with('upazila', 'cityCorporation', 'districtPourosova', 'location')->groupBy('emergency_allotments.id');

        return $query->orderBy('location_id')->paginate($perPage)->through(function ($allotmentArea, $program_id) use ($installment_id) {
            $allotmentArea->active_beneficiaries = $this->countActiveBeneficiaries($allotmentArea, $program_id);
            $allotmentArea->installment_id = $installment_id; // Add the installment ID to the resource
            return $allotmentArea;
        });
    }

    private function applyLocationFilter($query, $request)
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        $locationType = $user->assign_location?->type;
        $division_id = $request->query('division_id');
        $district_id = $request->query('district_id');
        $city_corp_id = $request->query('city_corp_id');
        $district_pourashava_id = $request->query('district_pourashava_id');
        $upazila_id = $request->query('upazila_id');
        $pourashava_id = $request->query('pourashava_id');
        $thana_id = $request->query('thana_id');
        $union_id = $request->query('union_id');
        $ward_id = $request->query('ward_id');

        if ($user->assign_location) {
            if ($locationType == 'ward') {
                $ward_id = $assignedLocationId;
                $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = $thana_id = $pourashava_id = $union_id = -1;
            } elseif ($locationType == 'union') {
                $union_id = $assignedLocationId;
                $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = $thana_id = $pourashava_id = -1;
            } elseif ($locationType == 'pouro') {
                $pourashava_id = $assignedLocationId;
                $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = $thana_id = $union_id = -1;
            } elseif ($locationType == 'thana') {
                if ($subLocationType == 2) {
                    $upazila_id = $assignedLocationId;
                    $division_id = $district_id = $city_corp_id = $district_pourashava_id = $thana_id = -1;
                } elseif ($subLocationType == 3) {
                    $thana_id = $assignedLocationId;
                    $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = -1;
                } else {
                    $query = $query->where('id', -1); // wrong location type
                }
            } elseif ($locationType == 'city') {
                if ($subLocationType == 1) {
                    $district_pourashava_id = $assignedLocationId;
                    $division_id = $district_id = $city_corp_id = $upazila_id = $thana_id = -1;
                } elseif ($subLocationType == 3) {
                    $city_corp_id = $assignedLocationId;
                    $division_id = $district_id = $district_pourashava_id = $upazila_id = $thana_id = -1;
                } else {
                    $query = $query->where('id', -1); // wrong location type
                }
            } elseif ($locationType == 'district') {
                $district_id = $assignedLocationId;
                $division_id = -1;
            } elseif ($locationType == 'division') {
                $division_id = $assignedLocationId;
            } else {
                $query = $query->where('id', -1); // wrong location assigned
            }
        }

        if ($division_id && $division_id > 0)
            $query = $query->where('emergency_allotments.division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('emergency_allotments.district_id', $district_id);
        if ($city_corp_id && $city_corp_id > 0)
            $query = $query->where('emergency_allotments.city_corp_id', $city_corp_id);
        if ($district_pourashava_id && $district_pourashava_id > 0)
            $query = $query->where('emergency_allotments.district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && $upazila_id > 0)
            $query = $query->where('emergency_allotments.upazila_id', $upazila_id);
        if ($pourashava_id && $pourashava_id > 0)
            $query = $query->where('emergency_allotments.pourashava_id', $pourashava_id);
        if ($thana_id && $thana_id > 0)
            $query = $query->where('emergency_allotments.thana_id', $thana_id);
        if ($union_id && $union_id > 0)
            $query = $query->where('emergency_allotments.union_id', $union_id);
        if ($ward_id && $ward_id > 0)
            $query = $query->where('emergency_allotments.ward_id', $ward_id);

        return $query;
    }

    private function countActiveBeneficiaries($allotmentArea, $program_id): int
    {

        $query = EmergencyBeneficiary::query();

        $query = $query->where('allotment_id', $allotmentArea->id);


        if ($program_id) {
            $query->where('program_id', $program_id);
        }

        if ($allotmentArea->city_corp_id) {
            $query = $query->where('permanent_city_corp_id', $allotmentArea->city_corp_id);
        }
        if ($allotmentArea->district_pourashava_id) {
            $query = $query->where('permanent_district_pourashava_id', $allotmentArea->district_pourashava_id);
        }
        if ($allotmentArea->upazila_id) {
            $query = $query->where('permanent_upazila_id', $allotmentArea->upazila_id);
        }

        if ($allotmentArea->pourashava_id) {
            $query = $query->where('permanent_pourashava_id', $allotmentArea->pourashava_id);
        }

        if ($allotmentArea->thana_id) {
            $query = $query->where('permanent_thana_id', $allotmentArea->thana_id);
        }
        //        dd($query->get());
        //        if ($allotmentArea->union_id) {
        //            $query = $query->where('permanent_union_id', $allotmentArea->union_id);
        //        }
        //
        //        if ($allotmentArea->ward_id) {
        //            $query = $query->where('permanent_ward_id', $allotmentArea->ward_id);
        //        }

        return $query->count();
    }

    public function getActiveBeneficiaries(Request $request, int $allotment_id)
    {
        $program_id = $request->program_id;
        $financial_year_id = $request->financial_year_id;
        $installment_schedule_id = $request->installment_schedule_id;
        $allotmentArea = EmergencyAllotment::findOrfail($allotment_id);
        $currentInstallment = $this->getPaymentCycleDates($installment_schedule_id);

        $prevInstallment = DB::table('payroll_installment_schedules')
            ->where('installment_number', '<', $currentInstallment->installment_number)
            ->where('payment_cycle', '=', $currentInstallment->payment_cycle)
            ->first(['id', 'installment_name', 'installment_number', 'payment_cycle']);

        if ($prevInstallment) {
            $previousInstallment = $this->getPaymentCycleDates($prevInstallment->id);
        } else {
            // Set default values if no previous installment is found
            $previousInstallment = (object)[
                'start_date' => Carbon::now()->startOfYear()->addMonths(6)->toDateString(),
                'end_date' => Carbon::now()->endOfYear()->addMonths(6)->subDay()->toDateString(),
                'installment_number' => 0,
                'payment_cycle' => $currentInstallment->payment_cycle
            ];
        }
        $query = EmergencyBeneficiary::query()
            ->leftJoin('emergency_payroll_details', function ($join) {
                $join->on('emergency_beneficiaries.beneficiary_id', '=', 'emergency_payroll_details.emergency_beneficiary_id')
                    ->where('emergency_payroll_details.is_set', '=', 0);
            })
            ->leftJoin('emergency_allotments', 'emergency_beneficiaries.allotment_id', '=', 'emergency_allotments.id')
            // ->where('emergency_beneficiaries.payment_start_date', '<', $currentInstallment->end_date)
            ->where('emergency_beneficiaries.status', 1)
            ->where('emergency_beneficiaries.isSelected', 1)
            ->where('emergency_beneficiaries.allotment_id', $allotment_id)
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('emergency_payroll_details')
                    ->whereColumn('emergency_payroll_details.emergency_beneficiary_id', 'emergency_beneficiaries.beneficiary_id')
                    ->where('emergency_payroll_details.is_set', '=', 1);
            })->addSelect([
                'emergency_beneficiaries.*',
                DB::raw('
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM emergency_payroll_details pd
                        WHERE pd.emergency_beneficiary_id  = emergency_beneficiaries.beneficiary_id
                        AND pd.status_id = 3
                    ) THEN 1
                    ELSE 0
                END as isRejected
            ')
            ]);

        $query->where('allotment_id', $allotmentArea->id);

        $query = $this->getBeneficiaryTrackingStatus($query, $currentInstallment, $previousInstallment);
        $beneficiaries = $this->calculateAmountChargeAndTotalAmount($query, $currentInstallment);
        return $beneficiaries;
    }
    public function getBeneficiaryTrackingStatus($query, $currentInstallment, $previousInstallment)
    {
        $currentYearStartDate = Carbon::now()->startOfYear()->addMonths(6)->toDateString(); // Starting from July 1st
        $currentYearEndDate = Carbon::now()->endOfYear()->addMonths(6)->subDay()->toDateString(); // Ending on June 30th of next year
        $currentEndDate = $currentInstallment->end_date;
        $previousStartDate = $previousInstallment->start_date;

        return $query->leftJoin('beneficiary_change_trackings', function ($join) use ($currentEndDate, $previousStartDate) {
            $join->on('emergency_beneficiaries.beneficiary_id', '=', 'beneficiary_change_trackings.beneficiary_id')
                ->where('beneficiary_change_trackings.change_type_id', 2)
                ->whereBetween('emergency_beneficiaries.payment_start_date', [$previousStartDate, $currentEndDate]);
        })
            ->selectRaw(
                'emergency_beneficiaries.*,
                 IF(beneficiary_change_trackings.id IS NOT NULL, true, false) as change_status,
                 case when isExisting = 0 then 1 ELSE 0 end as isNew,
                 IF(emergency_beneficiaries.payment_start_date < ?, 1, 0) as isRegular',
                [ $currentYearStartDate]
            );
    }

    private function calculateAmountChargeAndTotalAmount($query, $currentInstallment)
    {

        $query->selectRaw(
            'emergency_beneficiaries.*,
        ROUND(
            CASE
                WHEN emergency_beneficiaries.payment_start_date = ? THEN
                    CASE emergency_allotments.payment_cycle
                        WHEN "Monthly" THEN emergency_allotments.amount_per_person
                        WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                        WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                        WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                        ELSE 0
                    END
                WHEN emergency_beneficiaries.payment_start_date > ? THEN
                    FLOOR(DATEDIFF(?, emergency_beneficiaries.payment_start_date) / 30) * emergency_allotments.amount_per_person
                WHEN emergency_beneficiaries.payment_start_date = ? THEN
                    emergency_allotments.amount_per_person
                WHEN emergency_beneficiaries.payment_start_date < ? THEN
                    CASE emergency_allotments.payment_cycle
                        WHEN "Monthly" THEN emergency_allotments.amount_per_person
                        WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                        WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                        WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                        ELSE 0
                    END
                END, 2) as amount_per_person,

               ROUND(
            (
                CASE
                WHEN emergency_beneficiaries.payment_start_date = ? THEN
                    CASE emergency_allotments.payment_cycle
                        WHEN "Monthly" THEN emergency_allotments.amount_per_person
                        WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                        WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                        WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                        ELSE 0
                    END
                WHEN emergency_beneficiaries.payment_start_date > ? THEN
                    FLOOR(DATEDIFF(?, emergency_beneficiaries.payment_start_date) / 30) * emergency_allotments.amount_per_person
                WHEN emergency_beneficiaries.payment_start_date = ? THEN
                    emergency_allotments.amount_per_person
                WHEN emergency_beneficiaries.payment_start_date < ? THEN
                    CASE emergency_allotments.payment_cycle
                        WHEN "Monthly" THEN emergency_allotments.amount_per_person
                        WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                        WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                        WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                        ELSE 0
                    END
                END * (IFNULL(banks.charge, mfs.charge) / 100)
            ), 2) as charge,

            ROUND(
               (
                CASE
                    WHEN emergency_beneficiaries.payment_start_date = ? THEN
                        CASE emergency_allotments.payment_cycle
                            WHEN "Monthly" THEN emergency_allotments.amount_per_person
                            WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                            WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                            WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                            ELSE 0
                        END
                    WHEN emergency_beneficiaries.payment_start_date > ? THEN
                        FLOOR(DATEDIFF(?, emergency_beneficiaries.payment_start_date) / 30) * emergency_allotments.amount_per_person
                    WHEN emergency_beneficiaries.payment_start_date = ? THEN
                        emergency_allotments.amount_per_person
                    WHEN emergency_beneficiaries.payment_start_date < ? THEN
                        CASE emergency_allotments.payment_cycle
                            WHEN "Monthly" THEN emergency_allotments.amount_per_person
                            WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                            WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                            WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                            ELSE 0
                        END
                    END
                  ), 2
                )
                +
                ROUND(
                  (
                    CASE
                    WHEN emergency_beneficiaries.payment_start_date = ? THEN
                        CASE emergency_allotments.payment_cycle
                            WHEN "Monthly" THEN emergency_allotments.amount_per_person
                            WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                            WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                            WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                            ELSE 0
                        END
                    WHEN emergency_beneficiaries.payment_start_date > ? THEN
                        FLOOR(DATEDIFF(?, emergency_beneficiaries.payment_start_date) / 30) * emergency_allotments.amount_per_person
                    WHEN emergency_beneficiaries.payment_start_date = ? THEN
                        emergency_allotments.amount_per_person
                    WHEN emergency_beneficiaries.payment_start_date < ? THEN
                        CASE emergency_allotments.payment_cycle
                            WHEN "Monthly" THEN emergency_allotments.amount_per_person
                            WHEN "Quarterly" THEN emergency_allotments.amount_per_person * 3
                            WHEN "Half Yearly" THEN emergency_allotments.amount_per_person * 6
                            WHEN "Yearly" THEN emergency_allotments.amount_per_person * 12
                            ELSE 0
                        END
                    END * (IFNULL(banks.charge, mfs.charge) / 100)
                  ), 2
                )  as total_allowance_amount',
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
            ->leftJoin(
                'banks',
                'banks.id',
                '=',
                'emergency_beneficiaries.bank_id'
            )
            ->leftJoin('mfs', 'mfs.id', '=', 'emergency_beneficiaries.mfs_id')
            ->addSelect([
                DB::raw('IFNULL(banks.charge, mfs.charge) as bank_or_mfs_charge')
            ])
            ->orderBy('emergency_beneficiaries.id')
            ->groupBy('emergency_beneficiaries.id');

        return $query->get();
    }

    public function getAllotmentAreaStatistics($request, int $allotment_id)
    {

        $installment_id = $request->installment_schedule_id;
        $program_id = $request->program_id;
        $fiscal_year_id = $request->financial_year_id;
        $allotment = EmergencyAllotment::query()->with('location')->findOrFail($allotment_id);

        $query = EmergencyPayroll::query()
            ->join('emergency_payroll_details', 'emergency_payrolls.id', '=', 'emergency_payroll_details.emergency_payroll_id')
            ->where('emergency_payrolls.emergency_allotment_id', $allotment_id)
            ->where('emergency_payrolls.installment_schedule_id', $installment_id)
            ->get();

        $totalBeneficiaries = $this->countActiveBeneficiaries($allotment, $program_id);
        $program = AllowanceProgram::where('id', $program_id)->get(['id', 'name_en', 'name_bn', 'payment_cycle'])->first();
        $total_amount = (int)($allotment->amount_per_person * ($allotment->no_of_existing_benificiariy + $allotment->no_of_new_benificiariy));


        $annualPayrollEligibleAmount = $total_amount * 12;
        $sum_of_all_previous_installments = $this->previousAllPayment($installment_id, $allotment_id, $program_id, $fiscal_year_id);
        $installment = $this->getCurrentInstallment($installment_id, $allotment->payment_cycle);
        $installmentSchedule = $this->getPaymentCycleDates($installment_id);
        $payroll_eligible_amount = $this->calculateAmount(
            $annualPayrollEligibleAmount,
            $allotment->payment_cycle
        );
        $set_beneficiaries = $query->where('is_set', 1)->count();
        $allocated_beneficiaries = (int)($allotment->no_of_existing_benificiariy + $allotment->no_of_new_benificiariy);
        return [
            'allotment_area' => $allotment->location,
            'program' => $program,
            'installment' => $installment,
            'payment_cycle_start_date' => $installmentSchedule->start_date,
            'payment_cycle_end_date' => $installmentSchedule->end_date,
            'allocated_beneficiaries' => $allocated_beneficiaries,
            'total_beneficiary' => $totalBeneficiaries,
            'sum_of_all_previous_installments' => round($sum_of_all_previous_installments, 3),
            'set_beneficiaries' => $set_beneficiaries ?? 0,
            'payroll_eligible_amount' => round(($payroll_eligible_amount - $sum_of_all_previous_installments), 2) ?? 0,
            'annual_payroll_eligible_amount' => round($annualPayrollEligibleAmount, 2) ?? 0,
            'remaining_amount' => round($payroll_eligible_amount, 2) ?? 0,
        ];
    }

    private function previousAllPayment($installmentId, $allotment_id, $program_id, $financialYearId)
    {
        $payrollIds = EmergencyPayroll::where('program_id', $program_id)
            ->where('financial_year_id', $financialYearId)
            ->where('emergency_allotment_id', $allotment_id)
            ->where('installment_schedule_id', $installmentId)
            ->get()
            ->pluck('id')
            ->toArray();
        return EmergencyPayrollDetails::whereIn('emergency_payroll_id', $payrollIds)->where('is_set', 1)->sum('total_amount');
    }

    private function getCurrentInstallment($installmentId, $paymentCycle)
    {
        $currentDate = Carbon::now();
        $currentYear = $currentDate->year;

        //get current financial yer
        $financialCurrentYear = FinancialYear::where('status', 1)->where('version', 1)->first();

        // Fetch the installment setting
        $installmentSetting = EmergencyPayrollSetting::with(['financialYear', 'installment'])->where('installment_schedule_id', $installmentId)
            ->whereHas('financialYear', function ($q) use ($financialCurrentYear, $currentYear) {
                $q->where('id', $financialCurrentYear->id);
                $q->whereYear('start_date', $currentYear);
            })
            ->whereHas('installment', function ($q) use ($paymentCycle) {
                $q->where('payment_cycle', $paymentCycle);
            })
            ->first();


        if (!$installmentSetting) {
            return null;
        }

        $installmentSchedule = PayrollInstallmentSchedule::where('id', $installmentSetting->installment_schedule_id)->get(['installment_name', 'installment_name_bn'])
            ->first();

        if (!$installmentSchedule) {
            return null;
        }



        return $installmentSchedule;
    }

    private function getPaymentCycleDates($installmentScheduleId)
    {
        return \Illuminate\Support\Facades\DB::table('payroll_installment_schedules')
            ->select(
                'payment_cycle',
                'installment_number',
                'installment_name',
                DB::raw('CASE
            WHEN payment_cycle = "Monthly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "07-01")
                    WHEN installment_number = 2 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "08-01")
                    WHEN installment_number = 3 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "09-01")
                    WHEN installment_number = 4 THEN CONCAT(YEAR(CURRENT_DATE()), "-",  "10-01")
                    WHEN installment_number = 5 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "11-01")
                    WHEN installment_number = 6 THEN CONCAT(YEAR(CURRENT_DATE()), "-",  "12-01")
                    WHEN installment_number = 7 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-", "01-01")
                    WHEN installment_number = 8 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-",  "02-01")
                    WHEN installment_number = 9 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-",  "03-01")
                    WHEN installment_number = 10 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-", "04-01")
                    WHEN installment_number = 11 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-",  "05-01")
                    WHEN installment_number = 12 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-", "06-01")
                    ELSE CONCAT(YEAR(CURRENT_DATE()), "-", LPAD(MONTH(CURRENT_DATE()) + installment_number - 1, 2, "0"), "-01")
                END
            WHEN payment_cycle = "Quarterly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-07-01")
                    WHEN installment_number = 2 THEN CONCAT(YEAR(CURRENT_DATE()), "-10-01")
                    WHEN installment_number = 3 THEN CONCAT(YEAR(CURRENT_DATE()) + 1, "-01-01")
                    WHEN installment_number = 4 THEN CONCAT(YEAR(CURRENT_DATE()) + 1, "-04-01")
                    ELSE NULL
                END
            WHEN payment_cycle = "Half Yearly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-07-01")
                    WHEN installment_number = 2 THEN CONCAT(YEAR(CURRENT_DATE()) + 1, "-01-01")
                    ELSE NULL
                END
            WHEN payment_cycle = "Yearly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-07-01")
                    ELSE NULL
                END
            ELSE NULL
        END AS start_date'),
                DB::raw('CASE
            WHEN payment_cycle = "Monthly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "07-31")
                    WHEN installment_number = 2 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "08-31")
                    WHEN installment_number = 3 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "09-30")
                    WHEN installment_number = 4 THEN CONCAT(YEAR(CURRENT_DATE()), "-",  "10-31")
                    WHEN installment_number = 5 THEN CONCAT(YEAR(CURRENT_DATE()), "-", "11-30")
                    WHEN installment_number = 6 THEN CONCAT(YEAR(CURRENT_DATE()), "-",  "12-31")
                    WHEN installment_number = 7 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-", "01-31")
                    WHEN installment_number = 8 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-",  "02-28")
                    WHEN installment_number = 9 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-",  "03-31")
                    WHEN installment_number = 10 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-", "04-30")
                    WHEN installment_number = 11 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-",  "05-31")
                    WHEN installment_number = 12 THEN CONCAT(YEAR(CURRENT_DATE())+ 1, "-", "06-30")
                    ELSE LAST_DAY(DATE_ADD(CURRENT_DATE(), INTERVAL (installment_number - 1) MONTH))
                END
            WHEN payment_cycle = "Quarterly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-09-30")
                    WHEN installment_number = 2 THEN CONCAT(YEAR(CURRENT_DATE()), "-12-31")
                    WHEN installment_number = 3 THEN CONCAT(YEAR(CURRENT_DATE()) + 1, "-03-31")
                    WHEN installment_number = 4 THEN CONCAT(YEAR(CURRENT_DATE()) + 1, "-06-30")
                    ELSE NULL
                END
            WHEN payment_cycle = "Half Yearly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-12-31")
                    WHEN installment_number = 2 THEN CONCAT(YEAR(CURRENT_DATE()) + 1, "-06-30")
                    ELSE NULL
                END
            WHEN payment_cycle = "Yearly" THEN
                CASE
                    WHEN installment_number = 1 THEN CONCAT(YEAR(CURRENT_DATE()), "-06-30")
                    ELSE NULL
                END
            ELSE NULL
        END AS end_date')
            )
            ->where('id', $installmentScheduleId)
            ->first();
    }

    private function calculateAmount($total, $payment_cycle)
    {
        $payroll_eligible_amount = 0;

        if ($payment_cycle == "Monthly") {
            $payroll_eligible_amount = $total / 12;
        } else if ($payment_cycle == "Quarterly") {
            $payroll_eligible_amount = $total / 4;
        } else if ($payment_cycle == "Half Yearly") {
            $payroll_eligible_amount = $total / 2;
        } else {
            $payroll_eligible_amount = $total;
        }

        return $payroll_eligible_amount;
    }

    public function getSelectedBeneficiaries(Request $request, $allotment_id): \Illuminate\Database\Eloquent\Collection|array
    {
        $program_id = $request->queryParams['program_id'];
        $financial_year_id = $request->queryParams['financial_year_id'];
        $installment_schedule_id = $request->queryParams['installment_schedule_id'];
        $payroll_ids = EmergencyPayroll::where('emergency_allotment_id', $allotment_id)->pluck('id')->toArray();
        return EmergencyBeneficiary::query()
            ->join('emergency_payroll_details', 'emergency_beneficiaries.beneficiary_id', '=', 'emergency_payroll_details.emergency_beneficiary_id')
            ->join('emergency_payrolls', 'emergency_payrolls.id', '=', 'emergency_payroll_details.emergency_payroll_id')
            ->whereIn('emergency_payroll_details.emergency_payroll_id', $payroll_ids)
            ->where('emergency_payrolls.program_id', $program_id)
            ->where('emergency_payrolls.financial_year_id', $financial_year_id)
            ->where('emergency_payrolls.installment_schedule_id', $installment_schedule_id)
            ->where(function ($query) {
                $query->where('emergency_payrolls.is_approved', 0)
                    ->where('emergency_payroll_details.is_set', 0)
                    // ->orWhere('payrolls.is_rejected', 1)
                    ->where('emergency_payroll_details.status_id', 1);
            })
            ->select(
                'emergency_beneficiaries.*',
                'emergency_payroll_details.total_amount as total_allowance_amount',
                'emergency_payroll_details.charge as charge',
                'emergency_payroll_details.amount as amount',
            )
            ->with([
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard'
            ])
            ->get();
    }

    public function processPayroll(EmergencyPayrollRequest $request)
    {

        $total_beneficiaries = 0;
        $sub_total_amount = 0;
        $total_charge = 0;
        $total_amount = 0;

        try {
            $payroll_eligible_amount = (int)$request->input('payroll_eligible_amount');
            $allotment_id = $request->post('allotment_id');
            $program_id = $request->post('program_id') ?? null;
            $office_id = auth()->user()->office_id ?? null;
            $allotment = EmergencyAllotment::findOrFail($allotment_id);
            $max_beneficiary_limit = $allotment->total_beneficiaries;
            $validatedPayrollDetailsData = $request->validated('payroll_details');
            $total_payroll_amount = collect($validatedPayrollDetailsData)->sum('total');


            if (count($validatedPayrollDetailsData) > $max_beneficiary_limit) {
                return [
                    'message' => 'Maximum beneficiary limit reached',
                    'status' => '210',
                ];
            }

            if ($total_payroll_amount > $payroll_eligible_amount) {
                return [
                    'message' => 'Budget amount has been exceeded',
                    'status' => '211',
                ];
            }

            $payroll = EmergencyPayroll::query()
                ->where('financial_year_id', $allotment->financial_year_id)
                ->where('program_id', $program_id)
                ->where('emergency_allotment_id', $allotment_id)
                ->where('installment_schedule_id', $request->post('installment_schedule_id'))
                ->where(function ($query) {
                    $query->where('is_approved', 0)
                        ->where('is_rejected', 0);
                })
                ->first();


            DB::beginTransaction();

            if ($payroll) {
                // Delete all match records from log first
                $details_id = EmergencyPayrollDetails::where('emergency_payroll_id', $payroll->id)->pluck('id')->toArray();
                if (!empty($details_id)) {
                    EmergencyBeneficiaryPayrollPaymentStatusLog::whereIn('emergency_payroll_details_id', $details_id)->where('status_id', 1)->forceDelete();
                }
                // Delete all match records from details first
                $payroll->emergencyPayrollDetails()->where('is_set', 0)->forceDelete();
                $remainingBeneficiary = $max_beneficiary_limit - $payroll->emergencyPayrollDetails()->count();

                if (count($validatedPayrollDetailsData) > $remainingBeneficiary) {
                    return [
                        'message' => 'Beneficiary not found',
                        'status' => '212',
                    ];
                }
                $total_beneficiaries = $payroll->emergencyPayrollDetails()->count();
                $sub_total_amount = $payroll->emergencyPayrollDetails()->sum('amount');
                $total_charge = $payroll->emergencyPayrollDetails()->sum('charge');
                $total_amount = $sub_total_amount + $total_charge;
                // Update the payroll with the new totals
                $payroll->update([
                    'total_beneficiaries' => $total_beneficiaries,
                    'sub_total_amount' => $sub_total_amount,
                    'total_charge' => $total_charge,
                    'total_amount' => $total_amount,
                ]);
            } else {
                $payroll = EmergencyPayroll::create([
                    'emergency_allotment_id' => $request->allotment_id,
                    'financial_year_id' => $request->financial_year_id,
                    'office_id' => $office_id ?: auth()->user()->id,
                    'program_id' => $program_id,
                    'installment_schedule_id' => $request->installment_schedule_id,
                    'total_beneficiaries' => 0,
                    'sub_total_amount' => 0,
                    'total_charge' => 0,
                    'total_amount' => 0,
                ]);
            }

            foreach ($validatedPayrollDetailsData as $payrollDetailsData) {
                $dtlAmount = (int)$payrollDetailsData['amount'] ?? 0;
                $dtlCharge = (int)$payrollDetailsData['charge'] ?? 0;
                $dtlTotalAmount = (int)$payrollDetailsData['total'];

                $payrollDetailsData['emergency_payroll_id'] = $payroll->id;
                $payrollDetailsData['charge'] = $dtlCharge;
                $payrollDetailsData['total_amount'] = $dtlTotalAmount;
                $payrollDetailsData['status_id'] = 1;
                $payrollDetailsData['status'] = 'Pending';
                $payrollDetailsData['updated_by_id'] = auth()->user()->id;

                $total_beneficiaries++;
                $sub_total_amount += $dtlAmount;
                $total_charge += $dtlCharge;
                $total_amount += $dtlTotalAmount;

                $payrollDetail = EmergencyPayrollDetails::create($payrollDetailsData);

                EmergencyBeneficiaryPayrollPaymentStatusLog::create([
                    'emergency_beneficiary_id' => $payrollDetailsData['emergency_beneficiary_id'],
                    'emergency_payroll_details_id' => $payrollDetail->id,
                    'created_by' => auth()->user()->id,
                    'created_at' => now(),
                    'status_id' => 1,
                ]);
            }

            // Final update after processing all payroll details
            $payroll->update([
                'total_beneficiaries' => $total_beneficiaries,
                'sub_total_amount' => $sub_total_amount,
                'total_charge' => $total_charge,
                'total_amount' => $total_amount,
            ]);

            DB::commit();
            return $payroll;
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
        }
    }
    public function previewBeneficiaries(Request $request)
    {
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $installment_schedule_id = $request->query('installment_schedule_id');

        $query = EmergencyBeneficiary::query()
            ->join('emergency_payroll_details', 'emergency_beneficiaries.beneficiary_id', '=', 'emergency_payroll_details.emergency_beneficiary_id')
            ->join('emergency_payrolls', 'emergency_payrolls.id', '=', 'emergency_payroll_details.emergency_payroll_id')
            ->join('emergency_allotments', 'emergency_allotments.id', '=', 'emergency_payrolls.emergency_allotment_id');

        $query = $query->where(function ($query) {
            return $query->where('emergency_payrolls.is_checked', 0)
                ->where('emergency_payroll_details.is_set', '!=', 1)
                ->where('emergency_payroll_details.status_id', '!=', 3);
        });

        $query = $query->where('emergency_payrolls.program_id', $program_id);
        $query = $query->where('emergency_payrolls.financial_year_id', $financial_year_id);
        $query = $query->where('emergency_payrolls.installment_schedule_id', $installment_schedule_id);

        $query = $this->applyLocationFilter($query, $request);

        $query = $query
            ->selectRaw('emergency_beneficiaries.*, emergency_payrolls.emergency_allotment_id  as emergency_allotment_id ,emergency_payroll_details.emergency_payroll_id, emergency_payroll_details.id as emergency_payroll_detail_id, emergency_payroll_details.amount, emergency_payroll_details.charge, emergency_payroll_details.total_amount')
            ->with('permanentCityCorporation', 'permanentDistrictPourashava', 'permanentUpazila', 'permanentPourashava', 'permanentUnion', 'permanentWard');
        return $query->orderBy('emergency_beneficiaries.permanent_city_corp_id')
            ->orderBy('emergency_beneficiaries.permanent_district_pourashava_id')
            ->orderBy('emergency_beneficiaries.permanent_upazila_id')
            ->orderBy('emergency_beneficiaries.permanent_pourashava_id')
            ->orderBy('emergency_beneficiaries.permanent_thana_id')
            ->orderBy('emergency_beneficiaries.permanent_union_id')
            ->orderBy('emergency_beneficiaries.permanent_ward_id')
            ->get();
    }
    public function submitPayroll($request)
    {

        DB::beginTransaction();
        try {
            $requestData = $request->validated();
            $payroll_ids = array_unique(Arr::pluck($requestData['payroll_details'], 'payroll_id'));
            $payroll_details_ids = array_unique(Arr::pluck($requestData['payroll_details'], 'id'));
            $payrolls = EmergencyPayroll::whereIn('id', $payroll_ids)
                ->with('emergencyAllotment', 'emergencyPayrollDetails')
                ->where(function ($query) {
                    $query->where('is_approved', 0)
                        ->where('is_rejected', 0);
                })
                ->get();
            if (!empty($payrolls)) {
                EmergencyPayrollDetails::whereIn('id', $payroll_details_ids)->update(['is_set' => 1]);
                foreach ($payrolls as $payroll) {
                    $allotment = $payroll->emergencyAllotment;
                    $detailsCount = collect($requestData['payroll_details'])->where('payroll_id', $payroll->id)->count();
                    $is_checked = $detailsCount === $allotment->total_beneficiaries ? 1 : 0;
                    $user = auth()->user();
                    $payroll->is_submitted = 1;
                    $payroll->submitted_by_id = $user?->id;
                    $payroll->is_checked = $is_checked;
                    $payroll->submitted_at = now();
                    $payroll->save();
                    DB::commit();
                }
                return $payrolls;
            } else {
                return [
                    'message' => 'Emergency Payroll does not exist',
                    'status' => '211',
                ];
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
