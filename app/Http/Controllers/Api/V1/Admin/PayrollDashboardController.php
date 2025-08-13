<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AllowanceProgram;
use App\Models\Beneficiary;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyPayrollPaymentCycleDetails;
use App\Models\Location;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use App\Models\PayrollPaymentCycle;
use App\Models\PayrollPaymentCycleDetail;
use App\Models\PayrollPaymentProcessor;
use App\Models\PayrolPaymentCycle;
use Carbon\Carbon;
use DB;
use Illuminate\Http\Request;

class PayrollDashboardController extends Controller
{
    public function payrollData()
    {
        $financialYear  = getCurrentFinancialYear();
        $payroll = Payroll::where('financial_year_id', $financialYear->id)->get();
        $totalApproved = $payroll->where('is_approved', 1)->count();
        $totalRejected = $payroll->where('is_rejected', 1)->count();
        $totalSent = PayrollDetail::whereHas('payroll', function ($q) use($financialYear){
             $q->where('financial_year_id', $financialYear->id);
        })->where('is_set', 1)->count();
        return [
            'payroll' => $payroll,
            'totalCompleted' => $totalApproved,
            'totalRejected' => $totalRejected,
            'totalSent' => $totalSent,
        ];
    }

    public function paymentCycleStatusData()
    {
        $paymentCycle = PayrollPaymentCycle::get();
        $totalPaymentCycle = $paymentCycle->count();
        $totalProcessingIbos = $paymentCycle->where('status_id', 5)->count();

        return [
            'total_payment_cycle' => $totalPaymentCycle,
            'total_processing' => $totalProcessingIbos
        ];
    }

    public function programWisePayroll(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        $programs = AllowanceProgram::where('is_active', 1)
            ->with(['payroll' => function ($query) use ($startDate, $endDate, $currentYear) {
                if ($startDate && $endDate) {
                    $query->whereBetween('created_at', [$startDate, $endDate]);
                } else {
                    $query->whereYear('created_at', $currentYear);
                }
                $query->with('payrollDetails');
            }])
            ->get();

        $programWisePayrollData = $programs->map(function ($program) {

            $payrollCount = $program->payroll->sum(function ($payroll) {
                return $payroll->payrollDetails->count();
            });

            return [
                'name_en' => $program->name_en,
                'name_bn' => $program->name_bn,
                'payroll_count' => $payrollCount,
            ];
        });



        return response()->json([
            'data' => $programWisePayrollData
        ]);
    }

    public function programWisePaymentCycle(Request $request)
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $currentYear = Carbon::now()->year;

        // if ($startDate && $endDate) {
        //     $programs = AllowanceProgram::where('is_active', 1)
        //         ->with(['payroll' => function ($query) use ($startDate, $endDate) {
        //             $query->whereBetween('payment_cycle_generated_at', [$startDate, $endDate])
        //                 ->where('is_payment_cycle_generated', 1);
        //         }])
        //         ->get();
        // } else {
        //     $programs = AllowanceProgram::where('is_active', 1)
        //         ->with(['payroll' => function ($query) use ($currentYear) {
        //             $query->whereYear('payment_cycle_generated_at', $currentYear)
        //                 ->where('is_payment_cycle_generated', 1);
        //         }])
        //         ->get();
        // }

        $programs = AllowanceProgram::where('is_active', 1)
            ->with(['payroll' => function ($query) use ($startDate, $endDate, $currentYear) {
                if ($startDate && $endDate) {
                    $query->whereBetween('payment_cycle_generated_at', [$startDate, $endDate])
                        ->where('is_payment_cycle_generated', 1);
                } else {
                    $query->whereYear('created_at', $currentYear)
                        ->where('is_payment_cycle_generated', 1);
                }
                $query->with('paymentCycleDetails');
            }])
            ->get();

        $programWisePayrollData = $programs->map(function ($program) {

            $payrollCount = $program->payroll->sum(function ($payroll) {
                return $payroll->paymentCycleDetails->count();
            });

            return [
                'name_en' => $program->name_en,
                'name_bn' => $program->name_bn,
                'count' => $payrollCount,
            ];
        });

