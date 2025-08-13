<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Application\CommitteeApplicationService;
use App\Http\Services\Admin\Application\OfficeApplicationService;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\RoleTrait;
use App\Models\Bank;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyBeneficiaryBankInfoLog;
use App\Models\EmergencyBeneficiaryPayrollPaymentStatusLog;
use App\Models\EmergencyPayrollDetails;
use App\Models\EmergencyPayrollPaymentCycle;
use App\Models\EmergencyPayrollPaymentCycleDetails;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\BeneficiaryChangeTracking;
use App\Models\BeneficiaryChangeType;
use App\Models\Mfs;
use App\Models\PayrollPaymentProcessorArea;

class EmergencyReconciliatioController extends Controller
{
    use MessageTrait, RoleTrait;

    public function getEmergencyReconciliation(Request $request)
    {
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $reconciliation = EmergencyPayrollPaymentCycleDetails::with([
            'beneficiaries.program',
            'beneficiaries.permanentDistrict',
            'beneficiaries.permanentDivision',
            'bank',
            'branch',
        ])->whereIn('status_id', [7, 8, 11, 9]);
        // ->whereIn('status_id', [4, 5, 6, 7, 8]);


        if ($searchText) {
            $reconciliation->whereHas('beneficiaries', function ($query) use ($searchText) {
                $query->where(function ($subQuery) use ($searchText) {
                    $subQuery->where('name_en', 'LIKE', "%$searchText%")
                        ->orWhere('name_bn', 'LIKE', "%$searchText%")
                        ->orWhere('bank_name', 'LIKE', "%$searchText%")
                        ->orWhere('branch_name', 'LIKE', "%$searchText%")
                        ->orWhere('account_number', 'LIKE', "%$searchText%")
                        ->orWhere('nationality', 'LIKE', "%$searchText%")
                        ->orWhere('verification_number', 'LIKE', "%$searchText%")
                        ->orWhere('current_address', 'LIKE', "%$searchText%")
                        ->orWhere('permanent_address', 'LIKE', "%$searchText%");
                });
            });
        }

        $x = $reconciliation->orderBy('updated_at', 'DESC')->get();
        $mapped = $x->map(function ($item) {
            return $item->beneficiaries;
        });

        $this->applyUserWiseFiltering($mapped);
        $results = $reconciliation->paginate($perPage, ['*'], 'page', $page);

        return $results;

        // $query->orderBy('id', 'DESC');
        // return $query->paginate($perPage, ['*'], 'page', $page);

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

    private function getPaymentProcessorIds(EmergencyBeneficiary $beneficiary){
        return PayrollPaymentProcessorArea::when($beneficiary->permanent_city_corp_id , function ($q) use($beneficiary){
            $q->where('city_corp_id',$beneficiary->permanent_city_corp_id); 
        })
        ->when($beneficiary->permanent_district_pourashava_id , function ($q) use($beneficiary){
            $q->where('district_pourashava_id',$beneficiary->permanent_district_pourashava_id); 
        })
        ->when($beneficiary->permanent_upazila_id, function ($q) use($beneficiary){
            $q->where('upazila_id',$beneficiary->permanent_upazila_id); 
        })
        ->when($beneficiary->permanent_pourashava_id, function ($q) use($beneficiary){
            $q->where('pourashava_id',$beneficiary->permanent_pourashava_id); 
        })
        ->when($beneficiary->permanent_thana_id, function ($q) use($beneficiary){
            $q->where('thana_id',$beneficiary->permanent_thana_id); 
        })
        ->when($beneficiary->permanent_union_id, function ($q) use($beneficiary){
            $q->where('union_id',$beneficiary->permanent_union_id); 
        })
        ->when($beneficiary->permanent_ward_id , function ($q) use($beneficiary){
            $q->where('ward_id',$beneficiary->permanent_ward_id)->orWhereNull('ward_id'); 
        })
        ->pluck('payment_processor_id');
    }

    public function getBanksByBeneficiaryLocation($id){
        $emergencyPayrollPaymentCycle = EmergencyPayrollPaymentCycleDetails::findOrFail($id);
        $beneficiary = EmergencyBeneficiary::where('beneficiary_id', $emergencyPayrollPaymentCycle->emergency_beneficiary_id)
        ->first();
        $paymentProcessorIds = $this->getPaymentProcessorIds($beneficiary);
        
        $banks = Bank::whereHas('payrollPaymentProcessors', function ($q) use($paymentProcessorIds){
            $q->whereIn('id', $paymentProcessorIds);
        })
        ->with('payrollPaymentProcessors', function ($q) use($paymentProcessorIds){
            $q->whereIn('id', $paymentProcessorIds)->with('branches');
        })
        // ->select('id', 'name_en', 'name_bn')
        ->get();
        // return $banks;
        return $banks->map(function($bank){
            $branches = [];
            foreach($bank->payrollPaymentProcessors as $processor){
                foreach($processor->branches as $branch){
                    $branches[] = [
                        'id' => $branch->id,
                        'name_en' => $branch->name_en,
                        'name_bn' => $branch->name_bn,
                    ];
                }
            }
            return [
                'id' => $bank->id,
                'name_en' => $bank->name_en,
                'name_bn' => $bank->name_bn,
                'branches' => $branches
            ];
        });
    }
    public function getMfsesByBeneficiaryLocation($id){
        $emergencyPayrollPaymentCycle = EmergencyPayrollPaymentCycleDetails::findOrFail($id);
        $beneficiary = EmergencyBeneficiary::where('beneficiary_id', $emergencyPayrollPaymentCycle->emergency_beneficiary_id)
        ->first();
        $paymentProcessorIds = $this->getPaymentProcessorIds($beneficiary);
        
        $mfses = Mfs::whereHas('payrollPaymentProcessors', function ($q) use($paymentProcessorIds){
            $q->whereIn('id', $paymentProcessorIds);
        })
        ->select('id', 'name_en', 'name_bn')
        ->get();
        return $mfses;
    }

    public function edit($id)
    {

        $emergencyPayrollPaymentCycle = EmergencyPayrollPaymentCycleDetails::findOrFail($id);
        $beneficiary = EmergencyBeneficiary::where('beneficiary_id', $emergencyPayrollPaymentCycle->emergency_beneficiary_id)
        ->first();

        return $beneficiary;
    }

    //     public function update(Request $request, $id)
    //     {

    //         $beforeUpdate = EmergencyBeneficiary::findOrFail($id);
    //         $beneficiary = EmergencyBeneficiary::findOrFail($id);
    //         $emergencyPaymentCycleDetails = EmergencyPayrollPaymentCycleDetails::where('emergency_beneficiary_id', $beneficiary->id)->first();

    //         try {
    //             $beneficiaryBankInfo = new EmergencyBeneficiaryBankInfoLog();
    //             $beneficiaryBankInfo->beneficiary_id = $beneficiary->id;
    //             $beneficiaryBankInfo->account_name = $beneficiary->account_name;
    //             $beneficiaryBankInfo->account_number = $beneficiary->account_number;
    //             $beneficiaryBankInfo->account_owner = $beneficiary->account_owner;
    //             $beneficiaryBankInfo->account_type = $beneficiary->account_type;
    //             $beneficiaryBankInfo->bank_name = $beneficiary->bank_name;
    //             $beneficiaryBankInfo->branch_name = $beneficiary->branch_name;
    //             $beneficiaryBankInfo->save();

    //             $beneficiary->account_name = $request->account_name;
    //             $beneficiary->account_number = $request->account_number;
    //             $beneficiary->account_owner = $request->account_owner;
    //             $beneficiary->account_type = $request->account_type;
    //             $beneficiary->bank_name = $request->bank_name;
    //             $beneficiary->branch_name = $request->branch_name;
    //             $beneficiary->update();
    //             if (isset($emergencyPaymentCycleDetails)) {
    //     $emergencyPaymentCycleDetails->account_name = $request->account_name;
    //     $emergencyPaymentCycleDetails->account_number = $request->account_number;
    //     $emergencyPaymentCycleDetails->account_owner = $request->account_owner;
    //     $emergencyPaymentCycleDetails->account_type = $request->account_type;
    //     $emergencyPaymentCycleDetails->bank_name = $request->bank_id;
    //     $emergencyPaymentCycleDetails->branch_name = $request->branch_id;
    //     $emergencyPaymentCycleDetails->status_id = 11;
    //     $emergencyPaymentCycleDetails->update();

    // }




    //             Helper::activityLogUpdate($beneficiary, $beforeUpdate, 'Emergency Beneficiary', 'Emergency Beneficiary Updated !');

    //             return $beneficiary;
    //         } catch (\Throwable $th) {
    //             throw $th;
    //         }

    //     }

    public function update(Request $request, $id)
    {

        $payrollPaymentCycleDetails = EmergencyPayrollPaymentCycleDetails::findOrFail($id);
        // return $payrollPaymentCycleDetails;

        $beneficiary = EmergencyBeneficiary::where('beneficiary_id', $payrollPaymentCycleDetails->emergency_beneficiary_id)->first();
        $beforeUpdate = $beneficiary->replicate();
        //  return $beforeUpdate;

        try {
            $beneficiary->email = $request->email;
            $beneficiary->account_name = $request->account_name;
            $beneficiary->account_number = $request->account_number;
            $beneficiary->account_owner = $request->account_owner;
            if($request->account_type == 1){
                $beneficiary->bank_id = $request->bank_id;
                $beneficiary->bank_branch_id = $request->bank_branch_id;
                $beneficiary->mfs_id = null;
            }else if($request->account_type == 2){
                $beneficiary->bank_id = null;
                $beneficiary->bank_branch_id = null;
                $beneficiary->mfs_id = $request->mfs_id;
            }

            $beneficiary->account_type = $request->account_type;
            
            
            $beneficiary->update();
            if (isset($payrollPaymentCycleDetails)) {
                // $payrollPaymentCycleDetails->email = $request->email;
                $payrollPaymentCycleDetails->account_name = $request->account_name;
                $payrollPaymentCycleDetails->account_number = $request->account_number;
                $payrollPaymentCycleDetails->account_owner = $request->account_owner;
                $payrollPaymentCycleDetails->account_type = $request->account_type;
                if($request->account_type == 1){
                    $payrollPaymentCycleDetails->bank_id = $request->bank_id;
                    $payrollPaymentCycleDetails->bank_branch_id = $request->bank_branch_id;
                    $payrollPaymentCycleDetails->mfs_id = null;
                }else if($request->account_type == 2){
                    $payrollPaymentCycleDetails->bank_id = null;
                    $payrollPaymentCycleDetails->bank_branch_id = null;
                    $payrollPaymentCycleDetails->mfs_id = $request->mfs_id;
                }
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

    public function emergencyReconciliationUpdate($id)
    {
        $beforeUpdate = EmergencyPayrollPaymentCycleDetails::where('id', $id)->first();

        // Get the emergency payrolls by ID
        $reconciliation = EmergencyPayrollPaymentCycleDetails::where('id', $id)->first();


        //   return  $emergencyReconciliation;

        // Bulk update EmergencyPayrollPaymentCycleDetails
        if (!empty($reconciliation)) {
            $reconciliation = EmergencyPayrollPaymentCycleDetails::where('id', $reconciliation->id)->first();
            $reconciliation->status_id = 8;
            $reconciliation->update();
        }

        if (!empty($reconciliation)) {
            $reconciliation = EmergencyPayrollPaymentCycleDetails::with('payroll')->where('id', $reconciliation->id)->first();
            $reconciliationLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
            $reconciliationLog->emergency_beneficiary_id  = $reconciliation->emergency_cycle_id;
            $reconciliationLog->emergency_payroll_details_id  = $reconciliation->emergency_payroll_detail_id;
            $reconciliationLog->emergency_payment_cycle_details_id  = $reconciliation->id;
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
    public function emergencyReconciliationDelete($id)
    {
        // return $id;
        // Get the emergency payrolls by ID
        $beforeUpdate = EmergencyPayrollPaymentCycleDetails::where('id', $id)->first();
        $emergencyReconciliation = EmergencyPayrollPaymentCycleDetails::where('id', $id)->first();

        // Bulk update EmergencyPayrollPaymentCycleDetails
        if (!empty($emergencyReconciliation)) {
            $emergencyReconciliation = EmergencyPayrollPaymentCycleDetails::where('id', $emergencyReconciliation->id)->first();
            $emergencyReconciliation->status_id = 9;
            $emergencyReconciliation->update();
            $emergencyPayrollDetail = EmergencyPayrollDetails::find($emergencyReconciliation->emergency_payroll_detail_id);
            if($emergencyPayrollDetail){
                $emergencyPayrollDetail->status_id = 9;
                $emergencyPayrollDetail->update();
            }
            Helper::activityLogUpdate($emergencyReconciliation, $beforeUpdate, 'Emergency Payment Reconciliation', 'Emergency Payment Cycle Reconciliation Updated !');
        }
        if (!empty($emergencyReconciliation)) {
            $emergencyReconciliation = EmergencyPayrollPaymentCycleDetails::with('EmergencyPayroll')->where('id', $emergencyReconciliation->id)->first();
            $emergencyReconciliationLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
            $emergencyReconciliationLog->emergency_beneficiary_id = $emergencyReconciliation->emergency_beneficiary_id;
            $emergencyReconciliationLog->emergency_payroll_details_id = $emergencyReconciliation->EmergencyPayroll->id;
            $emergencyReconciliationLog->emergency_payment_cycle_details_id = $emergencyReconciliation->id;
            $emergencyReconciliationLog->status_id = 9;
            $emergencyReconciliationLog->created_by = Auth::user()->id;
            $emergencyReconciliationLog->save();
        }

        return response()->json([
            'message' => 'Emergency Reconciliation deleted successfully.',
            'success' => true,
        ], 200);
    }
    public function reconciliationDelete($id)
    {
        // return $id;
        // Get the emergency payrolls by ID
        $beforeUpdate = EmergencyPayrollPaymentCycleDetails::where('id', $id)->first();
        $reconciliation = EmergencyPayrollPaymentCycleDetails::where('id', $id)->first();
        // return  $emergencyReconciliation;
        // Bulk update EmergencyPayrollPaymentCycleDetails
        if (!empty($reconciliation)) {
            $reconciliation = EmergencyPayrollPaymentCycleDetails::where('id', $reconciliation->id)->first();
            $reconciliation->status_id = 9;
            $reconciliation->update();
            Helper::activityLogUpdate($reconciliation, $beforeUpdate, 'Emergency Payment Reconciliation', 'Emergency Payment Cycle Reconciliation Updated !');
        }
        if (!empty($reconciliation)) {
            $reconciliation = EmergencyPayrollPaymentCycleDetails::with('payroll')->where('id', $reconciliation->id)->first();
            $reconciliationLog = new EmergencyBeneficiaryPayrollPaymentStatusLog();
            $reconciliationLog->beneficiary_id = $reconciliation->emergency_beneficiary_id;
            $reconciliationLog->emergency_payroll_details_id  = $reconciliation->emergency_payroll_detail_id;
            $reconciliationLog->emergency_payment_cycle_details_id  = $reconciliation->id;
            $reconciliationLog->status_id = 9;
            $reconciliationLog->created_by = Auth::user()->id;
            $reconciliationLog->save();
        }

        return response()->json([
            'message' => 'Payroll Reconciliation deleted successfully.',
            'success' => true,
        ], 200);
    }

    public function reconciliationDataPullGetData(Request $request)
    {
        $query = EmergencyPayrollPaymentCycle::query()
            ->whereIn('status', ['Partially Completed', 'Initiated', 'Completed'])
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
            $query->whereHas('CycleDetails', function ($q) use ($statusId) {
                $q->where('status_id', $statusId);
            });
        }

        $supplementary = $query->with([
            'CycleDetails' => function ($query) {
                $query->select(
                    'emergency_cycle_id',
                    \DB::raw('SUM(CASE WHEN status_id = 6 THEN 1 ELSE 0 END) as completed_count'), //compelete_count at ibas++
                    \DB::raw('SUM(CASE WHEN status_id = 7 THEN 1 ELSE 0 END) as failed_count'), //failed
                    \DB::raw('SUM(CASE WHEN status_id = 8 THEN 1 ELSE 0 END) as resubmitted_count'), //inforamton updated
                    // \DB::raw('COUNT(*) as payment_cycle_details_count')
                    // \DB::raw('GROUP_CONCAT(status_id) as all_beneficiary')
                    \DB::raw('SUM(CASE WHEN status_id IN (6,7, 8) THEN 1 ELSE 0 END) as status_total'),
                )->groupBy('emergency_cycle_id');
            },
            'program',
            'installment',
            'financialYear'
        ])->paginate(request('perPage'));

        return response()->json($supplementary);
    }

    public function reconciliationDataPull($id)
    {
        //Api call start
        // $response = Http::get('https://api.example.com/data', [
        //     'payroll_payment_cycle_id' => $id,
        // ]);

        // if ($response->failed()) {
        //     return response()->json(["message" => "Failed to fetch data from API", "status" => 500]);
        // }

        // $items = collect($response->json('items')); // Adjust based on actual API response structure

        // if ($items->isEmpty()) {
        //     return response()->json(["message" => "No data found", "status" => 404]);
        // }
        //api call end
        // return $id;
        $items = EmergencyPayrollPaymentCycleDetails::where('emergency_cycle_id', $id)
            ->where('status_id', 5)
            ->get(); // demo this get will come from api

        if ($items->isEmpty()) {
            return response()->json(["message" => "No data found", "status" => 404]);
        }

        // Update the matching records
        foreach ($items as $key => $item) {
            $item->status_id = 7;
            $item->save();

            EmergencyBeneficiaryPayrollPaymentStatusLog::create([
                'emergency_beneficiary_id' => $item->emergency_beneficiary_id,
                'emergency_payroll_detail_id' => $item->emergency_payroll_detail_id,
                'emergency_payment_cycle_details_id' => $item->id,
                'status_id' => 7,
                'created_by' => auth()->user()->id,
            ]);
        }

        $completeCount = EmergencyPayrollPaymentCycleDetails::where('emergency_cycle_id', $id)
            ->where('status_id', 6)
            ->count();

        $failedCount = EmergencyPayrollPaymentCycleDetails::where('emergency_cycle_id', $id)
            ->where('status_id', 7)
            ->count();

        if ($failedCount > 0 && $completeCount == 0) {
            EmergencyPayrollPaymentCycle::where('id', $id)->update(['status' => 'Partially Completed']);
        } elseif ($completeCount > 0 && $failedCount == 0) {
            EmergencyPayrollPaymentCycle::where('id', $id)->update(['status' => 'Completed']);
        }

        return response()->json(["message" => "Data pull successful", "status" => 200]);
    }
}
