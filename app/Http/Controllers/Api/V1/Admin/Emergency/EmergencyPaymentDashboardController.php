<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Http\Controllers\Controller;
use App\Models\AllowanceProgram;
use App\Models\Beneficiary;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyPayrollPaymentCycleDetails;
use Carbon\Carbon;
use Illuminate\Http\Request;

class EmergencyPaymentDashboardController extends Controller
{
    public function EmergencyProgramWisePayrollBeneficiary(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        $programs = AllowanceProgram::where('is_active', 1)
            ->with(['emergencyPayroll' => function ($query) use ($startDate, $endDate, $currentYear) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $query->whereYear('created_at', $currentYear);
                }
                $query->with('emergencyPayrollDetails');
            }])
            ->get();

        $programWisePayrollData = $programs->filter(function ($program) {
            $payrollCount = $program->emergencyPayroll->sum(function ($payroll) {
                return $payroll->emergencyPayrollDetails->count();
            });

            // Only include programs with payrolls
            return $payrollCount > 0;
        })->map(function ($program) {
            $payrollCount = $program->emergencyPayroll->sum(function ($payroll) {
                return $payroll->emergencyPayrollDetails->count();
            });

            return [
                'name_en' => $program->name_en,
                'name_bn' => $program->name_bn,
                'payroll_count' => $payrollCount,
            ];
        });

        return response()->json(['data' => $programWisePayrollData->values()->toArray()]);
    }

    public function programWisePaymentCycleBeneficiaries(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        $programs = AllowanceProgram::where('is_active', 1)
            ->with(['emergencyPayroll' => function ($query) use ($startDate, $endDate, $currentYear) {
                if ($startDate && $endDate) {
                    $query->whereBetween('payment_cycle_generated_at', [$startDate, $endDate])
                        ->where('is_payment_cycle_generated', 1);
                } else {
                    $query->whereYear('created_at', $currentYear)
                        ->where('is_payment_cycle_generated', 1);
                }
                $query->with('emergencyPayrollDetails');
            }])
            ->get();

        // $programWisePayrollData = $programs->map(function ($program) {

        //     $payrollCount = $program->payroll->sum(function ($payroll) {
        //         return $payroll->paymentCycleDetails->count();
        //     });

        //     return [
        //         'name_en' => $program->name_en,
        //         'name_bn' => $program->name_bn,
        //         'count' => $payrollCount,
        //     ];
        // });
        $programWisePayrollData = $programs->filter(function ($program) {
            $payrollCount = $program->emergencyPayroll->sum(function ($payroll) {
                return $payroll->emergencyPayrollDetails->count();
            });

            // Only include programs with payrolls
            return $payrollCount > 0;
        })->map(function ($program) {
            $payrollCount = $program->emergencyPayroll->sum(function ($payroll) {
                return $payroll->emergencyPayrollDetails->count();
            });

            return [
                'name_en' => $program->name_en,
                'name_bn' => $program->name_bn,
                'count' => $payrollCount,
            ];
        });
        return response()->json(['data' => $programWisePayrollData->values()->toArray()]);
    }

    public function paymentCycleDisbursementStatus(Request $request)
    {
        $programId = $request->input('program_id');

        $payrollPaymentCycleDetails = EmergencyPayrollPaymentCycleDetails::with('EmergencyPayroll');

        if ($programId) {
            $payrollPaymentCycleDetails->whereHas('EmergencyPayroll', function ($query) use ($programId) {
                $query->where('program_id', $programId);
            });
        }

        $pending = (clone $payrollPaymentCycleDetails)->where('status_id', 4)->count();
        $initiated = (clone $payrollPaymentCycleDetails)->where('status_id', 5)->count();
        $completed = (clone $payrollPaymentCycleDetails)->where('status_id', 6)->count();
        $failed = (clone $payrollPaymentCycleDetails)->where('status_id', 7)->count();
        $inforamtionUpdated = (clone $payrollPaymentCycleDetails)->where('status_id', 8)->count();
        $deleted = (clone $payrollPaymentCycleDetails)->where('status_id', 9)->count();

        $statusCounts = [
            // ['name_en' => 'Pending', 'name_bn' => 'অপেক্ষমাণ', 'count' => $pending],
            ['name_en' => 'Initiated', 'name_bn' => 'প্রারম্ভিক', 'count' => $initiated],
            ['name_en' => 'Completed', 'name_bn' => 'সম্পন্ন', 'count' => $completed],
            ['name_en' => 'Failed', 'name_bn' => 'বিফল', 'count' => $failed],
            ['name_en' => 'Information Updated', 'name_bn' => 'তথ্য আপডেট করা হয়েছে', 'count' => $inforamtionUpdated],
            ['name_en' => 'Deleted', 'name_bn' => 'মুছে ফেলা হয়েছে', 'count' => $deleted],
        ];

        return response()->json($statusCounts);
    }

    public function emergencyDashboardData()
    {
        $emergencyAllotments = EmergencyAllotment::count();
        $existingBeneficiaries = EmergencyBeneficiary::where('isExisting',1)->count();
        $emergencyBeneficiaries = EmergencyBeneficiary::where('isExisting',0)->count();

        return response()->json([
            'emergency_allotments' => $emergencyAllotments,
            'beneficiary' => $existingBeneficiaries,
            'emergency_beneficiaries' => $emergencyBeneficiaries
        ]);
    }

    public function programBalance(Request $request)
    {
        $request->validate([
            'program_id' => 'integer',
        ]);

        $programId = $request->input('program_id');
        $programsQuery = AllowanceProgram::with('programAmount');

        if ($programId) {
            $programsQuery->where('id', $programId);
        }
        $programs = $programsQuery->get();
        $totalAmount = 0;

        foreach ($programs as $program) {
            if ($program->programAmount) {
                $totalAmount += $program->programAmount->amount ?? 0;
            }
        }

        $paymentCycleDetails = EmergencyPayrollPaymentCycleDetails::with('emergencyPayroll')->where('status_id', 6);
        if ($programId) {
            $paymentCycleDetails = $paymentCycleDetails->whereHas('emergencyPayroll', function ($q) use ($programId) {
                $q->where('program_id', $programId);
            });
        }
        $paymentCycleDetails = $paymentCycleDetails->get();
        $totalDisbursed = 0;

        foreach ($paymentCycleDetails as $item) {
            $totalDisbursed += $item['total_amount'] ?? 0;
        }
        $remaining = $totalAmount - $totalDisbursed;

        $data = [
            [
                'name_en' => 'Total Amount',
                'name_bn' => 'মোট পরিমাণ',
                'count' => $totalAmount
            ],
            [
                'name_en' => 'Total Disbursed',
                'name_bn' => 'মোট বিতরণ',
                'count' => $totalDisbursed
            ],
            [
                'name_en' => 'Remaining',
                'name_bn' => 'অবশিষ্ট',
                'count' => $remaining
            ]
        ];
        return response()->json(['data' => $data]);
    }
}
