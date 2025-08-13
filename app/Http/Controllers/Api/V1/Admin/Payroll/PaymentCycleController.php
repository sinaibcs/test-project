<?php

namespace App\Http\Controllers\Api\V1\Admin\Payroll;

use Carbon\Carbon;
use App\Helpers\Helper;
use App\Models\Payroll;
use Illuminate\Http\Request;
use App\Models\PayrollDetail;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use Illuminate\Support\Facades\DB;
use App\Models\PayrollPaymentCycle;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\PayrollPaymentCycleDetail;
use App\Models\PayrollInstallmentSchedule;
use App\Exports\PaymentCycleBeneficiaryExport;
use App\Http\Services\Admin\Payment\IbusPpService;
use App\Models\BeneficiaryPayrollPaymentStatusLog;
use App\Http\Services\Admin\Payment\IbusDataService;

class PaymentCycleController extends Controller
{
    use MessageTrait;
    const BASE_URL = 'http: //mis.bhata.gov.bd/api/mfssss';
    const PUSH_API = '/push-payroll-summary';

    public function getPaymentCycle(Request $request)
    {

        // $startDate = $request->query('start_date');
        // $endDate = $request->query('end_date');
        $searchText = $request->query('searchText');
        $installment_no = $request->query('installment_no');
        $program_id = $request->query('program_id');
        $financial_year = $request->query('financial_year');

        // return $searchText;
        $perPage = $request->query('perPage');
        $page = $request->query('page');
        $paymentCycle = PayrollPaymentCycle
            ::with(['installment','program']);

            $paymentCycle->when($searchText, function($query) use($searchText){
                $query->where('id', 'LIKE', "%$searchText%");
                $query->orwhere('name_en', 'LIKE', "%$searchText%");
                $query->orwhere('name_bn', 'LIKE', "%$searchText%");
                $query->orwhere('total_beneficiaries', 'LIKE', "%$searchText%");
                $query->orwhere('sub_total_amount', 'LIKE', "%$searchText%");
                $query->orwhere('total_charge', 'LIKE', "%$searchText%");
                $query->orwhere('total_amount', 'LIKE', "%$searchText%");

                // // Search within queryDetails relation
                $query->orWhereHas('installment', function ($query) use ($searchText) {
                    $query->where('installment_name', 'LIKE', "%$searchText%");
                    $query->orwhere('installment_name_bn', 'LIKE', "%$searchText%");
                });
                return $query;
            });

        if ($installment_no) {
            $paymentCycle->where('installment_schedule_id', $installment_no);

        }
        // if ($startDate && $endDate) {
        //     $paymentCycle->whereBetween('created_at', [$startDate, $endDate]); // return  $query->get();
        // }

        if ($program_id) {
            $paymentCycle->where('program_id', $program_id); // return  $query->get();
        }

        if ($financial_year) {
            $paymentCycle->where('financial_year_id', $financial_year); // return  $query->get();
        }

        // Adding filter to exclude CycleDetails and payroll with status_id = 9
        // $paymentCycle->whereHas('CycleDetails', function ($query) {
        //     $query->where('status_id', '!=', 9)
        //         ->whereHas('payroll', function ($subQuery) {
        //             $subQuery->where('is_rejected', '!=', 1);
        //         });
        // });

        $paymentCycle->orderBy('id', 'DESC');
        return $paymentCycle->paginate($perPage, ['*'], 'page', $page);

    }

