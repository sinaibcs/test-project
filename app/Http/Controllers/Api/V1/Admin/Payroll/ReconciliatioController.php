<?php

namespace App\Http\Controllers\Api\V1\Admin\Payroll;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Application\CommitteeApplicationService;
use App\Http\Services\Admin\Application\OfficeApplicationService;
use App\Http\Services\Admin\Payment\IbusDataService;
use App\Http\Services\Admin\Payment\IbusPpService;
use App\Http\Services\Admin\User\OfficeHeadService;
use App\Http\Services\Admin\User\UserService;
use App\Http\Services\Auth\AuthService;
use App\Http\Services\Notification\SMSservice;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\RoleTrait;
use App\Models\Beneficiary;
use App\Models\BeneficiaryChangeTracking;
use App\Models\BeneficiaryChangeType;
use App\Models\BeneficiaryPayrollPaymentStatusLog;
use App\Models\EmergencyPayrollPaymentCycle;
use App\Models\PayrollPaymentCycle;
use App\Models\PayrollPaymentCycleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\PayrollDetail;
use Log;


class ReconciliatioController extends Controller
{
    use MessageTrait, RoleTrait;

    private $UserService;

    public function __construct(UserService $UserService, public OfficeHeadService $officeHeadService, public SMSservice $SMSservice, public AuthService $authService)
    {
        $this->UserService = $UserService;
        $this->authService = $authService;
        $this->SMSservice = $SMSservice;
    }

    public function getReconciliation(Request $request)
    {
        // return $request->all();
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $reconciliation = PayrollPaymentCycleDetail::with([
            'beneficiaries.program',
            'beneficiaries.bank',
            'beneficiaries.mfs',
            'beneficiaries.branch',
            'beneficiaries.permanentDistrict',
            'beneficiaries.permanentDivision',
            'beneficiaryChangeTracking',
        ])->whereIn('status_id', [7, 8, 11,10]);
        // Apply filters
       $this->applyFilters($request, $reconciliation);
       // filter end
       $reconciliation->whereHas('beneficiaries', function ($query) use ($searchText, $request) {
            if ($searchText) {
                $query->where(function ($subQuery) use ($searchText) {
                    $subQuery->where('name_en', 'LIKE', "%$searchText%")
                        ->orWhere('name_bn', 'LIKE', "%$searchText%")
                        ->orWhere('mobile', 'LIKE', "%$searchText%")
                        ->orWhere('bank_name', 'LIKE', "%$searchText%")
                        ->orWhere('branch_name', 'LIKE', "%$searchText%")
                        ->orWhere('account_number', 'LIKE', "%$searchText%")
                        ->orWhere('nationality', 'LIKE', "%$searchText%")
                        ->orWhere('verification_number', 'LIKE', "%$searchText%")
                        ->orWhere('current_address', 'LIKE', "%$searchText%")
                        ->orWhere('permanent_address', 'LIKE', "%$searchText%");
                });
            }
            if ($request->verification_number) {
                $query->where('verification_number', $request->verification_number);
            }
            
            if ($request->account_number) {
                $query->where('account_number', $request->account_number);
            }
        });

        // $x = $reconciliation->orderBy('id', 'DESC')->get();
        $x = $reconciliation->orderBy('updated_at', 'ASC')->get();
        $mapped = $x->map(function ($item) {
            return $item->beneficiaries;
        });
        // return  $mapped ;
        $this->applyUserWiseFiltering($mapped);
        $results = $reconciliation->paginate($perPage, ['*'], 'page', $page);
        return $results;
    }

private function applyFilters(Request $request, &$query)
{
    // Program filter
    if ($request->program_id) {
        $query->where('program_id', $request->program_id);
    }

    // Status filter
    if ($request->status_id) {
        $query->where('status_id', $request->status_id);
    }

    // Financial year filter
    if ($request->financial_year_id) {
        $query->where('financial_year_id', $request->financial_year_id);
    }

    // Beneficiary filter
    if ($request->beneficiary_id) {
        $query->where('beneficiary_id', $request->beneficiary_id);
    }

    // Location-based filters
    $this->applyLocationFilters($request, $query);

    // Office filter
    if ($request->office_id) {
        $query->whereHas('payroll.office', function ($q) use ($request) {
            $q->where('id', $request->office_id);
        });
    }
}
private function applyLocationFilters(Request $request, &$query)
{
    // Division filter
    if ($request->division_id) {
        $query->whereHas('beneficiaries', function ($q) use ($request) {
            $q->where('permanent_division_id', $request->division_id);
        });
    }

    // District filter
    if ($request->district_id) {
        $query->whereHas('beneficiaries', function ($q) use ($request) {
            $q->where('permanent_district_id', $request->district_id);
        });
    }

    // Upazila filter
    if ($request->upazila_id) {
        $query->whereHas('beneficiaries', function ($q) use ($request) {
            $q->where('permanent_upazila_id', $request->upazila_id);
        });
    }

    // Pourashava filter
    if ($request->district_pourashava_id) {
        $query->whereHas('beneficiaries', function ($q) use ($request) {
            $q->where('permanent_district_pourashava_id', $request->district_pourashava_id);
        });
    }
}