        return response()->json([
            'data' => $programWisePayrollData
        ]);
    }

    public function monthlyApprovedPayroll(Request $request)
    {
        try {
            $currentDate = now();
            $currentYear = Carbon::now()->year;

            // Determine the start and end of the fiscal year based on the current month
            if ($currentDate->month >= 7) {
                $startDate = Carbon::create($currentYear, 7, 1);
                $endDate = Carbon::create($currentYear + 1, 6, 30);
            } else {
                $startDate = Carbon::create($currentYear - 1, 7, 1);
                $endDate = Carbon::create($currentYear, 6, 30);
            }

            $programId = $request->input('program_id');
            $query = Payroll::selectRaw('YEAR(created_at) as year, MONTH(created_at) as month, COUNT(*) as count')
                ->where('is_approved', 1)
                ->whereBetween('created_at', [$startDate, $endDate])
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc');

            if ($programId) {
                $query->where('program_id', $programId);
            }

            $results = $query->get();

            $monthlyPayrollCount = collect();
            // Generate the month/year structure based on the fiscal year
            for ($i = 7; $i <= 12; $i++) {
                $monthlyPayrollCount->push([
                    'year' => $startDate->year % 100,
                    'month' => $i,
                    'count' => 0,
                    'month_name' => Carbon::create()->month($i)->format('M') . '(' . $startDate->year % 100 . ')'
                ]);
            }
            for ($i = 1; $i <= 6; $i++) {
                $monthlyPayrollCount->push([
                    'year' => $endDate->year % 100,
                    'month' => $i,
                    'count' => 0,
                    'month_name' => Carbon::create()->month($i)->format('M') . '(' . $endDate->year % 100 . ')'
                ]);
            }

            // Update the count based on the results
            $results->each(function ($item) use ($monthlyPayrollCount) {
                $monthlyPayrollCount->transform(function ($monthData) use ($item) {
                    if ($monthData['month'] === $item->month && $monthData['year'] === $item->year % 100) {
                        $monthData['count'] = $item->count;
                    }
                    return $monthData;
                });
            });

            return [
                'data' => $monthlyPayrollCount
            ];
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function totalPaymentProcessor(Request $request)
    {

        $paymentType = $request->input('payment_type');

        $locations = Location::whereType('division')->get();

        $query = PayrollPaymentProcessor::with('ProcessorArea');

        if ($paymentType) {
            $query->where('processor_type', $paymentType);
        }

        $processorsByDivision = $query->get()
            ->groupBy('ProcessorArea.division_id')
            ->map(function ($processors) {
                return $processors->count();
            });

        $result = $locations->map(function ($location) use ($processorsByDivision) {
            return [
                'name_en' => $location->name_en,
                'name_bn' => $location->name_bn,
                'count' => $processorsByDivision->get($location->id, 0),
            ];
        });

        return [
            'data' => $result
        ];
    }

    public function totalAmountDisbursed(Request $request)
    {
        $request->validate([
            'program_id' => 'sometimes|integer',
        ]);

        $currentDate = now();
        $currentYear = $currentDate->year;

        if ($currentDate->month >= 7) {
            $startFiscalYear = $currentYear;
            $endFiscalYear = $currentYear + 1;
        } else {
            $startFiscalYear = $currentYear - 1;
            $endFiscalYear = $currentYear;
        }

        $years = range($startFiscalYear - 2, $endFiscalYear - 1);

        $programId = $request->input('program_id');

        $paymentCycleDetailsQuery = PayrollPaymentCycleDetail::where('status_id', 6)
            ->whereIn(DB::raw('YEAR(created_at)'), $years)
            ->select(
                DB::raw('YEAR(created_at) as year'),
                DB::raw('SUM(total_amount) as total_amount')
            )
            ->groupBy('year');

        if ($programId) {
            $paymentCycleDetailsQuery = $paymentCycleDetailsQuery->whereHas('payroll', function ($query) use ($programId) {
                $query->where('program_id', $programId);
            });
        }

        $paymentCycleDetails = $paymentCycleDetailsQuery->get();

        $data = $paymentCycleDetails->keyBy('year')->toArray();
        $currentFiscalYearRange = $startFiscalYear . '-' . $endFiscalYear;

        foreach ($years as $year) {
            if (!isset($data[$year])) {
                $data[$year] = [
                    'year' => $year . '-' . ($year + 1),
                    'total_amount' => 0,
                ];
            } else {
                $data[$year]['year'] = $year . '-' . ($year + 1);
            }

            if ($year == $startFiscalYear) {
                $data[$year]['year'] = $currentFiscalYearRange . ' Current';
            }
        }

        return response()->json(['data' => array_values($data)]);
    }

    public function programBalance(Request $request)
    {
        $request->validate([
            'program_id' => 'integer',
        ]);

        $currentYearId = getCurrentFinancialYear()?->id??0;

        $programId = $request->input('program_id');
        $programsQuery = AllowanceProgram::with('classAmounts')

        ->withCount(['allotments' => function ($query) use($currentYearId){
            $query->where('financial_year_id', $currentYearId)
                  ->select(DB::raw("SUM(total_amount) as total"));
        }])
        ->with('payroll', function($q) use($currentYearId){
            return $q->where('financial_year_id', $currentYearId)
                    ->withCount(['paymentCycleDetails' => function ($query) {
                        $query->where('status_id',6)->select(DB::raw("SUM(amount) as total"));
                    }]);
        });

        if ($programId) {
            $programsQuery->where('id', $programId);
        }
        $programs = $programsQuery->get();
        // return $programs;
        $totalAmount = 0;
        $totalDisbursed = 0;

        foreach ($programs as $program) {
            $totalAmount += $program->allotments_count??0;
            foreach($program->payroll as $payroll){
                $totalDisbursed += $payroll->payment_cycle_details_count;
            }
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