    public function pushPayrollSummary($id, IbusDataService $ibusDataService, IbusPpService $ibusPpService)
    {
        try {
            // Fetch the payment cycle
            $paymentCycle = PayrollPaymentCycle::find($id);
            if (!$paymentCycle) {
                throw new \Exception("Payment cycle not found.");
            }

            // Generate data for IBAS++ and ensure it is valid
            $payrollData = $ibusDataService->genPayrollPaymentCycleData($paymentCycle);

            if (empty($payrollData)) {
                throw new \Exception('Generated payroll data is invalid or empty.');
            }

            // Generate data for IBAS++ and ensure it is valid
            $payrollPaymentData = $ibusDataService->genPayrollPaymentData($paymentCycle);

            if (empty($payrollData)) {
                throw new \Exception('Generated payroll payment data is invalid or empty.');
            }

            // Fetch payment cycle details
            $paymentCycleDetails = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)
            ->where('status_id', 4)
            ->get();

            if ($paymentCycleDetails->isEmpty()) {
                return response()->json([
                    'message' => 'No records found.',
                    'success' => false,
                ], 404);
            }

            // Create payment cycle on IBAS++
            $cycleRes = $ibusPpService->createPaymentCycle($payrollData);
            if ($cycleRes === false) {
                throw new \Exception('Failed to create payment cycle on IBAS++.');
            }

            if($cycleRes["operationResult"] == false){
                throw new \Exception($cycleRes["errorMsg"]);
            }

            // Add bulk payments to IBAS++
            $paymentRes = $ibusPpService->addBulkPayment($payrollPaymentData);
            if ($paymentRes === false) {
                throw new \Exception('Failed to send payments to IBAS++.');
            }

            if($paymentRes["operationResult"] == false){
                throw new \Exception($paymentRes["errorMsg"]);
            }

            return DB::transaction(function () use ($id, $paymentCycle, $paymentCycleDetails) {
                // Clone the current state for logging before updates
                $beforeUpdate = clone $paymentCycle;


                // Update payment cycle status safely
                $paymentCycle->status = 'Initiated';
                $paymentCycle->update();

                // Update each payment cycle detail
                foreach ($paymentCycleDetails as $record) {
                    $record->status_id = 5;
                    $record->update();

                    // Log status updates securely
                    $cycleLog = new BeneficiaryPayrollPaymentStatusLog();
                    $cycleLog->beneficiary_id = $record->beneficiary_id;
                    $cycleLog->payroll_details_id = $record->payroll_detail_id;
                    $cycleLog->payment_cycle_details_id = $record->id;
                    $cycleLog->status_id = 5;
                    $cycleLog->created_by = auth()->user()->id;
                    $cycleLog->save();
                }

                // Record activity logs securely
                Helper::activityLogUpdate($paymentCycle, $beforeUpdate, 'Payroll Payment Cycle', 'Payroll Payment Cycle Sent!');

                return $paymentCycleDetails;
            });
        } catch (\Throwable $e) {
            // Log the error with detailed context for easier debugging
            \Log::error("Transaction failed: " . $e->getMessage(), [
                'id' => $id,
                'user_id' => auth()->id(),
                'stack' => $e->getTraceAsString(),
            ]);

            // Ensure any exception is rethrown to maintain transactional integrity
            throw $e;
        }
    }


    public function programWiseInstallment($event)
    {

        $program = AllowanceProgram::where('id', $event)->first();
        $installment = $program->payment_cycle;
        if ($installment) {
            $installment = PayrollInstallmentSchedule::where('payment_cycle', $installment)->get();
        }
        return $installment;
    }

    public function getPaymentCycleViewById(Request $request, $id)
    {
        return $this->getPaymentCycleView($request, $id);
    }
    private function getPaymentCycleView(Request $request, $id, $payroll_id = null)
    {
        // Set memory and execution time limits to handle potentially large datasets
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        // Retrieve query parameters with default values
        $searchText = $request->query('searchText', '');
        $perPage = $request->query('perPage', 15); // Default to 15 items per page
        $page = $request->query('page', 1);     // Default to page 1

        // Step 1: Get the payment cycle (main model)
        $paymentCycle = PayrollPaymentCycle::find($id);

        if (!$paymentCycle) {
            // Throw an exception if the payment cycle is not found
            throw new \Exception("Payment Details cycle not found.");
        }

        // Step 2: Build query for related paymentCycleDetails using joins
        // Start with the base query on the payment_cycle_details table
        $detailsQuery = DB::table('payroll_payment_cycle_details as ppcd')
            ->when($payroll_id,fn($q) => $q->where('ppcd.payroll_id', $payroll_id))
            // Left join beneficiaries table
            ->leftJoin('beneficiaries', 'ppcd.beneficiary_id', '=', 'beneficiaries.beneficiary_id')
            // Left join payroll_payment_statuses table
            ->leftJoin('payroll_payment_statuses', 'ppcd.status_id', '=', 'payroll_payment_statuses.id')
            // Left join payrolls table
            ->leftJoin('payrolls', 'ppcd.payroll_id', '=', 'payrolls.id')
            // Left join payroll_installment_schedules table
            ->leftJoin('payroll_installment_schedules', 'payrolls.installment_schedule_id', '=', 'payroll_installment_schedules.id')
            // Left join programs table
            ->leftJoin('allowance_programs', 'payrolls.program_id', '=', 'allowance_programs.id')
            // Left join financial_years table
            ->leftJoin('financial_years', 'payrolls.financial_year_id', '=', 'financial_years.id')
            // Left join offices table
            ->leftJoin('offices', 'payrolls.office_id', '=', 'offices.id')
            // Left join locations table (assuming assign_location_id is foreign key in offices)
            ->leftJoin('locations', 'offices.assign_location_id', '=', 'locations.id')
            ->where('ppcd.payroll_payment_cycle_id', $id) // Filter by the main payment cycle ID
            ->select(
                // Select all columns from payroll_payment_cycle_details
                'ppcd.*',
                // Select relevant columns from beneficiaries table with aliases
                'beneficiaries.id as beneficiary_primary_id',
                'beneficiaries.name_en as beneficiary_name_en',
                'beneficiaries.name_bn as beneficiary_name_bn',
                'ppcd.beneficiary_id as beneficiary_id',
                'beneficiaries.permanent_division_id',
                'beneficiaries.permanent_district_id',
                'beneficiaries.permanent_upazila_id',
                'beneficiaries.permanent_district_pourashava_id',
                // Select relevant columns from payroll_payment_statuses table with aliases
                'payroll_payment_statuses.name_en as payment_status_name_en',
                'payroll_payment_statuses.name_bn as payment_status_name_bn',
                // Select relevant columns from payrolls table with aliases
                'payrolls.id as payroll_id', // Ensure payroll ID is selected
                // 'payrolls.payroll_date',
                // Select relevant columns from payroll_installment_schedules table with aliases
                'payroll_installment_schedules.installment_name',
                'payroll_installment_schedules.installment_name_bn',
                // Select relevant columns from programs table with aliases
                'allowance_programs.name_en as program_name_en',
                'allowance_programs.name_bn as program_name_bn',
                // Select relevant columns from financial_years table with aliases
                'financial_years.financial_year as financial_year',
                // Select relevant columns from offices table with aliases
                'offices.name_en as office_name_en',
                'offices.name_bn as office_name_bn',
                // Select relevant columns from locations table with aliases
                'locations.name_en as assign_location_name_en',
                'locations.name_bn as assign_location_name_bn'
            );

        // Apply filters directly on joined tables
        $filters = [
            'payrolls.program_id' => $request->program_id,
            'ppcd.beneficiary_id' => $request->beneficiary_id, // Filter by beneficiary ID directly
            'payrolls.financial_year_id' => $request->financial_year_id,
            'ppcd.status_id' => $request->status_id // Status is on payment_cycle_details
        ];

        foreach ($filters as $field => $value) {
            if ($value) {
                $detailsQuery->where($field, $value);
            }
        }

        // Filter by office ID
        if ($request->office_id) {
            $detailsQuery->where('offices.id', $request->office_id);
        }

        // Location-based filters directly on beneficiaries table
        if ($request->division_id) {
            $detailsQuery->where('beneficiaries.permanent_division_id', $request->division_id);
        }
        if ($request->district_id) {
            $detailsQuery->where('beneficiaries.permanent_district_id', $request->district_id);
        }
        if ($request->upazila_id) {
            $detailsQuery->where('beneficiaries.permanent_upazila_id', $request->upazila_id);
        }
        if ($request->district_pourashava_id) {
            $detailsQuery->where('beneficiaries.permanent_district_pourashava_id', $request->district_pourashava_id);
        }

        // Search filter
        if (!empty($searchText)) {
            $detailsQuery->where(function ($query) use ($searchText) {
                // Search on payment_cycle_details columns
                $query->where('ppcd.payroll_payment_cycle_id', 'LIKE', "%$searchText%")
                    ->orWhere('ppcd.amount', 'LIKE', "%$searchText%")
                    ->orWhere('ppcd.charge', 'LIKE', "%$searchText%")
                    ->orWhere('ppcd.total_amount', 'LIKE', "%$searchText%")
                    // Search on beneficiaries columns
                    ->orWhere('beneficiaries.name_en', 'LIKE', "%$searchText%")
                    ->orWhere('beneficiaries.name_bn', 'LIKE', "%$searchText%")
                    ->orWhere('beneficiaries.beneficiary_id', 'LIKE', "%$searchText%")
                    // Search on program name
                    ->orWhere('allowance_programs.name_en', 'LIKE', "%$searchText%")
                    // Search on office assign location names
                    ->orWhere('locations.name_bn', 'LIKE', "%$searchText%")
                    ->orWhere('locations.name_en', 'LIKE', "%$searchText%")
                    // Search on installment names
                    ->orWhere('payroll_installment_schedules.installment_name', 'LIKE', "%$searchText%")
                    ->orWhere('payroll_installment_schedules.installment_name_bn', 'LIKE', "%$searchText%");
            });
        }

        // Handle download request for Excel export
        if ($request->download == 'yes') {
            // Pass the query builder instance to the export class
            return Excel::download(new PaymentCycleBeneficiaryExport($detailsQuery), 'payment-cycle-beneficiary.xls');
        }

        // Step 3: Paginate the filtered results
        // When using DB::table(), paginate returns a LengthAwarePaginator
        $cycleDetails = $detailsQuery->paginate($perPage, ['*'], 'page', $page);

        // Step 4: Return response
        return response()->json([
            'paymentCycle' => $paymentCycle, // The main payment cycle object
            'cycleDetails' => $cycleDetails, // The paginated details (flat structure from joins)
        ]);
    }

    public function getPaymentCycleViewById0(Request $request, $id)
    {
        ini_set('memory_limit', -1);
        ini_set('max_execution_time', 0);

        $searchText = $request->query('searchText', '');
        $perPage = $request->query('perPage', 15); // Default to 15
        $page = $request->query('page', 1); // Default to 1

        // Step 1: Get the payment cycle (main model)
        $paymentCycle = PayrollPaymentCycle::find($id);

        if (!$paymentCycle) {
            throw new \Exception("Payment Details cycle not found.");
        }

        // Step 2: Build query for related paymentCycleDetails
        $detailsQuery = $paymentCycle->PaymentCycleDetails()
            ->with([
                'beneficiaries',
                'paymentStatus',
                'payroll' => fn($q) => $q->with([
                    'installment',
                    'program',
                    'FinancialYear',
                    'office' => fn($q) => $q->with('assignLocation')
                    ]) 
            ]);

        // Apply filters
        $filters = [
            'program_id' => $request->program_id,
            'beneficiary_id' => $request->beneficiary_id,
            'financial_year_id' => $request->financial_year_id,
            'status_id' => $request->status_id
        ];

        foreach ($filters as $field => $value) {
            if ($value) {
                $detailsQuery->where($field, $value);
            }
        }

        // Filter by office ID
        if ($request->office_id) {
            $detailsQuery->whereHas('payroll.office', function ($q) use ($request) {
                $q->where('id', $request->office_id);
            });
        }

        // Location-based filters
        if ($request->division_id) {
            $detailsQuery->whereHas('beneficiaries', function ($q) use ($request) {
                $q->where('permanent_division_id', $request->division_id);
            });
        }
        if ($request->district_id) {
            $detailsQuery->whereHas('beneficiaries', function ($q) use ($request) {
                $q->where('permanent_district_id', $request->district_id);
            });
        }
        if ($request->upazila_id) {
            $detailsQuery->whereHas('beneficiaries', function ($q) use ($request) {
                $q->where('permanent_upazila_id', $request->upazila_id);
            });
        }
        if ($request->district_pourashava_id) {
            $detailsQuery->whereHas('beneficiaries', function ($q) use ($request) {
                $q->where('permanent_district_pourashava_id', $request->district_pourashava_id);
            });
        }

        // Search filter
        if (!empty($searchText)) {
            $detailsQuery->where(function ($query) use ($searchText) {
                $query->where('payroll_payment_cycle_id', 'LIKE', "%$searchText%")
                    ->orWhere('amount', 'LIKE', "%$searchText%")
                    ->orWhere('charge', 'LIKE', "%$searchText%")
                    ->orWhere('total_amount', 'LIKE', "%$searchText%");

                $query->orWhereHas('beneficiaries', function ($q) use ($searchText) {
                    $q->where('name_en', 'LIKE', "%$searchText%")
                        ->orWhere('name_bn', 'LIKE', "%$searchText%")
                        ->orWhere('beneficiary_id', 'LIKE', "%$searchText%");
                });

                $query->orWhereHas('payroll.program', function ($q) use ($searchText) {
                    $q->where('name_en', 'LIKE', "%$searchText%");
                });

                $query->orWhereHas('payroll.office.assignLocation', function ($q) use ($searchText) {
                    $q->where('name_bn', 'LIKE', "%$searchText%")
                    ->orWhere('name_en', 'LIKE', "%$searchText%");
                });

                $query->orWhereHas('payroll.installment', function ($q) use ($searchText) {
                    $q->where('installment_name', 'LIKE', "%$searchText%")
                    ->orWhere('installment_name_bn', 'LIKE', "%$searchText%");
                });
            });
        }

        if($request->download == 'yes'){
            return Excel::download(new PaymentCycleBeneficiaryExport($detailsQuery), 'payment-cycle-beneficiary.xls');
        }

        // Step 3: Paginate the filtered results
        $cycleDetails = $detailsQuery->paginate($perPage, ['*'], 'page', $page);

        // Step 4: Return response
        return response()->json([
            'paymentCycle' => $paymentCycle,
            'cycleDetails' => $cycleDetails,
        ]);
    }


    public function getPayrollWiseBeneficiary(Request $request, $cycle_id, $payroll_id)
    {
        return $this->getPaymentCycleView($request, $cycle_id, $payroll_id);
    }


    public function getPaymentCycleById($id)
    {
        // $paymentCycle = EmergencyPayrollPaymentCycle::with(['paymentCycleDetails.payroll.installment','paymentCycleDetails.payroll.program','PaymentCycleDetails.payroll.office.assignLocation'])->find($id);
        $paymentCycle = PayrollPaymentCycle::with([
            'PaymentCycleDetails' => function ($query) {
                $query->where('status', '!=', 'Rejected'); // Check for is_rejected = 0
                $query->with([
                    'payroll' => function ($query) {
                        // $query->where('is_approved', 0); // Check for is_approved = 0
                        $query->with([
                            'installment', // Load installments through payroll
                            'program', // Load program through payroll
                            'office.assignLocation', // Load assigned location through office
                        ]);
                    },
                ]);
            },
        ])->find($id);

        if (!$paymentCycle) {
            throw new \Exception("Payment cycle not found.");
        }
        return $paymentCycle;
    }

    public function getPaymentCycleRejectById($id)
    {
        // return $id;
        $paymentCycle = PayrollPaymentCycle::where('id', $id)
            ->with('PaymentCycleDetails.payroll', 'PaymentCycleDetails.payroll.program', 'PaymentCycleDetails.payroll.FinancialYear', 'PaymentCycleDetails.payroll.installment')
            ->first();
        // return  $paymentCycle;

        if (!$paymentCycle) {
            return response()->json(['message' => 'Payment cycle not found.'], 404);
        }

// Filter details where status_id != 3 and then group by payroll_id
$groupedByPayroll = $paymentCycle->PaymentCycleDetails
    // ->filter(function ($detail) {
    //     return $detail->status_id != 3 && $detail->status_id != 10;
    // })
    ->groupBy(function ($detail) {
        return $detail->payroll->id;
    });


// Transform the data
        $result = $groupedByPayroll->map(function ($details, $payrollId) use ($paymentCycle){
            return [
                'payroll_id' => $payrollId,
                'payroll' => $details->first()->payroll, // Assuming all details have the same payroll
                'financial_year' => $details->first()->payroll->FinancialYear->name,
                'program' => $details->first()->payroll->program->name,
                'cycle_ayroll_beneficiary_count' => $details->count(),
                'totalCharge' => $details->sum('charge'),
                'totalAmount' => $details->sum('amount'),
                'subTotalAmount' => $details->sum('total_amount'), // Count of cycle details for this payroll_id
                'status' => $paymentCycle->status,
            ];
        })->values()->toArray(); // Use values() to reset keys if you want a list rather than a keyed array

        return response()->json($result);

    }
    public function payrollDelete($id, $cycle_id)
    {
        // return $cycle_id;

        $paymentCycle = PayrollPaymentCycle::where('id', $cycle_id)->first();
        if (!$paymentCycle) {
            return response()->json(['message' => 'Payment cycle not found.'], 404);
        }

// Group by payroll
        $groupedByPayroll = $paymentCycle->PaymentCycleDetails
            ->filter(function ($detail) {
                return $detail->status_id != 3;
            })
            ->groupBy(function ($detail) {
                return $detail->payroll->id;
            });
        $rejectedCount = $groupedByPayroll->count();

        // Get the emergency payrolls by ID
        // $beforeUpdate = Payroll::where('id', $id)->first();
        $payroll = Payroll::where('id', $id)->first();

        $payrollDetails = PayrollDetail::where('payroll_id', $payroll->id)->get();

        $payrollPaymentCycleDetails = PayrollPaymentCycleDetail::with('payrollDetail')->where('payroll_id', $payroll->id)
            ->where('payroll_payment_cycle_id', $cycle_id)
            ->get();

// Update the status and save for EmergencyPayrollPaymentCycleDetails
        $payrollPaymentCycleDetails->each(function ($cycleDetail) {
            $cycleDetail->status_id = 3; // Set your desired status
            $cycleDetail->save();
            // Find the related PayrollDetail using the foreign key
            $payrollDetail = PayrollDetail::find($cycleDetail->payroll_detail_id);
            if ($payrollDetail) {
                $payrollDetail->status_id = 3; // Set the status in PayrollDetail
                $payrollDetail->save();
            }

        });

        // Update the status and save for PayrollDetails
// Update the status and save for EmergencyPayrollPaymentCycle
        if ($rejectedCount == 1) {
            PayrollPaymentCycle::where('id', $cycle_id)->update(['status' => 'Rejected']);

        }

        $rejectedById = Auth::user()->id;

// // Get the current timestamp
        $rejectedAt = Carbon::now();
        $payroll->is_rejected = 1;
        $payroll->rejected_by_id = $rejectedById;
        $payroll->rejected_at = $rejectedAt;
        $payroll->save();

        foreach ($payrollPaymentCycleDetails as $detail) {
            $emergencyReconciliationLog = new BeneficiaryPayrollPaymentStatusLog();
            $emergencyReconciliationLog->beneficiary_id = $detail->beneficiary_id;
            $emergencyReconciliationLog->payroll_details_id = $detail->payroll->id;
            $emergencyReconciliationLog->payment_cycle_details_id = $detail->id;
            $emergencyReconciliationLog->status_id = 3;
            $emergencyReconciliationLog->save();
        }

        // Return the deleted records
        return response()->json([
            'message' => 'Payroll Deleted Successfully.',
            'success' => true,
        ], 200);

    }

    public function payrollBeneficiaryDelete($id, $cycle_id)
    {
        $beforeUpdate = PayrollDetail::where('beneficiary_id', $id)->first();
        $emergencyPayrollDetails = PayrollDetail::where('beneficiary_id', $id)->first();
        if (isset($emergencyPayrollDetails)) {
            $emergencyPayrollDetails->status_id =9;
            $emergencyPayrollDetails->update();

        } else {
            return response()->json([
                'message' => 'Beneficiary Not Found !!.',
                'success' => false,
            ], 400);

        }

        $paymentCycleDetails = PayrollPaymentCycleDetail::where('beneficiary_id', $id)
            ->where('payroll_payment_cycle_id', $cycle_id)
            ->first();

        if (isset($paymentCycleDetails)) {
            $paymentCycleDetails->status_id = 3;
            $paymentCycleDetails->update();

            $cycleLog = new BeneficiaryPayrollPaymentStatusLog();
            $cycleLog->beneficiary_id = $paymentCycleDetails->beneficiary_id;
            $cycleLog->payroll_details_id = $paymentCycleDetails->payroll_detail_id;
            $cycleLog->payment_cycle_details_id = $paymentCycleDetails->id;
            $cycleLog->status_id = 3;
            $cycleLog->created_by = auth::user()->id;
            $cycleLog->save();
            $this->beneficiaryDelete($id);
        } else {
            return response()->json([
                'message' => 'Beneficiary Not Found !!.',
                'success' => false,
            ], 400);

        }

        // Helper::activityLogUpdate($emergencyPayrollPaymentCycleDetails, $beforeUpdate, 'Payment Cycle', 'Payment Cycle Rejected !');
        // Return the deleted records
        return response()->json([
            'message' => 'Beneficiary Deleted Successfully.',
            'success' => true,
        ], 200);

    }


    public function paymentCycleDelete($id, $payroll_id)
    {
        // Retrieve the payment cycle before deletion
        $beforeUpdate = PayrollPaymentCycle::where('id', $id)->first();
        $payrollPaymentCycle = PayrollPaymentCycle::where('id', $id)->first();

        // Retrieve the payment cycle details before deletion
        $payrollPaymentCycleDetails = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)->get();

        if ($payrollPaymentCycle) {
            // Delete the payment cycle
            $payrollPaymentCycle->status = 'Rejected';
            $payrollPaymentCycle->update();
        }
        if ($payrollPaymentCycleDetails) {
            // Update the status_id of each detail
            $payrollPaymentCycleDetails->each(function ($detail) {
                // Retrieve the PayrollDetail associated with the current detail
                $payroll_detail = PayrollDetail::where('id', $detail->payroll_detail_id)->first();

                // Check if the PayrollDetail exists before updating
                if ($payroll_detail) {
                    $payroll_detail->status_id = 9;
                    $payroll_detail->save();
                }

                // Update the status_id of the current detail
                $detail->status_id = 9; // Set your desired status
                $detail->save(); // Use save() instead of update() for individual model updates
            });
        }

        if (!empty($payrollPaymentCycleDetails)) {
            $payrollPaymentCycleDetails->each(function ($payrollPaymentCycleDetails) {
                $cycleLog = new BeneficiaryPayrollPaymentStatusLog();
                $cycleLog->beneficiary_id = $payrollPaymentCycleDetails->beneficiary_id;
                $cycleLog->payroll_details_id = $payrollPaymentCycleDetails->payroll_detail_id;
                $cycleLog->payment_cycle_details_id = $payrollPaymentCycleDetails->id;
                $cycleLog->status_id = 9;
                $cycleLog->created_by = Auth::user()->id;
                $cycleLog->save();

            });

        }

        // Log the activity
        // Helper::activityLogUpdate($payrollPaymentCycleDetails, $beforeUpdate, 'Payment Cycle', 'Payment Cycle Delete !');

        // Return the deleted records
        return response()->json([
            'message' => 'Payment Cycle Deleted Successfully.',
            'success' => true,
        ], 200);
    }

    public function beneficiaryDelete($beneficiary_id)
    {
        try {
            $p_detail = PayrollPaymentCycleDetail::where('beneficiary_id', $beneficiary_id)->first();
            if ($p_detail) {
                $payroll = PayrollPaymentCycle::where('id', $p_detail->payroll_payment_cycle_id)->first();
                $payroll->total_beneficiaries -= 1;
                $payroll->total_charge -= $p_detail->charge;
                $payroll->sub_total_amount -= $p_detail->amount;
                $payroll->total_amount -= $p_detail->total_amount;
                $payroll->save();
                BeneficiaryPayrollPaymentStatusLog::where('beneficiary_id', $beneficiary_id)->update(['status_id' => 3]);

            }
            return $p_detail;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }

public function rejectMultipleBeneficiaries(Request $request, $cycle_id)
{
    // Extract the beneficiary IDs from the request
    $beneficiaryIds = $request->input('beneficiary_ids');

    foreach ($beneficiaryIds as $id) {
        // Fetch the before update payroll details for logging purposes
        $beforeUpdate = PayrollDetail::where('beneficiary_id', $id)->first();

        // Fetch emergency payroll details for the beneficiary
        $emergencyPayrollDetails = PayrollDetail::where('beneficiary_id', $id)->first();

        // Check if emergency payroll details exist
        if (isset($emergencyPayrollDetails)) {
            // Set the status to 9 and update the record
            $emergencyPayrollDetails->status_id = 9;
            $emergencyPayrollDetails->update();
        } else {
            // If the beneficiary is not found, return a response
            return response()->json([
                'message' => 'Beneficiary Not Found !!',
                'success' => false,
            ], 400);
        }

        // Fetch the payment cycle details for the beneficiary and cycle
        $paymentCycleDetails = PayrollPaymentCycleDetail::where('beneficiary_id', $id)
            ->where('payroll_payment_cycle_id', $cycle_id)
            ->first();

        // Check if the payment cycle details exist
        if (isset($paymentCycleDetails)) {
            // Set the status to 3 (rejected) and update the record
            $paymentCycleDetails->status_id = 3;
            $paymentCycleDetails->update();

            // Log the status change in the BeneficiaryPayrollPaymentStatusLog table
            $cycleLog = new BeneficiaryPayrollPaymentStatusLog();
            $cycleLog->beneficiary_id = $paymentCycleDetails->beneficiary_id;
            $cycleLog->payroll_details_id = $paymentCycleDetails->payroll_detail_id;
            $cycleLog->payment_cycle_details_id = $paymentCycleDetails->id;
            $cycleLog->status_id = 3;
            $cycleLog->created_by = auth()->user()->id;
            $cycleLog->save();

            // Call the beneficiaryDelete method to delete the beneficiary from payroll
            $this->beneficiaryDelete($id);
        } else {
            // If payment cycle details are not found, return a response
            return response()->json([
                'message' => 'Beneficiary Not Found !!',
                'success' => false,
            ], 400);
        }

        // Optionally log activity, if needed
        // Helper::activityLogUpdate($emergencyPayrollPaymentCycleDetails, $beforeUpdate, 'Payment Cycle', 'Payment Cycle Rejected!');
    }

    // Return a success response after processing all beneficiaries
    return response()->json([
        'message' => 'Beneficiaries Deleted Successfully.',
        'success' => true,
    ], 200);
}

}
