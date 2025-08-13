<?php

namespace App\Http\Services\Admin\Emergency;

use App\Http\Traits\MessageTrait;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyBeneficiaryPayrollPaymentStatusLog;
use App\Models\EmergencyPayroll;
use App\Models\EmergencyPayrollDetails;
use App\Models\EmergencyPayrollPaymentCycle;
use App\Models\EmergencyPayrollPaymentCycleDetails;
use App\Models\EmergencyPayrollSetting;
use App\Models\PayrollInstallmentSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;


class EmergencyPayrollApprovalService
{
    use MessageTrait;
    public function getPayrollRejectedBeneficiaries($request, $payroll_id): \Illuminate\Database\Eloquent\Collection|array
    {
        $query = EmergencyBeneficiary::query()
            ->leftJoin('emergency_payroll_details', 'emergency_beneficiaries.beneficiary_id', '=', 'emergency_payroll_details.emergency_beneficiary_id')
            ->where('emergency_payroll_details.emergency_payroll_id', $payroll_id)
            ->where(function ($query) {
                $query->where('emergency_payroll_details.is_set', '=', 1)
                    ->where('emergency_payroll_details.status_id', '=', 9)
                    ->orWhere('emergency_payroll_details.status_id', '=', 10);
            });

        return $query
            ->select(
                'emergency_beneficiaries.*',
                'emergency_payroll_details.total_amount as total_allowance_amount',
                'emergency_payroll_details.amount',
                'emergency_payroll_details.charge',
                'emergency_payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program'
            )
            ->get();
    }
    public function getActivePayrollBeneficiaries($request, $payroll_id): \Illuminate\Database\Eloquent\Collection|array
    {

        $query = EmergencyBeneficiary::query()
            ->leftJoin('emergency_payroll_details', 'emergency_beneficiaries.beneficiary_id', '=', 'emergency_payroll_details.emergency_beneficiary_id')
            ->where('emergency_payroll_details.emergency_payroll_id', $payroll_id)
            ->where(function ($query) {
                $query->where('emergency_payroll_details.is_set', '=', 1)
                    ->where('emergency_payroll_details.status_id', '=', 1);
            });

        return $query
            ->select(
                'emergency_beneficiaries.*',
                'emergency_payroll_details.id as emergency_payroll_detail_id',
                'emergency_payroll_details.emergency_payroll_id as emergency_payroll_id',
                'emergency_payroll_details.total_amount as total_allowance_amount',
                'emergency_payroll_details.amount',
                'emergency_payroll_details.charge',
                'emergency_payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program'
            )
            ->get();
    }

    public function getPayrollApproveBeneficiaries($request, $payroll_id): \Illuminate\Database\Eloquent\Collection|array
    {

        $query = EmergencyBeneficiary::query()
            ->leftJoin('emergency_payroll_details', 'emergency_beneficiaries.beneficiary_id', '=', 'emergency_payroll_details.emergency_beneficiary_id')
            ->where('emergency_payroll_details.emergency_payroll_id', $payroll_id)
            ->where(function ($query) {
                $query->where('emergency_payroll_details.is_set', '=', 1)
                    ->where('emergency_payroll_details.status_id', '=', 2);
            });

        return $query
            ->select(
                'emergency_beneficiaries.*',
                'emergency_payroll_details.total_amount as total_allowance_amount',
                'emergency_payroll_details.amount',
                'emergency_payroll_details.charge',
                'emergency_payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program'
            )
            ->get();
    }

