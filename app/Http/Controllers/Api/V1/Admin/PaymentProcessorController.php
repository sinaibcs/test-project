<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Admin\PayrollManagement\PaymentTrackingResource;
use App\Http\Resources\Mobile\Payroll\PaymentTrackingMobileResource;
use App\Models\Bank;
use App\Models\BankBranch;
use App\Models\Beneficiary;
use App\Models\Mfs;
use App\Models\PayrollPaymentProcessor;
use App\Models\PayrollPaymentProcessorArea;
use App\Models\ProcessorBranch;
use App\Rules\UniquePaymentProcessor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Throwable;


class PaymentProcessorController extends Controller
{
    public function getMfs()
    {
        return Mfs::all();
    }

    public function index(Request $request)
    {
        $query = PayrollPaymentProcessor::with([
            'branch',
            'mfs',
            'bank',
            'ProcessorArea',
            'ProcessorArea.location',
            'ProcessorArea.division',
            'ProcessorArea.district',
            'ProcessorArea.upazila',
            'ProcessorArea.union',
            'ProcessorArea.thana',
            'ProcessorArea.CityCorporation',
            'ProcessorArea.DistrictPourashava',
            'ProcessorArea.LocationType',
            'ProcessorArea.ward'
        ])->latest();

        if ($request->search) {
            $query->where(function ($query) use ($request) {
                $searchTerm = '%' . $request->search . '%';
                //Search the data by name
//                $query->where('name_en', 'LIKE', '%' . $request->search . '%')
//                    ->orWhere('name_bn', 'LIKE', '%' . $request->search . '%')
//                    ->orWhere('focal_phone_no', 'LIKE', '%' . $request->search . '%')
//                    ->orWhere('focal_email_address', 'LIKE', '%' . $request->search . '%')
//                    ->orWhere('processor_type', 'LIKE', '%' . $request->search . '%');

                $query->where('processor_type', 'LIKE', '%' . $request->search . '%');
                $query->orWhereHas('bank', function ($query) use ($searchTerm) {
                    $query->where('name_en', 'LIKE', $searchTerm)
                        ->orWhere('name_bn', 'LIKE', $searchTerm);
                });
            });
        }

        if ($request->hasAny([
            'processor_type',
            'bank_id',
            'branch_id',
            'mfs_id',
            'location_type',
            'division_id',
            'district_id',
            'upazila_id',
            'city_corp_id',
            'thana_id',
            'district_pouro_id',
            'union_id'
        ])) {
            $query->whereHas('ProcessorArea', function ($query) use ($request) {
                if ($request->processor_type) {
                    $query->where('processor_type', $request->processor_type);
                }
                if ($request->bank_id) {
                    $query->where('bank_id', $request->bank_id);
                }
                // if ($request->branch_id) {
                //     $query->where('branch_id', $request->branch_id);
                // }
                if ($request->mfs_id) {
                    $query->where('mfs_id', $request->mfs_id);
                }
                if ($request->location_type) {
                    $query->where('location_type', $request->location_type);
                }
                if ($request->division_id) {
                    $query->where('division_id', $request->division_id);
                }
                if ($request->district_id) {
                    $query->where('district_id', $request->district_id);
                }
                if ($request->upazila_id) {
                    $query->where('upazila_id', $request->upazila_id);
                }
                if ($request->city_corp_id) {
                    $query->where('city_corp_id', $request->city_corp_id);
                }
                if ($request->thana_id) {
                    $query->where('thana_id', $request->thana_id);
                }
                if ($request->district_pouro_id) {
                    $query->where('district_pourashava_id', $request->district_pouro_id);
                }
                if ($request->union_id) {
                    $query->where('union_id', $request->union_id);
                }
            });

            if ($request->branch_id) {
                $query->whereHas('branch', function ($query) use ($request) {
                    $query->where('branch_id', $request->branch_id);
                });
            }
        }

        $data = $query->paginate(request('perPage'));

        $data->getCollection()->transform(function ($processor) {
            $location_en = '';
            $location_bn = '';

            if ($processor->ProcessorArea) {
                if ($processor->ProcessorArea->union) {
                    $location_en .= $processor->ProcessorArea->union->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->union->name_bn . ', ';
                } elseif ($processor->ProcessorArea->DistrictPourashava) {
                    $location_en .= $processor->ProcessorArea->DistrictPourashava->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->DistrictPourashava->name_bn . ', ';
                } elseif ($processor->ProcessorArea->thana) {
                    $location_en .= $processor->ProcessorArea->thana->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->thana->name_bn . ', ';
                }

                if ($processor->ProcessorArea->upazila) {
                    $location_en .= $processor->ProcessorArea->upazila->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->upazila->name_bn . ', ';
                } elseif ($processor->ProcessorArea->CityCorporation) {
                    $location_en .= $processor->ProcessorArea->CityCorporation->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->CityCorporation->name_bn . ', ';
                } elseif ($processor->ProcessorArea->DistrictPourashava) {
                    $location_en .= $processor->ProcessorArea->DistrictPourashava->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->DistrictPourashava->name_bn . ', ';
                }

                if ($processor->ProcessorArea->district) {
                    $location_en .= $processor->ProcessorArea->district->name_en . ', ';
                    $location_bn .= $processor->ProcessorArea->district->name_bn . ', ';
                }

                if ($processor->ProcessorArea->division) {
                    $location_en .= $processor->ProcessorArea->division->name_en;
                    $location_bn .= $processor->ProcessorArea->division->name_bn;
                }
            }

            $processor->full_location_en = rtrim($location_en, ', ');
            $processor->full_location_bn = rtrim($location_bn, ', ');

            return $processor;
        });

        return $this->sendResponse($data);
    }

