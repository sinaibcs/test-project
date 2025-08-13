<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryResource;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyPayrollPaymentCycle;
use App\Models\EmergencyPayrollPaymentCycleDetails;
use App\Models\EmergencyBeneficiaryPayrollPaymentStatusLog;
use App\Models\PayrollPaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EmergencySupplementaryController extends Controller
{
    public function getPayrollPaymentStatus()
    {
        $status = PayrollPaymentStatus::all();
        return response()->json($status);
    }

    public function getCycleFinancialYear()
    {
        $cycles = EmergencyPayrollPaymentCycle::with('financialYear')->get();

        $groupedItems = $cycles->map(function ($cycle) {
            return $cycle->financialYear;
        })->groupBy('id')->map(function ($group) {
            return $group->first();
        });

        return response()->json($groupedItems->values());
    }

    public function getEmergencyCycleInstallments()
    {
        $cycles = EmergencyPayrollPaymentCycle::with('installment')->get();

        $groupedInstallments = $cycles->map(function ($cycle) {
            return $cycle->installment;
        })->groupBy('id')->map(function ($group) {
            return $group->first();
        });

        return response()->json($groupedInstallments->values());
    }
    public function getEmergencyCyclePrograms()
    {
        $cycles = EmergencyPayrollPaymentCycle::with('program')->get();

        $groupedProgram = $cycles->map(function ($cycle) {
            return $cycle->program;
        })->groupBy('id')->map(function ($group) {
            return $group->first();
        });

        return response()->json($groupedProgram->values());
    }

    public function emergencySupplementaryPayrollData(Request $request)
    {
        $query = EmergencyPayrollPaymentCycle::query()
            ->whereIn('status', ['Partially Completed', 'Initiated'])
            ->with(['program', 'financialYear', 'installment']); // Load the necessary relationships

        if (request()->has('search')) {
            $searchTerm = request('search');

            $query->where(function ($q) use ($searchTerm) {
                $q->where('cycle_id', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name_en', 'like', '%' . $searchTerm . '%')
                    ->orWhere('name_bn', 'like', '%' . $searchTerm . '%')
                    // Search within related program
                    ->orWhereHas('program', function ($q) use ($searchTerm) {
                        $q->where('name_en', 'like', '%' . $searchTerm . '%')
                            ->orWhere('name_bn', 'like', '%' . $searchTerm . '%');
                    })
                    // Search within related financialYear
                    ->orWhereHas('financialYear', function ($q) use ($searchTerm) {
                        $q->where('financial_year', 'like', '%' . $searchTerm . '%');
                    })
                    // Search within related installment
                    ->orWhereHas('installment', function ($q) use ($searchTerm) {
                        $q->where('installment_name', 'like', '%' . $searchTerm . '%')
                            ->orWhere('installment_name_bn', 'like', '%' . $searchTerm . '%');
                    });
            });
        }
        // filter
        if (request()->has('program_id')) {
            $programId = request('program_id');
            $query->where('program_id', $programId);
        }
        if (request()->has('status')) {
            $status = request('status');
            $query->where('status', $status);
        }
        if (request()->has('financial_year_id')) {
            $financial_year_id = request('financial_year_id');
            $query->where('financial_year_id', $financial_year_id);
        }
        if (request()->has('installment_id')) {
            $installmentId = request('installment_id');
            $query->where('installment_schedule_id', $installmentId);
        }
        if (request()->has('status_id')) {
            $statusId = request('status_id');
            $query->whereHas('CycleDetails', function ($q) use ($statusId) {
                $q->where('status_id', $statusId);
            });
        }

        $supplementary = $query->with([
            'CycleDetails' => function ($query) {
                $query->select(
                    'emergency_cycle_id',
                    \DB::raw('SUM(CASE WHEN status_id IN (7,11) THEN 1 ELSE 0 END) as failed_count'), //failed
                    \DB::raw('SUM(CASE WHEN status_id = 8 THEN 1 ELSE 0 END) as resubmitted_count'), //inforamton updated
                    \DB::raw('SUM(CASE WHEN status_id = 10 THEN 1 ELSE 0 END) as deleted_count'), //deleted
                    \DB::raw('SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as initiated_count'), //initiated_count or processing at ibas++
                    \DB::raw('SUM(CASE WHEN status_id IN (7, 8, 10, 5) THEN 1 ELSE 0 END) as status_total')
                )->groupBy('emergency_cycle_id');
            },
            'program',
            'installment',
            'financialYear'
        ])->paginate(request('perPage'));

        return response()->json($supplementary);
    }

    //get revised beneficiary or information updated beneficiary
    public function emergencySupplementaryPayrollShow(Request $request, $id)
    {
        $payroll = EmergencyPayrollPaymentCycle::with('CycleDetails.beneficiary.program', 'CycleDetails.EmergencyPayroll.FinancialYear', 'CycleDetails.EmergencyPayroll.installment')->find($id);
        // dd($payroll);
        if (!$payroll) {
            return response()->json(['error' => 'Payroll not found'], 404);
        }

        $cycleDetails = $payroll->CycleDetails->filter(function ($cycleDetail) {
            return $cycleDetail->status_id == 8; // revised cycle details or information update beneficiary
        });

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $cycleDetails = $cycleDetails->filter(function ($cycleDetail) use ($search) {
                return Str::contains(Str::lower($cycleDetail->beneficiary->name_en), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiary->name_bn), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiary->program->name_en ?? ''), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiary->program->name_bn ?? ''), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiary->verification_number), Str::lower($search));
            });
        }

        $beneficiaries = $cycleDetails->map(function ($cycleDetail) {
            return [
                'emergency_cycle_id' => $cycleDetail->emergency_cycle_id,
                'emergency_cycle_details_id' => $cycleDetail->id,
                'emergency_payroll_id' => $cycleDetail->emergency_payroll_id,
                'emergency_beneficiary_id' => $cycleDetail->emergency_beneficiary_id,
                'financial_year' => $cycleDetail->EmergencyPayroll->FinancialYear->financial_year,
                'installment_name_en' => $cycleDetail->EmergencyPayroll->installment->installment_name,
                'installment_name_bn' => $cycleDetail->EmergencyPayroll->installment->installment_name_bn,
                'program_name_en' => $cycleDetail->beneficiary->program->name_en ?? null,
                'program_name_bn' => $cycleDetail->beneficiary->program->name_bn ?? null,
                'name_en' => $cycleDetail->beneficiary->name_en,
                'name_bn' => $cycleDetail->beneficiary->name_bn,
                'mobile' => $cycleDetail->beneficiary->mobile,
                'date_of_brith' => $cycleDetail->beneficiary->date_of_birth,
                'verification_number' => $cycleDetail->beneficiary->verification_number,
                // 'verification_type' => $cycleDetail->EmergencyBeneficiary->verification_type,
                'total_amount' => $cycleDetail->total_amount,
                'amount' => $cycleDetail->amount,
            ];
        });

        //     $beneficiariesArray = $beneficiaries->toArray();
        // $beneficiariesCollection = collect($beneficiariesArray);
        // $paginatedBeneficiaries = $beneficiariesCollection->paginate(10);

        // return response()->json($paginatedBeneficiaries);

        $beneficiariesArray = $beneficiaries->toArray();

        // Paginate the array data
        $currentPage = Paginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = array_slice($beneficiariesArray, ($currentPage - 1) * $perPage, $perPage);

        $paginatedBeneficiaries = new LengthAwarePaginator(
            $currentPageItems,
            count($beneficiariesArray),
            $perPage,
            $currentPage,
            ['path' => Paginator::resolveCurrentPath()]
        );

        return response()->json($paginatedBeneficiaries);
    }

    public function beneficiaryDetails($id)
    {
        try {

            $beneficiary = EmergencyBeneficiary::with(
                'program',
                'gender',
                'currentDivision',
                'currentDistrict',
                'currentCityCorporation',
                'currentDistrictPourashava',
                'currentUpazila',
                'currentPourashava',
                'currentThana',
                'currentUnion',
                'currentWard',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard',
                // 'financialYear'
            )
                ->find($id);
            if ($beneficiary) {
                return BeneficiaryResource::make($beneficiary)->additional([
                    'success' => true,
                    'message' => "Beneficiary found",
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => "Beneficiary not found",
                ], ResponseAlias::HTTP_OK);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function emergencySupplementaryPayrollUpdate(Request $request)
    {
        try {
            DB::beginTransaction();
            $requestData = $request->all();

            foreach ($requestData as $data) {
                $emergencyCycle = EmergencyPayrollPaymentCycleDetails::find($data['emergency_cycle_details_id']);

                if (!$emergencyCycle) {
                    return response()->json(['message' => 'Emergency cycle details not found.'], 404);
                }

                $emergencyCycle->update(['status_id' => 5]);

                EmergencyBeneficiaryPayrollPaymentStatusLog::create([
                    'emergency_beneficiary_id' => $emergencyCycle->emergency_beneficiary_id,
                    'emergency_payroll_details_id' => $emergencyCycle->emergency_payroll_detail_id,
                    'emergency_payment_cycle_details_id' => $emergencyCycle->id,
                    'status_id' => 5,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'Emergency supplementary payroll updated successfully.'], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to update emergency supplementary payroll.'], 500);
        }
    }
}