    public function getPayrollList(Request $request)
    {
        // dd($request->all());
        $program_id = $request->program_id;
        $financial_year_id = $request->financial_year_id;
        $installment_schedule_id = $request->installment_id;
        $office_id = $request->office;
        $status_id = $request->status_id;
        $division_id = $request->division_id;
        $district_id = $request->district_id;
        $location_type_id = $request->location_type_id;
        $upazila_id = $request->upazila_id;
        $perPage = $request->perPage ?? 10;

        $query = EmergencyPayroll::query()
            ->where('is_submitted', 1)
            ->with(['office', 'program', 'installmentSchedule', 'payrollDetails' => function ($query) {
                $query->select('emergency_payroll_id', 'total_amount', 'amount', 'charge', 'is_set')->where('is_set', 1);
            }]);



        if ($program_id) {
            $query->where('program_id', $program_id);
        }
        if ($office_id) {
            $query->where('office_id', $office_id);
        }

        if ($financial_year_id) {
            $query->where('financial_year_id', $financial_year_id);
        }

        if ($installment_schedule_id) {
            $query->where('installment_schedule_id', $installment_schedule_id);
        }

        $query = $this->applyStatusFilter($query, $status_id);


        return $query->paginate($perPage)->through(function ($payroll) use ($request) {
            $allotment = EmergencyAllotment::query()
                ->with('division', 'district', 'upazila', 'cityCorporation', 'districtPourosova', 'location')
                ->find($payroll->emergency_allotment_id);
            $this->applyLocationFilter1($allotment, $request);
            $payroll->division = $allotment?->division;
            $payroll->district = $allotment?->district;
            $payroll->upazila = $allotment?->upazila;
            $payroll->cityCorporation = $allotment?->cityCorporation;
            $payroll->districtPourosova = $allotment?->districtPourosova;
            $payroll->location = $allotment?->location;
            $payroll->approve_count = $payroll->payrollDetails()->where('status_id', 2)->count();
            $payroll->waiting_count = $payroll->payrollDetails()->where('status_id', 1)->count();
            $payroll->rollback_count = $payroll->payrollDetails()->whereIn('status_id', [9, 10])->count();
            return $payroll;
        });
    }
    private function applyStatusFilter($query, $status_id)
    {
        $status_id = (int)$status_id;
        switch ($status_id) {
            case 1:
                // Pending: is_submitted = 1 and others = 0
                $query->where('is_submitted', 1)
                    ->where('is_approved', 0)
                    ->where('is_rejected', 0);
                break;
            case 2:
                // Approved: is_approved = 1 and is_verified = 1, is_rejected = 0
                $query->where('is_approved', 1)
                    ->where('is_rejected', 0);
                break;
            case 3:
                // Rejected: is_rejected = 1
                $query->where('is_rejected', 1);
                break;
            default:
                // Handle any other statuses or invalid status_id
                break;
        }
        return $query;
    }
    private function applyLocationFilter1($query, $request)
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation
        $locationType = $user->assign_location?->type;
        // division->district
        // localtion_type=1; district-pouroshava->ward
        // localtion_type=2; thana->{union/pouro}->ward
        // localtion_type=3; thana->ward

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
            $query = $query->where('division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('district_id', $district_id);
        if ($city_corp_id && $city_corp_id > 0)
            $query = $query->where('city_corp_id', $city_corp_id);
        if ($district_pourashava_id && $district_pourashava_id > 0)
            $query = $query->where('district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && $upazila_id > 0)
            $query = $query->where('upazila_id', $upazila_id);

        if ($pourashava_id && $pourashava_id > 0)
            $query = $query->where('pourashava_id', $pourashava_id);
        if ($thana_id && $thana_id > 0)
            $query = $query->where('thana_id', $thana_id);
        if ($union_id && $union_id > 0)
            $query = $query->where('union_id', $union_id);
        if ($ward_id && $ward_id > 0)
            $query = $query->where('ward_id', $ward_id);

        return $query;
    }

    public function appssrovePayroll(Request $request, $id)
    {

        dd($id);
        $perCycleBeneficiaryLimit = 30;
        try {
            $payroll = EmergencyPayroll::find($request->payroll_id);
            if (!$payroll) {
                return [
                    'statusCode' => 404,
                    'message' => 'Payroll not found'
                ];
            }

            // Check if the payroll is already approved
            if ($payroll->is_approved) {
                return [
                    'statusCode' => 400,
                    'message' => 'Emergency payroll already approved'
                ];
            }

            $totalBeneficiaries = $payroll->total_beneficiaries ?? 0;
            $totalAmount = $payroll->total_amount ?? 0.00;
            $subTotalAmount = $payroll->sub_total_amount ?? 0.00;
            $totalCharge = $payroll->total_charge ?? 0.00;
            DB::beginTransaction();
            // Get existing pending payment cycles
            $existingCycles = EmergencyPayrollPaymentCycle::where('financial_year_id', $payroll->financial_year_id)
                ->where('installment_schedule_id', $payroll->installment_schedule_id)
                ->where('status', '=', 'Pending')
                ->get();

            $remainingBeneficiaries = $totalBeneficiaries;
            $remainingAmount = $totalAmount;
            $remainingSubTotalAmount = $subTotalAmount;
            $remainingCharge = $totalCharge;

            // Process existing cycles
            foreach ($existingCycles as $cycle) {
                $availableSpace = $perCycleBeneficiaryLimit - $cycle->total_beneficiaries;

                if ($availableSpace > 0) {
                    $beneficiariesToAdd = min($remainingBeneficiaries, $availableSpace);
                    $amountToAdd = $totalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                    $subTotalToAdd = $subTotalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                    $chargeToAdd = $totalCharge / $totalBeneficiaries * $beneficiariesToAdd;

                    $cycle->total_beneficiaries += $beneficiariesToAdd;
                    $cycle->total_amount += $amountToAdd;
                    $cycle->sub_total_amount += $subTotalToAdd;
                    $cycle->total_charge += $chargeToAdd;
                    $cycle->update();

                    $remainingBeneficiaries -= $beneficiariesToAdd;
                    $remainingAmount -= $amountToAdd;
                    $remainingSubTotalAmount -= $subTotalToAdd;
                    $remainingCharge -= $chargeToAdd;

                    $this->insertPaymentCycleDetails($cycle->id, $beneficiariesToAdd, $payroll->payrollDetails);

                    if ($remainingBeneficiaries <= 0) {
                        break;
                    }
                }
            }
            $cycle_index = 0;
            // Create new cycles if necessary
            while ($remainingBeneficiaries > 0) {
                $beneficiariesToAdd = min($remainingBeneficiaries, $perCycleBeneficiaryLimit);
                $amountToAdd = $totalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                $subTotalToAdd = $subTotalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                $chargeToAdd = $totalCharge / $totalBeneficiaries * $beneficiariesToAdd;

                $cycleName = $this->createCycleName($payroll->installment_schedule_id);
                $cycle = new EmergencyPayrollPaymentCycle();
                $cycle->emergency_payroll_id = $payroll->id;
                $cycle->financial_year_id = $payroll->financial_year_id;
                $cycle->installment_schedule_id = $payroll->installment_schedule_id;
                $cycle->program_id = $payroll->emergency_allotment_id;
                $cycleName['formatted_months_en'] .= $cycle_index > 0 ? "_$cycle_index" : "";
                $cycleName['formatted_months_bn'] .= $cycle_index > 0 ? "_$cycle_index" : "";
                $cycle->name_en = $cycleName['formatted_months_en'];
                $cycle->name_bn = $cycleName['formatted_months_bn'];
                $cycle->total_beneficiaries = $beneficiariesToAdd;
                $cycle->total_amount = $amountToAdd;
                $cycle->sub_total_amount = $subTotalToAdd;
                $cycle->total_charge = $chargeToAdd;
                $cycle->status = "Pending";
                $cycle->created_by_id = auth()->user()->id;
                $cycle->updated_by_id = auth()->user()->id;
                $cycle->save();

                $this->insertPaymentCycleDetails($cycle->id, $beneficiariesToAdd, $payroll->payrollDetails);

                $remainingBeneficiaries -= $beneficiariesToAdd;
                $remainingAmount -= $amountToAdd;
                $remainingSubTotalAmount -= $subTotalToAdd;
                $remainingCharge -= $chargeToAdd;
                $cycle_index++;
            }
            $payroll->is_rejected = false;
            $payroll->rejected_by_id = null;
            $payroll->rejected_at = null;
            if ($payroll->rejected_doc) {
                \Storage::delete($payroll->rejected_doc);
            }
            // Update payroll approval status
            $payroll->is_approved = 1;
            $payroll->is_payment_cycle_generated = 1;
            $payroll->approved_by_id = auth()->user()->id;
            $payroll->approved_at = Carbon::now();
            $payroll->payment_cycle_generated_at = Carbon::now();
            $payroll->approved_note = $request->note ?? null;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('public/emergency/payroll/approval');
                $payroll->approved_doc = $imagePath;
            }
            $payroll->save();

            DB::commit();
            return [
                'statusCode' => 200,
                'message' => 'Emergency Payroll Approved Successfully',
                'data' => $payroll
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'statusCode' => 0,
                'message' => $th->getMessage(),
            ];
        }
    }
    public function approvePayroll(Request $request, $id): array
    {
        $perCycleBeneficiaryLimit = 300;
        try {
            $payroll = EmergencyPayroll::where('id', $id)
                ->where('is_submitted', 1)
                ->with(['program', 'installmentSchedule', 'emergencyPayrollDetails' => function ($query) {
                    $query->where('is_set', 1);
                }])->first();

            if (!$payroll) {
                return [
                    'statusCode' => 404,
                    'message' => 'Emergency Payroll not found'
                ];
            }

            // Check if the payroll is already approved
            if ($payroll->is_approved) {
                return [
                    'statusCode' => 400,
                    'message' => 'Emergency Payroll already approved'
                ];
            }

            // Calculate totals
            $totalAmount = 0;
            $subTotalAmount = 0;
            $totalCharge = 0;

            foreach ($payroll->emergencyPayrollDetails as $detail) {
                $totalAmount += $detail->total_amount;
                $subTotalAmount += $detail->amount;
                $totalCharge += $detail->charge;
            }

            $totalBeneficiaries = count($payroll->emergencyPayrollDetails);

            DB::beginTransaction();

            // Get existing pending payment cycles
            $existingCycles = EmergencyPayrollPaymentCycle::where('financial_year_id', $payroll->financial_year_id)
                ->where('installment_schedule_id', $payroll->installment_schedule_id)
                ->where('status', '=', 'Pending')
                ->get();

            $remainingBeneficiaries = $totalBeneficiaries;
            $remainingAmount = $totalAmount;
            $remainingSubTotalAmount = $subTotalAmount;
            $remainingCharge = $totalCharge;

            // Process existing cycles
            foreach ($existingCycles as $cycle) {
                $availableSpace = $perCycleBeneficiaryLimit - $cycle->total_beneficiaries;

                if ($availableSpace > 0) {
                    $beneficiariesToAdd = min($remainingBeneficiaries, $availableSpace);
                    $amountToAdd = $totalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                    $subTotalToAdd = $subTotalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                    $chargeToAdd = $totalCharge / $totalBeneficiaries * $beneficiariesToAdd;

                    $cycle->total_beneficiaries += $beneficiariesToAdd;
                    $cycle->total_amount += $amountToAdd;
                    $cycle->sub_total_amount += $subTotalToAdd;
                    $cycle->total_charge += $chargeToAdd;
                    $cycle->update();

                    $remainingBeneficiaries -= $beneficiariesToAdd;
                    $remainingAmount -= $amountToAdd;
                    $remainingSubTotalAmount -= $subTotalToAdd;
                    $remainingCharge -= $chargeToAdd;

                    $this->insertPaymentCycleDetails($cycle->id, $payroll, $beneficiariesToAdd, $payroll->emergencyPayrollDetails);

                    if ($remainingBeneficiaries <= 0) {
                        break;
                    }
                }
            }

            // Create new cycles if necessary
            $cycle_index = 0;
            while ($remainingBeneficiaries > 0) {
                $beneficiariesToAdd = min($remainingBeneficiaries, $perCycleBeneficiaryLimit);
                $amountToAdd = $totalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                $subTotalToAdd = $subTotalAmount / $totalBeneficiaries * $beneficiariesToAdd;
                $chargeToAdd = $totalCharge / $totalBeneficiaries * $beneficiariesToAdd;

                $cycleName = $this->createCycleName($payroll->installment_schedule_id);

                // Check if cycle name exists and increment if necessary
                while (EmergencyPayrollPaymentCycle::where('name_en', $cycleName['formatted_months_en'] . ($cycle_index > 0 ? "_$cycle_index" : ""))->exists()) {
                    $cycle_index++;
                }

                $cycle = new EmergencyPayrollPaymentCycle();
                $cycle->emergency_payroll_id  = $payroll->id;
                $cycle->financial_year_id = $payroll->financial_year_id;
                $cycle->installment_schedule_id = $payroll->installment_schedule_id;
                $cycle->program_id = $payroll->program_id;
                $cycle->name_en = $cycleName['formatted_months_en'] . ($cycle_index > 0 ? "_$cycle_index" : "");
                $cycle->name_bn = $cycleName['formatted_months_bn'] . ($cycle_index > 0 ? "_$cycle_index" : "");
                $cycle->total_beneficiaries = $beneficiariesToAdd;
                $cycle->total_amount = $amountToAdd;
                $cycle->sub_total_amount = $subTotalToAdd;
                $cycle->total_charge = $chargeToAdd;
                $cycle->status = "Pending";
                $cycle->created_by_id = auth()->user()->id;
                $cycle->updated_by_id = auth()->user()->id;
                $cycle->save();

                $this->insertPaymentCycleDetails($cycle->id, $payroll, $beneficiariesToAdd, $payroll->emergencyPayrollDetails);

                $remainingBeneficiaries -= $beneficiariesToAdd;
                $remainingAmount -= $amountToAdd;
                $remainingSubTotalAmount -= $subTotalToAdd;
                $remainingCharge -= $chargeToAdd;
                $cycle_index++;
            }

            //            Update details table

            EmergencyPayrollDetails::where('emergency_payroll_id', $payroll->id)->where('is_set', 1)->update(['status_id' => 2, 'status' => 'Approved']);

            foreach ($payroll->emergencyPayrollDetails as $payrollDetailsData) {
                EmergencyBeneficiaryPayrollPaymentStatusLog::create([
                    'emergency_beneficiary_id' => $payrollDetailsData->emergency_beneficiary_id,
                    'emergency_payroll_details_id' => $payrollDetailsData->id,
                    'created_by' => auth()->user()->id,
                    'created_at' => now(),
                    'status_id' => 2,
                ]);
            }

            // Update payroll approval status
            $payroll->is_rejected = false;
            $payroll->rejected_note = null;
            $payroll->rejected_by_id = null;
            $payroll->rejected_at = null;
            $payroll->is_approved = 1;
            $payroll->is_payment_cycle_generated = 1;
            $payroll->approved_by_id = auth()->user()->id;
            $payroll->approved_at = Carbon::now();
            $payroll->payment_cycle_generated_at = Carbon::now();
            $payroll->save();

            DB::commit();
            return [
                'statusCode' => 200,
                'message' => 'Emergency Payroll Approved Successfully',
                'data' => $payroll
            ];
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'statusCode' => 0,
                'message' => $th->getMessage(),
            ];
        }
    }
    private function insertPaymentCycleDetails($cycleId, $payroll, $beneficiariesCount, $payrollDetails): void
    {
        static $detailIndex = 0;
        $slicedPayrollDetails = array_slice($payrollDetails->toArray(), $detailIndex, $beneficiariesCount);

        foreach ($slicedPayrollDetails as $payrollDetail) {
            // Check if the detail is already processed
            $existingDetail = EmergencyPayrollPaymentCycleDetails::where('emergency_cycle_id', $cycleId)
                ->where('emergency_payroll_detail_id', $payrollDetail['id'])
                ->first();

            if (!$existingDetail) {
                $paymentCycleDetail = new EmergencyPayrollPaymentCycleDetails();
                $paymentCycleDetail->emergency_cycle_id  = $cycleId;
                $paymentCycleDetail->emergency_payroll_id  = $payrollDetail['emergency_payroll_id'];
                $paymentCycleDetail->emergency_payroll_detail_id  = $payrollDetail['id'];
                $paymentCycleDetail->emergency_beneficiary_id  = $payrollDetail['emergency_beneficiary_id'];
                $paymentCycleDetail->total_amount = $payrollDetail['total_amount'];
                $paymentCycleDetail->amount = $payrollDetail['amount'];
                $paymentCycleDetail->charge = $payrollDetail['charge'];
                $paymentCycleDetail->status = "Pending";
                $paymentCycleDetail->financial_year_id = $payroll->financial_year_id;
                $paymentCycleDetail->installment_schedule_id = $payroll->installment_schedule_id;
                $paymentCycleDetail->program_id = $payroll->program_id;
                $paymentCycleDetail->updated_by_id = auth()->user()->id;
                $paymentCycleDetail->status_id = 4;
                $paymentCycleDetail->save();
                EmergencyBeneficiaryPayrollPaymentStatusLog::create([
                    'emergency_beneficiary_id' => $payrollDetail['emergency_beneficiary_id'],
                    'emergency_payroll_details_id' => $payrollDetail['id'],
                    'created_by' => auth()->user()->id,
                    'created_at' => now(),
                    'status_id' => 4,
                ]);
            }
        }
        $detailIndex += $beneficiariesCount;
    }

    private function createCycleName($id): array
    {
        $installment = PayrollInstallmentSchedule::where('id', $id)->select('installment_name', 'installment_name_bn')->first();
        if ($installment) {
            $installmentName = $installment->installment_name;
            $installmentNameBn = $installment->installment_name_bn;
            $year = date('Y');
            $formattedMonthsEn = $this->formatMonthEn($installmentName, $year);
            $formattedMonthsBn = $this->formatMonthBn($installmentNameBn, $year);
            return [
                'installment_name' => $installmentName,
                'installment_name_bn' => $installmentNameBn,
                'formatted_months_en' => $formattedMonthsEn,
                'formatted_months_bn' => $formattedMonthsBn
            ];
        } else {
            return [
                'installment_name' => '',
                'installment_name_bn' => '',
                'formatted_months_en' => '',
                'formatted_months_bn' => ''
            ];
        }
    }

    private function formatMonthEn($installmentName, $year): string
    {

        preg_match('/\((.*?)\)/', $installmentName, $matches);
        if (isset($matches[1])) {
            $monthRange = $matches[1];
            $months = explode(' - ', $monthRange);
            if (count($months) > 1) {
                return $months[0] . '/' . $year . ' - ' . $months[1] . '/' . $year;
            }
            return $months[0] . '/' . $year;
        } else {
            return '';
        }
    }

    private function formatMonthBn($installmentNameBn, $year): string
    {
        preg_match('/\((.*?)\)/', $installmentNameBn, $matches);
        if (isset($matches[1])) {
            $monthRange = $matches[1];
            $months = explode(' - ', $monthRange);
            if (count($months) > 1) {
                return $months[0] . '/' . $year . ' - ' . $months[1] . '/' . $year;
            }
            return $months[0] . '/' . $year;
        } else {
            return '';
        }
    }

    public function rejectPayroll($request)
    {
        try {
            $payroll = EmergencyPayroll::findOrFail($request->payroll_id);
            if ($payroll !== null && $payroll->emergencyPayrollDetails->isNotEmpty()) {
                DB::beginTransaction();
                foreach ($payroll->emergencyPayrollDetails as $payrollDetailsData) {
                    EmergencyBeneficiaryPayrollPaymentStatusLog::create([
                        'emergency_beneficiary_id' => $payrollDetailsData->emergency_beneficiary_id,
                        'emergency_payroll_details_id' => $payrollDetailsData->id,
                        'created_by' => auth()->id(),
                        'created_at' => now(),
                        'status_id' => 3,
                    ]);
                }
                EmergencyPayrollDetails::where('emergency_payroll_id', $payroll->id)
                    ->update([
                        'status_id' => 3,
                        'is_set' => 0,
                        'amount' => 0,
                        'charge' => 0,
                        'total_amount' => 0,
                        'status' => 'Rejected',
                    ]);

                $payroll->update([
                    'total_beneficiaries' => 0,
                    'total_charge' => 0,
                    'sub_total_amount' => 0,
                    'total_amount' => 0,
                    'is_rejected' => true,
                    'rejected_by_id' => auth()->id(),
                    'rejected_at' => now(),
                    'rejected_doc' => $request->hasFile('rejected_doc')
                        ? $request->file('rejected_doc')->store('public/emergency/payroll/reject')
                        : $payroll->rejected_doc,  // If rejected_doc is not uploaded, retain the old document
                ]);
                DB::commit();
                return $payroll;
            } else {
                return [
                    'error' => 'Payroll details not found for this payroll',
                    'details' => 'Payroll details not found for this payroll',
                ];
            }
        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'error' => 'Failed to process payroll due to an internal error',
                'details' => $e->getMessage(),
            ];
        }
    }

    /**
     * @param $query
     * @param $request
     * @return mixed
     */
    private function applyLocationFilter($query, $request): mixed
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation
        $locationType = $user->assign_location?->type;

        $division_id = $request->query('division_id');
        $district_id = $request->query('district_id');
        //        $location_type_id = $request->query('location_type_id');
        $city_corp_id = $request->query('city_corp_id');
        $district_pourashava_id = $request->query('district_pourashava_id');
        $upazila_id = $request->query('upazila_id');
        //        $sub_location_type_id = $request->query('sub_location_type_id');
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
}