    public function store(Request $request)
    {
        // return $request->all();
        $request->validate([
            'processor_type' => 'required',
//             focal information removed due to client requirement
//            'name_en' => 'string|nullable',
//            'name_bn' => 'string|nullable',
//            'focal_phone' => 'required|unique:payroll_payment_processors,focal_phone_no',
//            'focal_email' => 'required|email|unique:payroll_payment_processors,focal_email_address',
            'division' => 'required',
            'district' => 'nullable|integer',
            'location_type' => 'nullable|integer',
        ]);

        $location_id = collect([
            $request->division,
            $request->district,
            $request->city_corporation,
            $request->district_pourashava,
            $request->upazila,
            $request->thana,
            $request->union,
        ])->filter()->last();

        $exists = DB::table('payroll_payment_processors')
            ->join('payroll_payment_processor_areas', 'payroll_payment_processors.id', '=', 'payroll_payment_processor_areas.payment_processor_id')
            ->join('processor_branches', 'payroll_payment_processors.id', '=', 'processor_branches.processor_id')
            ->where('payroll_payment_processors.processor_type', $request->processor_type)
            ->where('payroll_payment_processors.bank_id', $request->bank_id)
            ->where('payroll_payment_processors.mfs_id', $request->mfs_id)
            ->where('payroll_payment_processor_areas.location_id', $location_id);

        // if (!empty($request->branch_id) && is_array($request->branch_id)) {
        //     $exists->whereIn('processor_branches.branch_id', $request->branch_id);
        // } else {
        //     $exists->where('processor_branches.branch_id', $request->branch_id);
        // }

        if ($exists->exists()  == true) {
            return response()->json(['success' => false, 'status' => '403', 'message' => 'Processor already exists in this area.']);
        }

        DB::beginTransaction();
        try {
            $paymentProcessor = PayrollPaymentProcessor::create([
                'processor_type' => $request->processor_type,
//                'name_en' => $request->name_en,
//                'name_bn' => $request->name_bn,
                'bank_id' => $request->bank_id,
                'mfs_id' => $request->mfs_id,
                'bank_branch_name' => $request->branch_name,
//                'focal_email_address' => $request->focal_email,
//                'focal_phone_no' => $request->focal_phone,
                // 'charge' => $request->charge,
            ]);

            if (!empty($request->branch_id) && is_array($request->branch_id)) {
                foreach ($request->branch_id as $branchId) {
                    ProcessorBranch::create([
                        'processor_id' => $paymentProcessor->id,
                        'branch_id' => $branchId,
                    ]);
                }
            }

            $location_id = collect([
                $request->division,
                $request->district,
                $request->city_corporation,
                $request->district_pourashava,
                $request->upazila,
                $request->thana,
                $request->union,
                // $request->ward
            ])->filter()->last();

            PayrollPaymentProcessorArea::create([
                'payment_processor_id' => $paymentProcessor->id,
                'division_id' => $request->division,
                'district_id' => $request->district,
                'location_type' => $request->location_type,
                'city_corp_id' => $request->city_corporation,
                'district_pourashava_id' => $request->district_pourashava,
                'upazila_id' => $request->upazila,
                'sub_location_type' => null,
                'pourashava_id' => null,
                'thana_id' => $request->thana,
                'union_id' => $request->union,
                'ward_id' => $request->ward,
                'location_id' => $location_id,
                'office_id' => null,
            ]);

            DB::commit();

            return response()->json(['success' => true, 'status' => '200', 'message' => 'Payment processor created successfully']);
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function show($id)
    {
        // return PayrollPaymentProcessor::with('mfs', 'bank', 'branch.branch', 'ProcessorArea', 'ProcessorArea.division', 'ProcessorArea.district', 'ProcessorArea.upazila', 'ProcessorArea.union', 'ProcessorArea.thana', 'ProcessorArea.ward', 'ProcessorArea.CityCorporation', 'ProcessorArea.DistrictPourashava', 'ProcessorArea.LocationType')->findOrFail($id);
        return PayrollPaymentProcessor::with([
            'mfs',
            'bank',
            'branch' => function ($query) {
                $query->with([
                    'branch' => function ($branchQuery) {
                        $branchQuery->select('id', 'name_en', 'name_bn', 'routing_number');
                    }
                ]);
            },
            'ProcessorArea',
            'ProcessorArea.division',
            'ProcessorArea.district',
            'ProcessorArea.upazila',
            'ProcessorArea.union',
            'ProcessorArea.thana',
            'ProcessorArea.ward',
            'ProcessorArea.CityCorporation',
            'ProcessorArea.DistrictPourashava',
            'ProcessorArea.LocationType'
        ])->findOrFail($id);
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'processor_type' => 'required',
//            focal information removed due to client requirement
//            'name_en' => 'string|nullable',
//            'name_bn' => 'string|nullable',
//            'focal_phone' => 'required',
//            'focal_email' => 'required|email',
            'division' => 'required',
            'district' => 'nullable|integer',
            'location_type' => 'nullable|integer',
            // 'charge' => 'required',
        ]);

        $location_id = collect([
            $request->division,
            $request->district,
            $request->city_corporation,
            $request->district_pourashava,
            $request->upazila,
            $request->thana,
            $request->union,
        ])->filter()->last();

        $exists = DB::table('payroll_payment_processors')
            ->join('payroll_payment_processor_areas', 'payroll_payment_processors.id', '=', 'payroll_payment_processor_areas.payment_processor_id')
            ->join('processor_branches', 'payroll_payment_processors.id', '=', 'processor_branches.processor_id')
            ->where('payroll_payment_processors.processor_type', $request->processor_type)
            ->where('payroll_payment_processors.bank_id', $request->bank_id)
            ->where('payroll_payment_processors.mfs_id', $request->mfs_id)
            ->where('payroll_payment_processor_areas.location_id', $location_id)
            ->where('payroll_payment_processors.id', '<>', $id);

        // if (!empty($request->branch_id) && is_array($request->branch_id)) {
        //     $exists->whereIn('processor_branches.branch_id', $request->branch_id);
        // } else {
        //     $exists->where('processor_branches.branch_id', $request->branch_id);
        // }

        DB::beginTransaction();
        try {
            $paymentProcessor = PayrollPaymentProcessor::findOrFail($id);

            if ($exists->exists()  == true) {
                return response()->json(['success' => true, 'status' => '403', 'message' => 'Processor already exists in this area. Others data updated.']);
            } else {
                $paymentProcessor->update([
                    'processor_type' => $request->processor_type,
//                    'name_en' => $request->name_en,
//                    'name_bn' => $request->name_bn,
                    'bank_id' => $request->bank_id,
                    'mfs_id' => $request->mfs_id,
//                    'focal_email_address' => $request->focal_email,
//                    'focal_phone_no' => $request->focal_phone,
                    // 'charge' => $request->charge,
                ]);

                if (!empty($request->branch_id) && is_array($request->branch_id)) {
                    $existing = ProcessorBranch::where('processor_id', $paymentProcessor->id)->get();
                    foreach ($existing as $key => $value) {
                        $value->delete();
                    }
                    foreach ($request->branch_id as $branchId) {
                        ProcessorBranch::create([
                            'processor_id' => $paymentProcessor->id,
                            'branch_id' => $branchId,
                        ]);
                    }
                }

                $location_id = collect([
                    $request->division,
                    $request->district,
                    $request->city_corporation,
                    $request->district_pourashava,
                    $request->upazila,
                    $request->thana,
                    $request->union,
                    // $request->ward
                ])->filter()->last();

                $paymentProcessorArea = PayrollPaymentProcessorArea::where('payment_processor_id', $id)->firstOrFail();
                $paymentProcessorArea->update([
                    'division_id' => $request->division,
                    'district_id' => $request->district,
                    'location_type' => $request->location_type,
                    'city_corp_id' => $request->city_corporation,
                    'district_pourashava_id' => $request->district_pourashava,
                    'upazila_id' => $request->upazila,
                    'sub_location_type' => null,
                    'pourashava_id' => null,
                    'thana_id' => $request->thana,
                    'union_id' => $request->union,
                    'ward_id' => $request->ward,
                    'location_id' => $location_id,
                    'office_id' => null,
                ]);

                DB::commit();

                return response()->json(['success' => true, 'status' => '200', 'message' => 'Payment processor updated successfully']);
            }
        } catch (Throwable $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()]);
        }
    }

    public function destroy($id)
    {
        DB::beginTransaction();

        try {
            $paymentProcessor = PayrollPaymentProcessor::findOrFail($id);
            $paymentProcessor->forceDelete();

            $processorArea = PayrollPaymentProcessorArea::where('payment_processor_id', $id);
            $processorArea->forceDelete();

            DB::commit();
            return response()->json(['message' => 'Payment Processor deleted successfully']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Failed to delete Payment Processor', 'error' => $e->getMessage()], 500);
        }
    }

    public function getBanks()
    {
        return Bank::all();
    }

    public function getBranches(Request $request, $id)
    {
        $query = BankBranch::select('id', 'bank_id', 'name_en', 'name_bn', 'district_id')
                            ->where('status', 1)
                            ->where('bank_id', $id)
                            ->whereNotNull('routing_number');

        if ($request->has('bank_district_id')) {
            $query->where('district_id',  $request->bank_district_id);
        }

        return $query->get();
    }

    public function getPaymentTrackingInfo(Request $request)
    {
        $Beneficiary = Beneficiary::with(
            'PayrollDetails.payroll.financialYear',
            'PayrollDetails.payroll.installmentSchedule',
            'PayrollDetails.payroll.office',
            'PayrollDetails.beneficiaryPayrollPaymentStatusLog.status',
            'PayrollDetails.beneficiaryPayrollPaymentStatusLog.user',
        )
            ->where(function ($query) use ($request) {
                $query->where('verification_number', $request->beneficiary_id)
                    ->orWhere('beneficiary_id', $request->beneficiary_id);
            })
            ->where('mobile', $request->phone_no)
            ->first();
        if ($Beneficiary) {
            return (new PaymentTrackingMobileResource($Beneficiary))->additional([
                'success' => true,
            ]);
        } else {
            $messageBn = "উপকারভোগী পাওয়া যায়নি";
            $messageEn = "Beneficiary Not Found";
            $ben = Beneficiary::where('verification_number', $request->beneficiary_id)
            ->orWhere('beneficiary_id', $request->beneficiary_id)->first();

            if($ben == null){
                $messageBn = "আপনার প্রদত্ত জাতীয় পরিচয় (NID) / জন্ম নিবন্ধন নম্বরের সাথে সংশ্লিষ্ট উপকারভোগী পাওয়া যায়নি";
                $messageEn = "Beneficiary not found with given National Identity (NID) / Birth Registration Number";
            }else{
                $ben = Beneficiary::where('mobile', $request->phone_no)->first();
                if($ben == null){
                    $messageBn = "উপকারভোগীর ফোন নম্বরটি সঠিক নয়";
                    $messageEn = "Beneficiary's phone number is not correct";
                }
            }

            return response()->json([
                'success' => false,
                'message_en' => $messageEn,
                'message_bn' => $messageBn,
            ]);
        }
    }
}
