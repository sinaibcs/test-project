<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\CommonResource;
use App\Http\Traits\MessageTrait;
use App\Models\AllowanceProgram;
use App\Models\EmergencyBeneficiaryPayrollPaymentStatusLog;
use App\Models\EmergencyPayroll;
use App\Models\EmergencyPayrollDetails;
use App\Models\EmergencyPayrollPaymentCycle;
use App\Models\EmergencyPayrollPaymentCycleDetails;
use App\Models\PayrollInstallmentSchedule;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class EmergencyPaymentCycleController extends Controller
{
    use MessageTrait;
    const BASE_URL = 'http: //mis.bhata.gov.bd/api/mfssss';
    const PUSH_API = '/push-payroll-summary';

    public function getPaymentCycle(Request $request)
    {
        $startDate = $request->query('start_date');
        $endDate = $request->query('end_date');
        $searchText = $request->query('searchText');
        $installment_no = $request->query('installment_no');
        $program_id = $request->query('program_id');
        $financial_year = $request->query('financial_year');

        // return $installment_no;
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $paymentCycle = EmergencyPayrollPaymentCycle
            ::with(['CycleDetails.payroll', 'installment']);

        if ($searchText) {
            $paymentCycle->orwhere('cycle_id', 'LIKE', "%$searchText%");
            $paymentCycle->orwhere('name_en', 'LIKE', "%$searchText%");
            $paymentCycle->orwhere('name_bn', 'LIKE', "%$searchText%");
            $paymentCycle->orwhere('total_beneficiaries', 'LIKE', "%$searchText%");
            $paymentCycle->orwhere('sub_total_amount', 'LIKE', "%$searchText%");
            $paymentCycle->orwhere('total_charge', 'LIKE', "%$searchText%");
            $paymentCycle->orwhere('total_amount', 'LIKE', "%$searchText%");

        }

        if ($installment_no) {
            $paymentCycle->where('installment_schedule_id', $installment_no);

        }
        if ($startDate && $endDate) {
            $paymentCycle->whereBetween('created_at', [$startDate, $endDate]); // return  $query->get();
        }

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

    public function pushPayrollSummary($id)
    {
        try {
            // Start the transaction
            return DB::transaction(function () use ($id) {
                // Find the payment cycle record
                $beforeUpdate = EmergencyPayrollPaymentCycle::find($id);
                $paymentCycle = EmergencyPayrollPaymentCycle::find($id);
                // return $paymentCycle;
                if (!$paymentCycle) {
                    throw new \Exception("Payment cycle not found.");
                } 

                // push data to the payment cycle
                // return  $id;
                $paymentCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_cycle_id', $id)->where('status_id', 4)->get();
                // return   $paymentCycleDetails;
                if ($paymentCycleDetails->isEmpty()) {
                    return response()->json([
                        'message' => 'No records found.',
                        'success' => false,
                    ], 404);
                } else {
                    $paymentCycle->status = 'Initiated';
                    $paymentCycle->update();
                }

                foreach ($paymentCycleDetails as $record) {
                    $record->status_id = 5;
                    $record->update();
                    $cycleLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
                    $cycleLog->emergency_beneficiary_id = $record->emergency_beneficiary_id;
                    $cycleLog->emergency_payroll_details_id  = $record->emergency_payroll_detail_id;
                    $cycleLog->emergency_payment_cycle_details_id = $record->id;
                    $cycleLog->status_id = 5;
                    $cycleLog->created_by = auth::user()->id;
                    $cycleLog->save();

                }

                // $formattedDetails = $paymentCycleDetails->map(function ($detail) {

                //     return [
                //         'beneficiary_id' => $detail->beneficiary_id,
                //         'cycle_id' => $detail->payroll_payment_cycle_id,
                //         'amount' => $detail->amount,
                //         // 'reason' => $detail->reason,
                //         // 'summary_time' => $detail->summary_time,
                //         // 'status' => $detail->status,
                //     ];
                // });

                // $response = Http::contentType('application/json')
                //   ->post(self::BASE_URL . self::PUSH_API, $formattedDetails);

                //  if ($response->successful()) {
                //      return $response->json();
                //      } else {
                //        throw new \Exception('Request failed with status ' . $response->status());
                //      }
                Helper::activityLogUpdate($paymentCycle, $beforeUpdate, 'Payroll Payment Cycle', 'Payroll Payment Cycle Send !');

                return $paymentCycleDetails;

            });
        } catch (\Throwable $e) {
            // Handle the exception (rollback is automatically handled by DB::transaction)
            // Log the error or handle it accordingly
            \Log::error("Transaction failed: " . $e->getMessage());
            throw $e; // Re-throw the exception for higher-level handling
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
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage'); // default to 15 if not provided
        $page = $request->query('page'); // default to 1 if not provided

        $paymentCycleQuery = EmergencyPayrollPaymentCycle::where('id', $id)
            ->with([
                'cycleDetails' => function ($query) use ($searchText) {
                    //  $query->with([
                    //     'beneficiaries' => function ($query) use ($searchText) {
                    //         $query->where(function ($query) use ($searchText) {
                    //             $query->orWhere('name_en', 'LIKE', "%$searchText%")
                    //                 ->orWhere('name_bn', 'LIKE', "%$searchText%");
                    //         });
                    //     },
                    // ]);
                    $query->with('beneficiaries')
                        ->with([
                            'payroll' => function ($query) use ($searchText) {
                                $query->with([
                                    'installment', // Load installments through payroll
                                    'beneficiaries', // Load beneficiaries through payroll
                                    'program', // Load program through payroll
                                    'office.assignLocation', // Load assigned location through office
                                ]);

//                             if ($searchText) {
//     $query->where(function ($query) use ($searchText) {
//          return $query;
//         $query->orWhere('cycle_id', 'LIKE', "%$searchText%")
//             ->orWhere('name_en', 'LIKE', "%$searchText%")
//             ->orWhere('name_bn', 'LIKE', "%$searchText%")
//             ->orWhere('sub_total_amount', 'LIKE', "%$searchText%")
//             ->orWhere('total_charge', 'LIKE', "%$searchText%")
//             ->orWhere('total_amount', 'LIKE', "%$searchText%");
//     });
// }

                            },
                        ]);

                },
            ]);

        $paymentCycle = $paymentCycleQuery->first();

        if (!$paymentCycle) {
            throw new \Exception("Payment Details cycle not found.");
        }

        // Get cycle details and paginate them
        $cycleDetails = $paymentCycle->cycleDetails()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'paymentCycle' => $paymentCycle,
            'cycleDetails' => $cycleDetails,
        ]);
    }
    public function getPaymentCycleById($id)
    {

        // $paymentCycle = EmergencyPayrollPaymentCycle::with(['paymentCycleDetails.payroll.installment','paymentCycleDetails.payroll.program','PaymentCycleDetails.payroll.office.assignLocation'])->find($id);
        $paymentCycle = EmergencyPayrollPaymentCycle::with([
            'paymentCycleDetails' => function ($query) {
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
//     public function getPaymentCycleRejectById($id)
//     {
//         $paymentCycle = EmergencyPayrollPaymentCycle::where('id', $id)
//             ->with('cycleDetails.payroll', 'cycleDetails.payroll.program', 'cycleDetails.payroll.FinancialYear', 'cycleDetails.payroll.installment')
//             ->first();

//         if (!$paymentCycle) {
//             return response()->json(['message' => 'Payment cycle not found.'], 404);
//         }

// // Group by payroll
//         $groupedByPayroll = $paymentCycle->cycleDetails->groupBy(function ($detail) {
//             return $detail->payroll->id;
//         });

// // Transform the data
//         $result = $groupedByPayroll->map(function ($details, $payrollId) {
//             return [
//                 'payroll_id' => $payrollId,
//                 'payroll' => $details->first()->payroll, // Assuming all details have the same payroll name
//                 'financial_year' => $details->first()->payroll->FinancialYear->name,
//                 'program' => $details->first()->payroll->program->name,

//             ];
//         })->values()->toArray(); // Use values() to reset keys if you want a list rather than a keyed array

//         return response()->json($result);

//     }
    public function getPaymentCycleRejectById($id)
    {
        // return $id;
        $paymentCycle = EmergencyPayrollPaymentCycle::where('id', $id)
            ->with('cycleDetails.payroll', 'cycleDetails.payroll.program', 'cycleDetails.payroll.FinancialYear', 'cycleDetails.payroll.installment')
            ->first();
        // return  $paymentCycle;

        if (!$paymentCycle) {
            return response()->json(['message' => 'Payment cycle not found.'], 404);
        }

// Group by payroll_id
        $groupedByPayroll = $paymentCycle->cycleDetails->groupBy(function ($detail) {
            return $detail->payroll->id;
        });

// Transform the data
        $result = $groupedByPayroll->map(function ($details, $payrollId) {
            return [
                'payroll_id' => $payrollId,
                'payroll' => $details->first()->payroll, // Assuming all details have the same payroll
                'financial_year' => $details->first()->payroll->FinancialYear->name,
                'program' => $details->first()->payroll->program->name,
                'cycle_ayroll_beneficiary_count' => $details->count(),
                'totalCharge' => $details->sum('charge'),
                'totalAmount' => $details->sum('amount'),
                'subTotalAmount' => $details->sum('total_amount'), // Count of cycle details for this payroll_id
            ];
        })->values()->toArray(); // Use values() to reset keys if you want a list rather than a keyed array

        return response()->json($result);

    }
    public function getPaymentCyclePayrolls(Request $request, $id)
    {
        $perPage = $request->query('perPage', 10); // Default to 10 items per page if not specified
        $page = $request->query('page', 1); // Default to page 1 if not specified

        $paymentCycle = EmergencyPayrollPaymentCycle::where('id', $id)
            ->with('cycleDetails.payroll', 'cycleDetails.payroll.program', 'cycleDetails.payroll.FinancialYear', 'cycleDetails.payroll.installment')
            ->first();

        if (!$paymentCycle) {
            return response()->json(['message' => 'Payment cycle not found.'], 404);
        }

// Group by payroll_id
        $groupedByPayroll = $paymentCycle->cycleDetails->groupBy(function ($detail) {
            return $detail->payroll->id;
        });

// Transform the data
        $result = $groupedByPayroll->map(function ($details, $payrollId) {
            return [
                'payroll_id' => $payrollId,
                'payroll' => $details->first()->payroll, // Assuming all details have the same payroll
                'financial_year' => $details->first()->payroll->FinancialYear->name,
                'program' => $details->first()->payroll->program->name,
                'cycle_payroll_beneficiary_count' => $details->count(),
                'totalCharge' => $details->sum('charge'),
                'totalAmount' => $details->sum('amount'),
                'subTotalAmount' => $details->sum('total_amount'), // Count of cycle details for this payroll_id
            ];
        })->values(); // Remove ->toArray() as it's not needed here

// Paginate the transformed data using LengthAwarePaginator
        $currentPageItems = $result->forPage($page, $perPage);

        $paginator = new LengthAwarePaginator(
            $currentPageItems,
            $result->count(), // Total count of items
            $perPage,
            $page,
            ['path' => LengthAwarePaginator::resolveCurrentPath()]
        );

// Return the paginated result as a collection of resources
        return CommonResource::collection($paginator);

    }

    public function emergencyPayrollDelete($id, $cycle_id)
    {

        $paymentCycle = EmergencyPayrollPaymentCycle::where('id', $cycle_id)
            ->with('cycleDetails.payroll', 'cycleDetails.payroll.program', 'cycleDetails.payroll.FinancialYear', 'cycleDetails.payroll.installment')
            ->first();

        if (!$paymentCycle) {
            return response()->json(['message' => 'Payment cycle not found.'], 404);
        }

// Group by payroll
        $groupedByPayroll = $paymentCycle->cycleDetails
            ->filter(function ($detail) {
                return $detail->status_id != 3;
            })
            ->groupBy(function ($detail) {
                return $detail->payroll->id;
            });
        $rejectedCount = $groupedByPayroll->count();

        // return $cycle_id;
        // Get the emergency payrolls by ID
        $beforeUpdate = EmergencyPayroll::where('id', $id)->get();
        $emergencyPayroll = EmergencyPayroll::where('id', $id)->first();
        // return $emergencyPayroll;
        $emergencyPayrollDetails = EmergencyPayrollDetails::where('emergency_payroll_id', $emergencyPayroll->id)->get();
        // return  $emergencyPayrollDetails;
        $emergencyPayrollPaymentCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_payroll_id', $emergencyPayroll->id)->get();
        // $emergencyPayrollPaymentCycle = EmergencyPayrollPaymentCycle::where('emergency_payroll_id', $emergencyPayroll->id)->get();

        //    return $emergencyPayrollDetails->count();
        // Delete the associated records
        // Update the status and save for EmergencyPayrollDetails
        $emergencyPayrollDetails->each(function ($detail) {
            $detail->status_id = 3; // Set your desired status
            $detail->save();
        });

// Update the status and save for EmergencyPayrollPaymentCycleDetails
        $emergencyPayrollPaymentCycleDetails->each(function ($cycleDetail) {
            $cycleDetail->status_id = 3; // Set your desired status
            $cycleDetail->save();
        });

        //    return $rejectedCount;
// Update the status and save for EmergencyPayrollPaymentCycle
        if ($rejectedCount == 1) {
            EmergencyPayrollPaymentCycle::where('id', $cycle_id)->update(['status' => 'Rejected']);

            // return   $emergencyPayrollPaymentCycle;
            //     $emergencyPayrollPaymentCycle->each(function ($cycle) {
            //     $cycle->status = 'Rejected'; // Set your desired status
            //     $cycle->save();
            // });
        }

        // Helper::activityLogUpdate($emergencyPayrollPaymentCycleDetails, $beforeUpdate, 'Emergency Payment Cycle', 'Emergency Payment Cycle Rejected !');

// Get the ID of the currently authenticated user
        $rejectedById = Auth::user()->id;

// // Get the current timestamp
        $rejectedAt = Carbon::now();
        $emergencyPayroll->is_rejected = 1;
        $emergencyPayroll->rejected_by_id = $rejectedById;
        $emergencyPayroll->rejected_at = $rejectedAt;
        $emergencyPayroll->save();

        $emergencyCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_payroll_id', $emergencyPayroll->id)->get();
        foreach ($emergencyCycleDetails as $emergencyCycleDetail) {
            $emergencyReconciliationLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
            $emergencyReconciliationLog->emergency_beneficiary_id = $emergencyCycleDetail->emergency_beneficiary_id;
            $emergencyReconciliationLog->emergency_payroll_details_id = $emergencyCycleDetail->EmergencyPayroll->id;
            $emergencyReconciliationLog->emergency_payment_cycle_details_id = $emergencyCycleDetail->id;
            $emergencyReconciliationLog->status_id = 3;
            $emergencyReconciliationLog->save();
        }

        // Return the deleted records
        return response()->json([
            'message' => 'Emergency Payroll Deleted Successfully.',
            'success' => true,
        ], 200);

    }
    // public function emergencyBeneficiaryDelete($id,$cycleId)
    // {
    // //    return $id;
    //     $beforeUpdate = EmergencyPayrollDetails::where('emergency_beneficiary_id', $id)->first();
    //     $emergencyPayrollDetails = EmergencyPayrollDetails::where('emergency_beneficiary_id', $id)->first();
    //     if (isset($emergencyPayrollDetail)) {
    //         $emergencyPayrollDetails->status_id = 3;
    //         $emergencyPayrollDetails->update();
    //     } else {
    //         return response()->json([
    //             'message' => 'Emergency beneficiary Not Found !!.',
    //             'success' => false,
    //         ], 200);

    //     }

    //     $emergencyPayrollPaymentCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_beneficiary_id', $id)->first();
    //     if (isset($emergencyPayrollPaymentCycleDetails)) {
    //         $emergencyPayrollPaymentCycleDetails->status_id = 3;
    //         $emergencyPayrollPaymentCycleDetails->update();
    //     } else {
    //         return response()->json([
    //             'message' => 'Emergency beneficiary Not Found !!.',
    //             'success' => false,
    //         ], 200);

    //     }
    //     Helper::activityLogUpdate($emergencyPayrollPaymentCycleDetails, $beforeUpdate, 'Emergency Payment Cycle', 'Emergency Payment Cycle Rejected !');
    //     // Return the deleted records
    //     return response()->json([
    //         'message' => 'Emergency Payroll Deleted Successfully.',
    //         'success' => true,
    //     ], 200);

    // }

      public function emergencyBeneficiaryDelete($id, $cycle_id)
    {
        $emergencyPayrollDetails = EmergencyPayrollDetails::where('emergency_beneficiary_id', $id)->first();
        // EmergencyPayrollPaymentCycleDetails::where('emergency_payroll_detail_id', $emergencyPayrollDetails->id)->update(['status_id' => 9]);
        if (isset($emergencyPayrollDetails)) {
            $emergencyPayrollDetails->status_id = 9;
            $emergencyPayrollDetails->update();

        } else {
            return response()->json([
                'message' => 'Beneficiary Not Found !!.',
                'success' => false,
            ], 400);

        }

        $paymentCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_beneficiary_id', $id)
            ->where('emergency_cycle_id', $cycle_id)
            ->first();
        // return  $emergencyPayrollPaymentCycleDetails;
        if (isset($paymentCycleDetails)) {
            $paymentCycleDetails->status_id = 9;
            $paymentCycleDetails->update();

            $cycleLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
            $cycleLog->emergency_beneficiary_id = $paymentCycleDetails->emergency_beneficiary_id;
            $cycleLog->emergency_payroll_details_id  = $paymentCycleDetails->emergency_payroll_detail_id;
            $cycleLog->emergency_payment_cycle_details_id = $paymentCycleDetails->id;
            $cycleLog->status_id = 9;
            $cycleLog->created_by = auth::user()->id;
            $cycleLog->save();
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

 public function paymentCycleDelete($id)
    {
        // Retrieve the payment cycle before deletion
             
        $beforeUpdate = EmergencyPayrollPaymentCycle::where('id', $id)->first();
        $payrollPaymentCycle = EmergencyPayrollPaymentCycle::where('id', $id)->first();

        // Retrieve the payment cycle details before deletion
        $payrollPaymentCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_cycle_id', $id)->get();

        if ($payrollPaymentCycle) {
            // Delete the payment cycle
            $payrollPaymentCycle->status = 'Rejected';
            $payrollPaymentCycle->update();
        }

        if ($payrollPaymentCycleDetails) {
            // Update the status_id of each detail
            $payrollPaymentCycleDetails->each(function ($detail) {
                $payroll_detail = EmergencyPayrollDetails::where('id', $detail->emergency_payroll_detail_id)->first();

                // Check if the PayrollDetail exists before updating
                if ($payroll_detail) {
                    $payroll_detail->status_id = 9;
                    $payroll_detail->save();
                }
                $detail->status_id = 9; // Set your desired status
                $detail->save(); // Use save() instead of update() for individual model updates
            });
        }

        if (!empty($payrollPaymentCycleDetails)) {
            $payrollPaymentCycleDetails->each(function ($payrollPaymentCycleDetails) {
                $cycleLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
                $cycleLog->emergency_beneficiary_id  = $payrollPaymentCycleDetails->emergency_beneficiary_id;
                $cycleLog->emergency_payroll_details_id = $payrollPaymentCycleDetails->emergency_payroll_detail_id;
                $cycleLog->emergency_payment_cycle_details_id  = $payrollPaymentCycleDetails->id;
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
       public function getPayrollWiseBeneficiary(Request $request, $cycle_id,$payroll_id)
    {
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage'); // default to 15 if not provided
        $page = $request->query('page'); // default to 1 if not provided

        $paymentCycleQuery = EmergencyPayrollPaymentCycle::where('id', $cycle_id)
            ->with([
                'PaymentCycleDetails' => function ($query) use ($searchText,$cycle_id,$payroll_id) {
                      $query->where('emergency_cycle_id', $cycle_id);
                      $query->where('emergency_payroll_id', $payroll_id);
                    $query->with('beneficiaries')
                        ->with([
                            'payroll' => function ($query) use ($searchText) {
                                $query->with([
                                    'installment', // Load installments through payroll
                                    'program', // Load program through payroll
                                    'office.assignLocation', // Load assigned location through office
                                ]);

//                             if ($searchText) {
//     $query->where(function ($query) use ($searchText) {
//          return $query;
//         $query->orWhere('cycle_id', 'LIKE', "%$searchText%")
//             ->orWhere('name_en', 'LIKE', "%$searchText%")
//             ->orWhere('name_bn', 'LIKE', "%$searchText%")
//             ->orWhere('sub_total_amount', 'LIKE', "%$searchText%")
//             ->orWhere('total_charge', 'LIKE', "%$searchText%")
//             ->orWhere('total_amount', 'LIKE', "%$searchText%");
//     });
// }

                            },
                        ]);

                },
            ]);

        $paymentCycle = $paymentCycleQuery->first();

        if (!$paymentCycle) {
            throw new \Exception("Payment Details cycle not found.");
        }

        // Get cycle details and paginate them
        $cycleDetails = $paymentCycle->PaymentCycleDetails()->paginate($perPage, ['*'], 'page', $page);

        return response()->json([
            'paymentCycle' => $paymentCycle,
            'cycleDetails' => $cycleDetails,
        ]);
    }

}