    public function applyUserWiseFiltering($query)
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');

        if ($user->programs_id) {
            $query->whereIn('program_id', $user->programs_id);
        }

        if ($user->hasRole($this->officeHead) && $user->office_type) {
            return (new OfficeApplicationService())->getApplications($query, $user);
        }

        if ($user->hasRole($this->committee) && $user->committee_type_id) {
            return (new CommitteeApplicationService())->getApplications($query, $user);
        }

        if ($user->hasRole($this->superAdmin)) {
            return (new OfficeApplicationService())->applyLocationTypeFilter(
                query: $query,
                divisionId: request('division_id'),
                districtId: request('district_id')
            );
        }
    }

    public function edit($id)
    {

        $payrollPaymentCycle = PayrollPaymentCycleDetail::findOrFail($id);
        $beneficiary = Beneficiary::where('beneficiary_id', $payrollPaymentCycle->beneficiary_id)->first();

        return $beneficiary;
    }

    public function update(Request $request, $id)
    {
    //    return $request->all();
        $payrollPaymentCycleDetails = PayrollPaymentCycleDetail::where('beneficiary_id', $request->beneficiary_id)->first();
        // return $payrollPaymentCycleDetails->beneficiary_id;

        $beneficiary = Beneficiary::where('beneficiary_id', $payrollPaymentCycleDetails->beneficiary_id)->first();
        //  return $beneficiary;
        $beforeUpdate = $beneficiary->replicate();
        //  return $beforeUpdate;

        try {
            $beneficiary->account_name = $request->account_name;
            $beneficiary->account_number = $request->account_number;
            $beneficiary->account_owner = $request->account_owner;
            $beneficiary->account_type = $request->account_type;
             if($request->account_type==1){
                $beneficiary->bank_id = $request->bank_id;
                $beneficiary->bank_branch_id = $request->bank_branch_id;
                $beneficiary->mfs_id =null;
             }else{
                $beneficiary->mfs_id = $request->mfs_id;
                $beneficiary->bank_id = null;
                $beneficiary->bank_branch_id = null;
            }
            // $beneficiary->bank_id = $request->bank_id;
            // $beneficiary->mfs_id = $request->mfs_id;
            // $beneficiary->bank_branch_id = $request->bank_branch_id;
            $beneficiary->update();
            if (isset($payrollPaymentCycleDetails)) {
                $payrollPaymentCycleDetails->account_name = $request->account_name;
                $payrollPaymentCycleDetails->account_number = $request->account_number;
                $payrollPaymentCycleDetails->account_owner = $request->account_owner;
                $payrollPaymentCycleDetails->account_type = $request->account_type;
                $payrollPaymentCycleDetails->bank_name = $request->bank_id;
                $payrollPaymentCycleDetails->branch_name = $request->bank_branch_id;
                $payrollPaymentCycleDetails->mfs_name = $request->mfs_id;
                $payrollPaymentCycleDetails->status_id = 11;
                $payrollPaymentCycleDetails->update();
            }

            // change log
            $changes = $beneficiary->getChanges();

            // return $changes;
            $accountOldValues = [];
            $accountNewValues = [];
            $accountAttributes = [
                'account_name',
                'account_owner',
                'account_number',
                'financial_year_id',
                'account_type',
                'bank_name',
                'branch_name',
                'monthly_allowance'
            ];
            foreach ($changes as $attribute => $newValue) {

                if (in_array($attribute, $accountAttributes)) {
                    $accountOldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                    $accountNewValues[$attribute] = $newValue;
                }
            }

            if (count($accountNewValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'ACCOUNT_CHANGE')->first();
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $request->beneficiary_id,
                    'change_type_id' => 2,
                    'previous_value' => json_encode($accountOldValues),
                    'change_value' => json_encode($accountNewValues),
                ]);
            }
            // change log end

            Helper::activityLogUpdate($beneficiary, $beforeUpdate, 'Reconciliation Beneficiary Update', 'Payroll Reconciliation Beneficiary Updated !');

            return $beneficiary;
        } catch (\Throwable $th) {
            throw $th;
        }
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
    public function getPaymentCycleRejectById($id)
    {
        // $paymentCycle = EmergencyPayrollPaymentCycle::with(['paymentCycleDetails.payroll.installment','paymentCycleDetails.payroll.program','PaymentCycleDetails.payroll.office.assignLocation'])->find($id);
        $paymentCycle = EmergencyPayrollPaymentCycle::with([
            'paymentCycleDetails' => function ($query) {
                $query->where('status', '!=', 'Rejected');
                $query->with([
                    'payroll' => function ($query) {
                        $query->where('is_approved', 0); // Check for is_approved = 0
                        $query->where('is_rejected', 0); // Check for is_rejected = 0
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
    public function reconciliationUpdate($id, $payroll_payment_cycle_id)
    {
        $beforeUpdate = PayrollPaymentCycleDetail::where('id', $id)
            ->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)
            ->first();

        // Get the emergency payrolls by ID
        $reconciliation = PayrollPaymentCycleDetail::where('id', $id)
            ->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)
            ->first();


        //   return  $emergencyReconciliation;

        // Bulk update EmergencyPayrollPaymentCycleDetails
        if (!empty($reconciliation)) {
            $reconciliation = PayrollPaymentCycleDetail::where('id', $reconciliation->id)
                ->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)
                ->first();
            $reconciliation->status_id = 8;
            $reconciliation->update();
        }

        if (!empty($reconciliation)) {
            $reconciliation = PayrollPaymentCycleDetail::with('payroll')->where('id', $reconciliation->id)->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)->first();
            $reconciliationLog = new BeneficiaryPayrollPaymentStatusLog();
            $reconciliationLog->beneficiary_id = $reconciliation->beneficiary_id;
            $reconciliationLog->payroll_details_id = $reconciliation->payroll_detail_id;
            $reconciliationLog->payment_cycle_details_id = $reconciliation->id;
            $reconciliationLog->status_id = 8;
            $reconciliationLog->created_by = Auth::user()->id;
            $reconciliationLog->save();
        }

        Helper::activityLogUpdate($reconciliation, $beforeUpdate, 'Payroll Payment Reconciliation', 'Payroll Payment Cycle Reconciliation Updated !');

        return response()->json([
            'message' => 'Payroll Payment Reconciliation Successfully Updated.',
            'success' => true,
        ], 200);
    }
    public function reconciliationDelete($id, $payroll_payment_cycle_id)
    {
        // return $id;
        // Get the emergency payrolls by ID
        $beforeUpdate = PayrollPaymentCycleDetail::where('id', $id)->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)->first();
        $reconciliation = PayrollPaymentCycleDetail::where('id', $id)->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)->first();
        // return  $emergencyReconciliation;
        // Bulk update EmergencyPayrollPaymentCycleDetails
        if (!empty($reconciliation)) {
            $reconciliation = PayrollPaymentCycleDetail::where('id', $reconciliation->id)->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)->first();
            $reconciliation->status_id = 10;
            $reconciliation->update();
            $this->beneficiaryDelete($reconciliation->beneficiary_id);

            Helper::activityLogUpdate($reconciliation, $beforeUpdate, 'Emergency Payment Reconciliation', 'Emergency Payment Cycle Reconciliation Updated !');
        }
        if (!empty($reconciliation)) {
            $reconciliation = PayrollPaymentCycleDetail::with('payroll')->where('id', $reconciliation->id)->where('payroll_payment_cycle_id', $payroll_payment_cycle_id)->first();
            $reconciliationLog = new BeneficiaryPayrollPaymentStatusLog();
            $reconciliationLog->beneficiary_id = $reconciliation->beneficiary_id;
            $reconciliationLog->payroll_details_id = $reconciliation->payroll_detail_id;
            $reconciliationLog->payment_cycle_details_id = $reconciliation->id;
            $reconciliationLog->status_id = 10;
            $reconciliationLog->created_by = Auth::user()->id;
            $reconciliationLog->save();
           
        }

        $payroll_detail = PayrollDetail::where('id', $reconciliation->payroll_detail_id)->first();
        //  return  $payroll_detail;
// Check if the PayrollDetail exists before updating
        if ($payroll_detail) {
            $payroll_detail->status_id = 10;
            $payroll_detail->save();
            // $this->beneficiaryDelete($payroll_detail->beneficiary_id);

         }


        return response()->json([
            'message' => 'Payroll Reconciliation deleted successfully.',
            'success' => true,
        ], 200);
    }

      public function beneficiaryDelete($beneficiary_id)
    {
        // return $beneficiary_id;
        try {
            $p_detail = PayrollPaymentCycleDetail::where('beneficiary_id', $beneficiary_id)->first();

            // return  $p_detail;
            if ($p_detail) {
                $payroll = PayrollPaymentCycle::where('id', $p_detail->payroll_payment_cycle_id)->first();
                $payroll->total_beneficiaries -= 1;
                $payroll->total_charge -= $p_detail->charge;
                $payroll->sub_total_amount -= $p_detail->amount;
                $payroll->total_amount -= $p_detail->total_amount;
                $payroll->save();
                // $p_detail->status = "Rejected";
                // $p_detail->status_id = 3;
                // $p_detail->save();
                // BeneficiaryPayrollPaymentStatusLog::where('beneficiary_id', $beneficiary_id)->update(['status_id' => 3]);
                // $p_detail->forceDelete();
            }
            return $p_detail;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
    //data pull
    public function reconciliationDataPullGetData(Request $request)
    {

        $query = PayrollPaymentCycle::query()
            ->whereIn('status', ['Partially Completed', 'Initiated', 'Completed'])
            ->with(['program', 'financialYear', 'installment']); // Load the necessary relationships
        // dd($query->get());
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
                        $q->where('financial_year', 'like', '%' . $searchTerm . '%')
                        ->orWhere('financial_year', 'like', '%'. Helper::banglaToEnglish($searchTerm).'%');
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
        if (request()->has('financial_year_id')) {
            $financial_year_id = request('financial_year_id');
            $query->where('financial_year_id', $financial_year_id);
        }
        if (request()->has('installment_id')) {
            $installmentId = request('installment_id');
            $query->where('installment_schedule_id', $installmentId);
        }
        if (request()->has('status')) {
            $status = request('status');
            $query->where('status', $status);
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
                    \DB::raw('SUM(CASE WHEN status_id = 6 THEN 1 ELSE 0 END) as completed_count'), //compelete_count at ibas++
                    \DB::raw('SUM(CASE WHEN status_id = 7 THEN 1 ELSE 0 END) as failed_count'), //failed
                    \DB::raw('SUM(CASE WHEN status_id = 8 THEN 1 ELSE 0 END) as resubmitted_count'), //inforamton updated
                    // \DB::raw('COUNT(*) as payment_cycle_details_count')
                    // \DB::raw('GROUP_CONCAT(status_id) as all_beneficiary')
                    \DB::raw('SUM(CASE WHEN status_id IN (6,7, 8) THEN 1 ELSE 0 END) as status_total'),
                )->groupBy('payroll_payment_cycle_id');
            },
            'program',
            'installment',
            'financialYear',
        ])
        ->orderBy('created_at','desc')
        // ->orderBy('financial_year_id','desc')
        // ->orderBy('installment_schedule_id','desc')
        ->paginate(request('perPage'));

        return response()->json($supplementary);
    }

    public function reconciliationDataPull(IbusPpService $ibusPpService, IbusDataService $ibusDataService ,$id)
    {
        // $items = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)
        //     ->where('status_id', 5)
        //     ->get(); // demo this get will come from api

        // if ($items->isEmpty()) {
        //     return response()->json(["message" => "No data found", "status" => 404]);
        // }

        // // Update the matching records
        // foreach ($items as $key => $item) {
        //     $status = $key % 2 == 0? 7: 6;
        //     $item->status_id = $status;
        //     $item->save();

        //     BeneficiaryPayrollPaymentStatusLog::create([
        //         'beneficiary_id' => $item->beneficiary_id,
        //         'payroll_details_id' => $item->payroll_detail_id,
        //         'payment_cycle_details_id' => $item->id,
        //         'status_id' => $status, //test status
        //         'created_by' => auth()->user()->id,
        //     ]);

        // }

        // $completeCount = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)
        //     ->where('status_id', 6)
        //     ->count();

        // $failedCount = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)
        //     ->where('status_id', 7)
        //     ->count();

        // if ($failedCount > 0 && $completeCount > 0) {
        //     PayrollPaymentCycle::where('id', $id)->update(['status' => 'Partially Completed']);
        // } elseif ($completeCount > 0 && $failedCount == 0) {
        //     PayrollPaymentCycle::where('id', $id)->update(['status' => 'Completed']);
        // }

        // return response()->json(["message" => "Data pull successful", "status" => 200]);

        // Production code
        $paymentCycle = PayrollPaymentCycle::find($id);

        // Generate data for IBAS++ and ensure it is valid
        $payrollDataPullPayload = $ibusDataService->genPayrollReconciliationPullData($paymentCycle);

        if (empty($payrollDataPullPayload)) {
            throw new \Exception('Generated payroll data is invalid or empty.');
        }

        // Pull reconciliation data from IBAS++
        $dataPullRes = $ibusPpService->pullReconciliationData($payrollDataPullPayload);
        if ($dataPullRes === false) {
            throw new \Exception('Failed to pull reconciliation data from IBAS++.');
        }

        if($dataPullRes["operationResult"] == false){
            throw new \Exception($dataPullRes["errorMsg"]);
        }
        $paylist = $dataPullRes['paylist'];

        Log::info(json_encode($paylist));
        // return $paylist;

        // if (empty($paylist)) {
        //     throw new \Exception('Paylist is empty. No reconciliation data to process.');
        // }

        \DB::beginTransaction();
        try{

            $beneficiaryIds = array_column($paylist, 'beneficiaryId');
            $failedPayrollDetails = PayrollPaymentCycleDetail::whereIn('beneficiary_id', $beneficiaryIds)->where('status_id','<>', 7)->get();
            
            $failedLogs = [];
            $authId = auth()->user()->id;

            foreach ($paylist as $payment) {
                $detail = $failedPayrollDetails->firstWhere('beneficiary_id', $payment['beneficiaryId']);
                
                if ($detail) {
                    $detail->returned_remarks = $payment['remarks'];
                    $detail->returned_code = $payment['returnedCode'];
                    $detail->returned_text = $payment['returnedText'];
                    $detail->eft_reference_number = $payment['eftReferenceNumber'];
                    $detail->payment_uid = $payment['paymentUid'];
                    $detail->status_id = 7;
                    $detail->save();

                    $failedLogs[] = [
                        'beneficiary_id' => $detail->beneficiary_id,
                        'payroll_details_id' => $detail->payroll_detail_id,
                        'payment_cycle_details_id' => $detail->id,
                        'status_id' => 7,
                        'returned_remarks' => $payment['remarks'],
                        'returned_code' => $payment['returnedCode'],
                        'returned_text' => $payment['returnedText'],
                        'eft_reference_number' => $payment['eftReferenceNumber'],
                        'payment_uid' => $payment['paymentUid'],
                        'created_by' => $authId,
                    ];

                }
            }

            if(count($failedLogs)){
                collect($failedLogs)->chunk(1000)->each(function ($chunk) {
                    BeneficiaryPayrollPaymentStatusLog::insert($chunk->toArray());
                });
                
            }

            $updateSuccessCnt = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $paymentCycle->id)->whereNotIn('beneficiary_id', $beneficiaryIds)->where('status_id','<>', 6)->update(['status_id' => 6]);
            $successLogs = [];
            if($updateSuccessCnt){
                $successPayrollDetails = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $paymentCycle->id)->whereNotIn('id', $beneficiaryIds)->where('status_id','<>', 7)->get();
                foreach($successPayrollDetails as $detail){
                    $successLogs[] = [
                        'beneficiary_id' => $detail->beneficiary_id,
                        'payroll_details_id' => $detail->payroll_detail_id,
                        'payment_cycle_details_id' => $detail->id,
                        'status_id' => 6,
                        'created_by' => $authId,
                    ];
                }
            }

            if(count($successLogs)){
                collect($successLogs)->chunk(1000)->each(function ($chunk) {
                    BeneficiaryPayrollPaymentStatusLog::insert($chunk->toArray());
                });
            }

            $completeCount = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)
            ->where('status_id', 6)
            ->count();

            $failedCount = PayrollPaymentCycleDetail::where('payroll_payment_cycle_id', $id)
                ->where('status_id', 7)
                ->count();

            if ($failedCount > 0) {
                PayrollPaymentCycle::where('id', $id)->update(['status' => 'Partially Completed']);
            } elseif ($completeCount > 0 && $failedCount == 0) {
                PayrollPaymentCycle::where('id', $id)->update(['status' => 'Completed']);
            }

            \DB::commit();

            return response()->json(["message" => "Data pull successful", 'data'=> $dataPullRes ,"status" => 200]);

        }catch(\Throwable $t){
            \DB::rollBack();
            throw $t;
        }
  
    }
}