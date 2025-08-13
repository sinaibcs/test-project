<?php

namespace App\Http\Controllers\Api\V1\Admin\Payroll;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryResource;
use App\Http\Services\Admin\Payment\IbusDataService;
use App\Http\Services\Admin\Payment\IbusPpService;
use App\Models\Beneficiary;
use App\Models\BeneficiaryPayrollPaymentStatusLog;
use App\Models\PayrollPaymentCycle;
use App\Models\PayrollPaymentCycleDetail;
use App\Models\PayrollPaymentStatus;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class SupplementaryController extends Controller
{
    public function getPayrollPaymentStatus()
    {
        $status = PayrollPaymentStatus::all();
        return response()->json($status);
    }

    public function getCycleInstallments()
    {
        $cycles = PayrollPaymentCycle::with('installment')->get();

        $groupedInstallments = $cycles->map(function ($cycle) {
            return $cycle->installment;
        })->groupBy('id')->map(function ($group) {
            return $group->first();
        });

        return response()->json($groupedInstallments->values());
    }

    public function getCycleFinancialYear()
    {
        $cycles = PayrollPaymentCycle::with('financialYear')->get();

        $groupedItems = $cycles->map(function ($cycle) {
            return $cycle->financialYear;
        })->groupBy('id')->map(function ($group) {
            return $group->first();
        });

        return response()->json($groupedItems->values());
    }
    public function getCyclePrograms()
    {
        $cycles = PayrollPaymentCycle::with('program')->get();

        $groupedProgram = $cycles->map(function ($cycle) {
            return $cycle->program;
        })->groupBy('id')->map(function ($group) {
            return $group->first();
        });

        return response()->json($groupedProgram->values());
    }

    public function supplementaryPayrollData(Request $request)
    {
        $query = PayrollPaymentCycle::query()
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
            $query->whereHas('PaymentCycleDetails', function ($q) use ($statusId) {
                $q->where('status_id', $statusId);
            });
        }

        $supplementary = $query->with([
            'PaymentCycleDetails' => function ($query) {
                $query->select(
                    'payroll_payment_cycle_id',
                    \DB::raw('SUM(CASE WHEN status_id IN (7,11) THEN 1 ELSE 0 END) as failed_count'), //failed
                    \DB::raw('SUM(CASE WHEN status_id = 8 THEN 1 ELSE 0 END) as resubmitted_count'), //inforamton updated
                    \DB::raw('SUM(CASE WHEN status_id = 10 THEN 1 ELSE 0 END) as deleted_count'), //deleted
                    \DB::raw('SUM(CASE WHEN status_id = 5 THEN 1 ELSE 0 END) as initiated_count'), //initiated_count or processing at ibas++
                    \DB::raw('SUM(CASE WHEN status_id IN (7, 8, 10,11, 5) THEN 1 ELSE 0 END) as status_total')
                )->groupBy('payroll_payment_cycle_id');
            },
            'program',
            'installment',
            'financialYear'
        ])
            ->paginate($request->input('perPage'));

        return response()->json($supplementary);
    }

    //get revised beneficiary or information updated beneficiary
    public function supplementaryPayrollShow(Request $request, $id)
    {
        $payroll = PayrollPaymentCycle::with('PaymentCycleDetails.beneficiaries.program', 'PaymentCycleDetails.beneficiaries.financialYear', 'PaymentCycleDetails.payroll.installmentSchedule')->find($id);

        if (!$payroll) {
            return response()->json(['error' => 'Payroll not found'], 404);
        }

        $PaymentCycleDetails = $payroll->PaymentCycleDetails->filter(function ($cycleDetail) {
            return $cycleDetail->status_id == 8; // revised cycle details or information update beneficiary
        });

        // Apply search filter if provided
        if ($request->has('search') && !empty($request->search)) {
            $search = $request->search;
            $PaymentCycleDetails = $PaymentCycleDetails->filter(function ($cycleDetail) use ($search) {
                return Str::contains(Str::lower($cycleDetail->beneficiaries->name_en), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiaries->name_bn), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiaries->beneficiary_id), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiaries->mobile), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiaries->program->name_en ?? ''), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiaries->program->name_bn ?? ''), Str::lower($search)) ||
                    Str::contains(Str::lower($cycleDetail->beneficiaries->verification_number), Str::lower($search));
            });
        }

        $beneficiaries = $PaymentCycleDetails->map(function ($cycleDetail) {
            return [
                'cycle_id' => $cycleDetail->payroll_payment_cycle_id,
                'cycle_details_id' => $cycleDetail->id,
                'payroll_id' => $cycleDetail->payroll_id,
                'beneficiary_id' => $cycleDetail->beneficiary_id,
                'beneficiary_primary_id' => $cycleDetail->beneficiaries->id,
                // 'financial_year' => $cycleDetail->EmergencyPayroll->FinancialYear->financial_year,
                // 'installment_name_en' => $cycleDetail->EmergencyPayroll->installment->installment_name,
                // 'installment_name_bn' => $cycleDetail->EmergencyPayroll->installment->installment_name_bn,
                // 'program_name_en' => $cycleDetail->EmergencyBeneficiary->program->name_en ?? null,
                // 'program_name_bn' => $cycleDetail->EmergencyBeneficiary->program->name_bn ?? null,
                'name_en' => $cycleDetail->beneficiaries->name_en,
                'name_bn' => $cycleDetail->beneficiaries->name_bn,
                'mobile' => $cycleDetail->beneficiaries->mobile,
                'date_of_birth' => $cycleDetail->beneficiaries->date_of_birth,
                'verification_number' => $cycleDetail->beneficiaries->verification_number,
                'verification_type' => $cycleDetail->beneficiaries->verification_type,
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

            $beneficiary = Beneficiary::with(
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
                ->where('beneficiary_id', $id)->first();
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

    public function supplementaryPayrollUpdate(Request $request, IbusPpService $ibusPpService, IbusDataService $ibusDataService)
    {
        $requestData = $request->all();

        if (empty($requestData)) {
            return response()->json(['message' => 'Invalid data provided.'], 422);
        }

        // Extract cycle detail IDs
        $paymentCycleDetailsIds = array_column($requestData, 'cycle_details_id');

        try {
            
            // Generate supplementary payment data and validate it
            $cycleId = $requestData[0]['cycle_id'] ?? null;
            if (!$cycleId) {
                throw new \Exception('Cycle ID is missing from request data.');
            }
            
            $payrollData = $ibusDataService->genPayrollSupplementaryPaymentData($cycleId, $paymentCycleDetailsIds);
            if (empty($payrollData)) {
                throw new \Exception('Generated payroll data is invalid or empty.');
            }
            
            // Send bulk payment to IBAS++
            $paymentRes = $ibusPpService->addBulkPayment($payrollData);
            if ($paymentRes === false) {
                throw new \Exception('Failed to send payments to IBAS++.');
            }
            
            DB::beginTransaction();
            
            foreach ($requestData as $data) {
                $cycleDetailId = $data['cycle_details_id'] ?? null;
                if (!$cycleDetailId) {
                    throw new \Exception('Cycle detail ID is missing for one of the records.');
                }

                $emergencyCycle = PayrollPaymentCycleDetail::find($cycleDetailId);

                if (!$emergencyCycle) {
                    throw new \Exception("Cycle detail with ID {$cycleDetailId} not found.");
                }

                // Update the cycle detail's status
                $emergencyCycle->update(['status_id' => 5]);

                // Log the status update securely
                BeneficiaryPayrollPaymentStatusLog::create([
                    'beneficiary_id' => $emergencyCycle->beneficiary_id,
                    'payroll_details_id' => $emergencyCycle->payroll_detail_id,
                    'payment_cycle_details_id' => $emergencyCycle->id,
                    'status_id' => 5,
                    'created_by' => auth()->id(),
                ]);
            }

            DB::commit();

            return response()->json(['message' => 'Supplementary payroll sent to IBAS++ successfully.'], 200);
        } catch (\Throwable $e) {
            DB::rollBack();

            // Log error with details for debugging
            \Log::error('Failed to send supplementary payroll to IBAS++: ' . $e->getMessage(), [
                'request_data' => $requestData,
                'user_id' => auth()->id(),
                'stack' => $e->getTraceAsString(),
            ]);

            return response()->json(['message' => 'Failed to send supplementary payroll to IBAS++.'], 500);
        }
    }

}
