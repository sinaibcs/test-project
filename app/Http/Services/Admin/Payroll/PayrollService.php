<?php

namespace App\Http\Services\Admin\Payroll;

use Arr;
use Log;
use Exception;
use Carbon\Carbon;
use App\Models\Office;
use App\Models\Payroll;
use App\Models\Location;
use App\Models\Allotment;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\PayrollDetail;
use App\Models\AllowanceProgram;
use App\Http\Traits\PayrollTrait;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Models\AllowanceProgramAge;
use App\Models\PayrollPaymentCycle;
use App\Models\AllowanceProgramAmount;
use Illuminate\Database\Query\Builder;
use App\Models\PayrollInstallmentSetting;
use App\Models\PayrollPaymentCycleDetail;
use App\Models\PayrollInstallmentSchedule;
use App\Models\PayrollVerificationSetting;
use App\Http\Services\Notification\SMSservice;
use App\Models\BeneficiaryPayrollPaymentStatusLog;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\Admin\Payroll\SavePayrollRequest;
use App\Http\Requests\Admin\Payroll\SubmitPayrollRequest;
use App\Http\Requests\Admin\Payroll\VerifyPayrollRequest;

class PayrollService
{
    use PayrollTrait;

    public function __construct(public SMSservice $SMSservice)
    {

        $this->SMSservice = $SMSservice;
    }
    public function currentFinancialYear()
    {
        return FinancialYear::query()->where('status', 1)->first();
    }

    public function getProgramInfo($program_id): array
    {
        $allowance = AllowanceProgram::findOrFail($program_id);
        $allowance_age = AllowanceProgramAge::where('allowance_program_id', $program_id)->with('gender')->get();
        $allowance_amount = AllowanceProgramAmount::where('allowance_program_id', $program_id)->with('type')->get();

        return [
            'allowance_program' => $allowance,
            'age_limit_wise_allowance' => $allowance_age,
            'type_wise_allowance' => $allowance_amount
        ];
    }
    public function getSelectedBeneficiaries(Request $request)
    {
        $program_id = $request->program_id;
        $financial_year_id = $request->financial_year_id;
        $installment_schedule_id = $request->installment_schedule_id;
        $payroll_ids = Payroll::where('allotment_id', $request->allotment_id)->pluck('id')->toArray();
        // dd($payroll_ids);
        return Beneficiary::query()
            ->join('payroll_details', 'beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
            ->join('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->whereIn('payroll_details.payroll_id', $payroll_ids)
            ->where('payrolls.program_id', $program_id)
            ->where('payrolls.financial_year_id', $financial_year_id)
            ->where('payrolls.installment_schedule_id', $installment_schedule_id)
            ->where(function ($query) {
                $query->where('payroll_details.is_set', 0)
                    ->where('payroll_details.status_id', 1);
            })
            ->select(
                'beneficiaries.*',
                'payroll_details.total_amount as total_allowance_amount',
                'payroll_details.charge as charge',
                'payroll_details.amount as amount',
            )
            ->with([
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard'
            ])
            ->get();
    }
    public function beneficiaryDelete($payroll_details_id)
    {
        try {
            $p_detail = PayrollDetail::where('id', $payroll_details_id)->first();
            if ($p_detail != null) {
                $payroll = Payroll::where('id', $p_detail->payroll_id)->first();
                $payroll->total_beneficiaries -= 1;
                $payroll->total_charge -= $p_detail->charge;
                $payroll->sub_total_amount -= $p_detail->amount;
                $payroll->total_amount -= $p_detail->total_amount;
                $payroll->save();
                $p_detail->amount = 0.00;
                $p_detail->charge = 0.00;
                $p_detail->total_amount = 0.00;
                $p_detail->status = "Rejected";
                $p_detail->status_id = 3;
                $p_detail->is_set = 0;
                $p_detail->save();
                BeneficiaryPayrollPaymentStatusLog::where('payroll_details_id', $payroll_details_id)->update(['status_id' => 3]);
                //                $p_detail->forceDelete();
            }
            return $p_detail;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
    private function getDuplicateAccountNumbers() {
        return \DB::table('beneficiaries')
        ->select('account_number')
        ->whereNotNull('account_number')
        ->where('status', 1)
        ->whereNull('deleted_at')
        ->groupBy('account_number')
        ->havingRaw('COUNT(*) > 1')
        ->pluck('account_number')->toArray();
    }
    private function allotmentFilter(Request $request, $query){
        $program_id = $request->program_id;
        $financial_year_id = $request->financial_year_id;
        $office_id = $request->office_id;
        $division_id = $request->division_id;
        $district_id = $request->district_id;
        $upazila_id = $request->upazila_id;
        $city_corp_id = $request->city_corp_id;
        $district_pourashava_id = $request->district_pourashava_id;
        $query->when($program_id, function($q) use($program_id, $request){
            if(is_array($program_id)){
                $q->whereIn('allotments.program_id', $program_id);
            }else{
                $q->where('allotments.program_id', $program_id);
            }
        });
        $query->when($financial_year_id, function ($q) use ($financial_year_id) {
            if(is_array($financial_year_id)){
                $q->whereIn('allotments.financial_year_id', $financial_year_id);
            }else{
                $q->where('allotments.financial_year_id', $financial_year_id);
            }
        });
        $query->when($upazila_id, function ($q) use ($upazila_id) {
            $q->where('allotments.upazila_id', $upazila_id);
        });
        $query->when($district_pourashava_id, function ($q) use ($district_pourashava_id) {
            $q->where('allotments.district_pourashava_id', $district_pourashava_id);
        });
        $query->when($city_corp_id, function ($q) use ($city_corp_id) {
            $q->where('allotments.city_corp_id', $city_corp_id);
        });
        $query->when($district_id, function ($q) use ($district_id) {
            $q->where('allotments.district_id', $district_id);
        });
        $query->when($division_id, function ($q) use ($division_id) {
            $q->where('allotments.division_id', $division_id);
        });
        $query->when($office_id, function ($q) use ($office_id) {
            $q->where('allotments.office_id', $office_id);
        });
    }
    public function getActivePayrollBeneficiaries($request, $forExcel = false)
    {

        $query = Beneficiary::query()
            ->leftJoin('payroll_details', 'beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
            ->leftJoin('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->where('payrolls.office_id', $request->office_id)
            ->where('payrolls.financial_year_id', $request->financial_year_id)
            ->where('payrolls.installment_schedule_id', $request->installment_id)
            ->where('payrolls.program_id', $request->program_id)
            ->when(!empty($request->beneficiary_ids), function($q)use($request){
                $q->whereIn('beneficiaries.beneficiary_id', $request->beneficiary_ids);
            })
            ->when(!empty($request->nominee_name), function($q)use($request){
                $q->where(function($q) use($request){
                    $q->where('beneficiaries.nominee_en', 'like' , "%".$request->nominee_name."%")
                    ->orWhere('beneficiaries.nominee_bn', 'like' , "%".$request->nominee_name."%");
                });
            })
            ->when(!empty($request->account_number), function($q)use($request){
                $q->where('beneficiaries.account_number', $request->account_number);
            })
            ->when(!empty($request->account_number), function($q)use($request){
                $q->where('beneficiaries.account_number', $request->account_number);
            })
            ->when(!empty($request->nid_number), function($q)use($request){
                $q->where('beneficiaries.verification_number', $request->nid_number);
            })
            ->when(!empty($request->mobile), function($q)use($request){
                $q->where('beneficiaries.mobile', $request->mobile);
            })
            ->when(!empty($request->bank_id), function($q)use($request){
                $q->where('beneficiaries.bank_id', $request->bank_id);
            })
            ->when(!empty($request->mfs_id), function($q)use($request){
                $q->where('beneficiaries.mfs_id', $request->mfs_id);
            })
            ->when(!empty($request->union_id), function($q)use($request){
                $q->where('beneficiaries.permanent_union_id', $request->union_id);
            })
            ->when(!empty($request->thana_id), function($q)use($request){
                $q->where('beneficiaries.permanent_thana_id', $request->thana_id);
            })
            ->when(!empty($request->permanent_pourashava_id), function($q)use($request){
                $q->where('beneficiaries.permanent_permanent_pourashava_id', $request->permanent_pourashava_id);
            })
            ->when(!empty($request->ward_id), function($q)use($request){
                $q->where('beneficiaries.permanent_ward_id', $request->ward_id);
            })
            ->where(function ($query) {
                $query->where('payroll_details.is_set', '=', 1)
                    ->where('payroll_details.status_id', '=', 1);
            });
        $user = auth()->user();
        if ($user->unions()->exists()) {
            $query->whereIn('beneficiaries.permanent_union_id', $user->unions()->pluck('id'));
        }
        $query
            ->select(
                'beneficiaries.*',
                'payroll_details.payroll_id as payroll_id',
                'payroll_details.id as payroll_details_id',
                'payroll_details.total_amount as total_allowance_amount',
                'payroll_details.amount',
                'payroll_details.charge',
                'payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program',
                'bank',
                'mfs'
            );
            if($forExcel){
                return $query->get();
            }

            return $query->paginate($request->per_page);
    }
    public function getPayrollApproveBeneficiaries($request, $forExcel = false)
    {

        $query = Beneficiary::query()
            ->leftJoin('payroll_details', 'beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
            ->leftJoin('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->where('payrolls.office_id', $request->office_id)
            ->where('payrolls.financial_year_id', $request->financial_year_id)
            ->where('payrolls.installment_schedule_id', $request->installment_id)
            ->where('payrolls.program_id', $request->program_id)
            ->when(!empty($request->beneficiary_ids), function($q)use($request){
                $q->whereIn('beneficiaries.beneficiary_id', $request->beneficiary_ids);
            })
            ->when(!empty($request->nominee_name), function($q)use($request){
                $q->where(function($q) use($request){
                    $q->where('beneficiaries.nominee_en', 'like' , "%".$request->nominee_name."%")
                    ->orWhere('beneficiaries.nominee_bn', 'like' , "%".$request->nominee_name."%");
                });
            })
            ->when(!empty($request->account_number), function($q)use($request){
                $q->where('beneficiaries.account_number', $request->account_number);
            })
            ->when(!empty($request->account_number), function($q)use($request){
                $q->where('beneficiaries.account_number', $request->account_number);
            })
            ->when(!empty($request->nid_number), function($q)use($request){
                $q->where('beneficiaries.verification_number', $request->nid_number);
            })
            ->when(!empty($request->mobile), function($q)use($request){
                $q->where('beneficiaries.mobile', $request->mobile);
            })
            ->when(!empty($request->bank_id), function($q)use($request){
                $q->where('beneficiaries.bank_id', $request->bank_id);
            })
            ->when(!empty($request->mfs_id), function($q)use($request){
                $q->where('beneficiaries.mfs_id', $request->mfs_id);
            })
            ->when(!empty($request->union_id), function($q)use($request){
                $q->where('beneficiaries.permanent_union_id', $request->union_id);
            })
            ->when(!empty($request->thana_id), function($q)use($request){
                $q->where('beneficiaries.permanent_thana_id', $request->thana_id);
            })
            ->when(!empty($request->permanent_pourashava_id), function($q)use($request){
                $q->where('beneficiaries.permanent_permanent_pourashava_id', $request->permanent_pourashava_id);
            })
            ->when(!empty($request->ward_id), function($q)use($request){
                $q->where('beneficiaries.permanent_ward_id', $request->ward_id);
            })
            ->where(function ($query) {
                $query->where('payroll_details.is_set', '=', 1)
                    ->where('payroll_details.status_id', '=', 2);
            });

            $user = auth()->user();
            if ($user->unions()->exists()) {
                $query->whereIn('permanent_union_id', $user->unions()->pluck('id'));
            }

        $query
            ->select(
                'beneficiaries.*',
                'payroll_details.payroll_id as payroll_id',
                'payroll_details.id as payroll_details_id',
                'payroll_details.total_amount as total_allowance_amount',
                'payroll_details.amount',
                'payroll_details.charge',
                'payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program',
                'bank',
                'mfs'
            );

            if($forExcel){
                return $query->get();
            }

            return $query->paginate($request->per_page);
    }
    public function getPayrollBeneficiaries($request, $forExcel = false)
    {
        $allotmentQuery = Allotment::query();
        $this->allotmentFilter($request, $allotmentQuery);
        $allotmentIdsQury = (clone $allotmentQuery)->select('id');
        // $payrollIds = Payroll::whereIn('allotment_id', $allotmentIdsQury);

        $query = Beneficiary::query()
            ->leftJoin('payroll_details', 'beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
            ->leftJoin('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->whereIn('payrolls.allotment_id', $allotmentIdsQury)
            ->where(function($q) use($request){
                if($request->type == 'pending')
                    $q->where('payroll_details.status_id', 1)->where('payroll_details.is_set', 1);
                elseif($request->type == 'approved')
                    $q->where('payroll_details.status_id', 2);
                elseif($request->type == 'rejected')
                    $q->where('payroll_details.status_id', 3);
                    
            })
            ->when($request->filled('financial_year_id'), function ($query) use ($request) {
                $value = $request->financial_year_id;
                return is_array($value)
                    ? $query->whereIn('payrolls.financial_year_id', $value)
                    : $query->where('payrolls.financial_year_id', $value);
            })
            ->when($request->filled('installment_id'), function ($query) use ($request) {
                $value = $request->installment_id;
                return is_array($value)
                    ? $query->whereIn('payrolls.installment_schedule_id', $value)
                    : $query->where('payrolls.installment_schedule_id', $value);
            })
            ->when($request->filled('program_id'), function ($query) use ($request) {
                $value = $request->program_id;
                return is_array($value)
                    ? $query->whereIn('payrolls.program_id', $value)
                    : $query->where('payrolls.program_id', $value);
            })
            ->when($request->filled('bank_id'), function ($query) use ($request) {
                $value = $request->bank_id;
                return is_array($value)
                    ? $query->whereIn('beneficiaries.bank_id', $value)
                    : $query->where('beneficiaries.bank_id', $value);
            })
            ->when($request->filled('mfs_id'), function ($query) use ($request) {
                $value = $request->mfs_id;
                return is_array($value)
                    ? $query->whereIn('beneficiaries.mfs_id', $value)
                    : $query->where('beneficiaries.mfs_id', $value);
            })
            ;
            // ->where(function ($query) {
            //     $query->where('payroll_details.is_set', '=', 1)
            //         ->where('payroll_details.status_id', '=', 9)
            //         ->orWhere('payroll_details.status_id', '=', 10);
            // });

        $query
            ->select(
                'beneficiaries.*',
                'payroll_details.payroll_id as payroll_id',
                'payroll_details.id as payroll_details_id',
                'payroll_details.total_amount as total_allowance_amount',
                'payroll_details.amount',
                'payroll_details.charge',
                'payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program',
                'mfs',
                'bank'
            );
            if($forExcel){
                return $query->get();
            }

            return $query->paginate($request->per_page);
    }
    public function getPayrollRejectedBeneficiaries($request, $forExcel = false)
    {

        $query = Beneficiary::query()
            ->leftJoin('payroll_details', 'beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
            ->leftJoin('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->where('payrolls.office_id', $request->office_id)
            ->where('payrolls.financial_year_id', $request->financial_year_id)
            ->where('payrolls.installment_schedule_id', $request->installment_id)
            ->where('payrolls.program_id', $request->program_id)
            ->when(!empty($request->beneficiary_ids), function($q)use($request){
                $q->whereIn('beneficiaries.beneficiary_id', $request->beneficiary_ids);
            })
            ->when(!empty($request->nominee_name), function($q)use($request){
                $q->where(function($q) use($request){
                    $q->where('beneficiaries.nominee_en', 'like' , "%".$request->nominee_name."%")
                    ->orWhere('beneficiaries.nominee_bn', 'like' , "%".$request->nominee_name."%");
                });
            })
            ->when(!empty($request->account_number), function($q)use($request){
                $q->where('beneficiaries.account_number', $request->account_number);
            })
            ->when(!empty($request->account_number), function($q)use($request){
                $q->where('beneficiaries.account_number', $request->account_number);
            })
            ->when(!empty($request->nid_number), function($q)use($request){
                $q->where('beneficiaries.verification_number', $request->nid_number);
            })
            ->when(!empty($request->mobile), function($q)use($request){
                $q->where('beneficiaries.mobile', $request->mobile);
            })
            ->when(!empty($request->bank_id), function($q)use($request){
                $q->where('beneficiaries.bank_id', $request->bank_id);
            })
            ->when(!empty($request->mfs_id), function($q)use($request){
                $q->where('beneficiaries.mfs_id', $request->mfs_id);
            })
            ->when(!empty($request->union_id), function($q)use($request){
                $q->where('beneficiaries.permanent_union_id', $request->union_id);
            })
            ->when(!empty($request->thana_id), function($q)use($request){
                $q->where('beneficiaries.permanent_thana_id', $request->thana_id);
            })
            ->when(!empty($request->permanent_pourashava_id), function($q)use($request){
                $q->where('beneficiaries.permanent_permanent_pourashava_id', $request->permanent_pourashava_id);
            })
            ->when(!empty($request->ward_id), function($q)use($request){
                $q->where('beneficiaries.permanent_ward_id', $request->ward_id);
            })
            ->where(function ($query) {
                $query->where('payroll_details.is_set', '=', 1)
                    ->where('payroll_details.status_id', '=', 9)
                    ->orWhere('payroll_details.status_id', '=', 10);
            });

            $user = auth()->user();
            if ($user->unions()->exists()) {
                $query->whereIn('permanent_union_id', $user->unions()->pluck('id'));
            }

        $query
            ->select(
                'beneficiaries.*',
                'payroll_details.payroll_id as payroll_id',
                'payroll_details.id as payroll_details_id',
                'payroll_details.total_amount as total_allowance_amount',
                'payroll_details.amount',
                'payroll_details.charge',
                'payroll_details.status_id'
            )
            ->with(
                'permanentUpazila',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUnion',
                'permanentPourashava',
                'permanentWard',
                'program',
                'mfs',
                'bank'
            );
            if($forExcel){
                return $query->get();
            }

            return $query->paginate($request->per_page);
    }
    public function getActiveInstallments($program_id, $financial_year_id)
    {
        return PayrollInstallmentSetting::query()
            ->join('payroll_installment_schedules', 'payroll_installment_schedules.id', '=', 'payroll_installment_settings.installment_schedule_id')
            ->select('payroll_installment_schedules.*')
            ->where('payroll_installment_settings.program_id', $program_id)
            ->where('payroll_installment_settings.financial_year_id', $financial_year_id)
            ->orderBy('payroll_installment_schedules.id')
            ->get();
    }
    
    public function getInstallments($program_id)
    {
        return PayrollInstallmentSchedule::query()
            ->where('payment_cycle', AllowanceProgram::find($program_id)?->payment_cycle)
            ->orderBy('id')
            ->get();
    }
    private function applyLocationFilter($query, $request, $shouldCheckUnionAccess = true)
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        $locationType = $user->assign_location?->type;
        $program_id = $request->query('program_id');
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

        if ($division_id && $division_id > 0) {
            $query = $query->where('allotments.division_id', $division_id);
        }
        if ($district_id && $district_id > 0) {
            $query = $query->where('allotments.district_id', $district_id);
        }
        if ($city_corp_id && $city_corp_id > 0) {
            $query = $query->where('allotments.city_corp_id', $city_corp_id);
        }
        if ($district_pourashava_id && $district_pourashava_id > 0) {
            $query = $query->where('allotments.district_pourashava_id', $district_pourashava_id);
        }
        if ($upazila_id && $upazila_id > 0) {
            $query = $query->where('allotments.upazila_id', $upazila_id);
        }
        if ($pourashava_id && $pourashava_id > 0) {
            $query = $query->where('allotments.pourashava_id', $pourashava_id);
        }
        if ($thana_id && $thana_id > 0) {
            $query = $query->where('allotments.thana_id', $thana_id);
        }
        if ($union_id && $union_id > 0) {
            $query = $query->where('allotments.union_id', $union_id);
        }
        if ($ward_id && $ward_id > 0) {
            $query = $query->where('allotments.ward_id', $ward_id);
        }

        $program = AllowanceProgram::find($program_id);
        if($user->office_id && in_array($user->office->office_type, [8,9,10,11,35])){
            if($program->is_office_wise_budget){
                $query->where('allotments.office_id', $user->office_id);
            }
        }

        // if ($user->userWards()->exists()) {
        //     $query->whereIn('allotments.ward_id', $user->userWards()->pluck('id'));
        // }



        if ($program->is_office_wise_budget != 1) {
            $union_ids = $user->unions()->where('type', 'union')->pluck('id');
            $pouro_ids = $user->unions()->where('type', 'pouro')->pluck('id');
            if(count($union_ids) > 0 && count($pouro_ids) > 0){
                $query->where(function($q)use($union_ids, $pouro_ids){
                    $q->whereIn('allotments.union_id', $union_ids)->orWhereIn('allotments.pourashava_id', $pouro_ids);
                });
            }elseif(count($union_ids) > 0){
                $query->whereIn('allotments.union_id', $union_ids);
            }elseif(count($pouro_ids) > 0){
                $query->whereIn('allotments.pourashava_id', $pouro_ids);
            }
        }

        return $query;
    }
    private function countClassWiseActiveBeneficiaries($allotment, $request): int
    {
        $gender_id = (array) $request->gender_id ?? [];
        $query = Beneficiary::whereHas('verifyLogs', function($q)use($request){
            return $q->where('financial_year_id', $request->financial_year_id);
        });

        $query = $query->where('program_id', $allotment->program_id);
        $query = $query->where('status', 1);

        if ($allotment->city_corp_id) {
            $query = $query->where('permanent_city_corp_id', $allotment->city_corp_id);
        }

        if ($allotment->district_pourashava_id) {
            $query = $query->where('permanent_district_pourashava_id', $allotment->district_pourashava_id);
        }

        if ($allotment->upazila_id) {
            $query = $query->where('permanent_upazila_id', $allotment->upazila_id);
        }

        if ($allotment->pourashava_id) {
            $query = $query->where('permanent_pourashava_id', $allotment->pourashava_id);
        }

        if ($allotment->thana_id) {
            $query = $query->where('permanent_thana_id', $allotment->thana_id);
        }

        if ($allotment->union_id) {
            $query = $query->where('permanent_union_id', $allotment->union_id);
        }

        if ($allotment->ward_id) {
            $query = $query->where('permanent_ward_id', $allotment->ward_id);
        }
        if ($allotment->office_id) {
            $ward_ids = $allotment->office->wards()->pluck('ward_id');
            $query = $query->whereIn('permanent_ward_id', $ward_ids);
        }
        if ($allotment->type_id) {
            $query = $query->where('type_id', $allotment->type_id);
        }
        if ($gender_id) {
            $query = $query->whereIn('beneficiaries.gender_id', $gender_id);
        }
        return $query->whereNotNull('payment_start_date')->whereNotNull('payment_start_date')->count();
    }
    private function countActiveBeneficiaries($allotmentArea, $request): int
    {
        $gender_id = (array) $request->gender_id ?? [];
        // $type_id = (array) $request->class_id ?? [];
        $query = Beneficiary::query()->whereHas('verifyLogs', function($q)use($request){
            return $q->where('financial_year_id', $request->financial_year_id);
        });

        $query = $query->where('program_id', $allotmentArea->program_id);
        $query = $query->where('status', 1);

        if ($allotmentArea->city_corp_id) {
            $query = $query->where('permanent_city_corp_id', $allotmentArea->city_corp_id);
        }

        if ($allotmentArea->district_pourashava_id) {
            $query = $query->where('permanent_district_pourashava_id', $allotmentArea->district_pourashava_id);
        }

        if ($allotmentArea->upazila_id) {
            $query = $query->where('permanent_upazila_id', $allotmentArea->upazila_id);
        }

        if ($allotmentArea->pourashava_id) {
            $query = $query->where('permanent_pourashava_id', $allotmentArea->pourashava_id);
        }

        if ($allotmentArea->thana_id) {
            $query = $query->where('permanent_thana_id', $allotmentArea->thana_id);
        }

        if ($allotmentArea->union_id) {
            $query = $query->where('permanent_union_id', $allotmentArea->union_id);
        }

        if ($allotmentArea->ward_id) {
            $query = $query->where('permanent_ward_id', $allotmentArea->ward_id);
        }
        if ($allotmentArea->office_id) {
            $ward_ids = $allotmentArea->office->wards()->pluck('ward_id');
            $query = $query->whereIn('permanent_ward_id', $ward_ids);
        }
        if ($gender_id) {
            $query = $query->whereIn('beneficiaries.gender_id', $gender_id);
        }
        if ($allotmentArea->type_id) {
            $query = $query->where('beneficiaries.type_id', $allotmentArea->type_id);
        }
        // if ($type_id) {
        //     $query = $query->whereIn('beneficiaries.type_id', $type_id);
        // }
        return $query->whereNotNull('payment_start_date')->whereNotNull('payment_start_date')->count();
    }
    public function getAllotmentAreaList(Request $request)
    {
        $installment_id = $request->query('installment_schedule_id');
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $perPage = $request->query('perPage', 100);

        $program = AllowanceProgram::find($program_id);

        $query = Allotment::query()
            ->leftJoin('payrolls', 'allotments.id', '=', 'payrolls.allotment_id');

        // $query = $query->where(function ($query) {
        //     return $query->where('payrolls.is_checked', 0)
        //         ->orWhere('payrolls.is_checked', null)
        //         ->orWhere('payrolls.is_rejected', 1);
        // });

        if ($program_id) {
            $query = $query->where('allotments.program_id', $program_id);
        }

        if ($financial_year_id) {
            $query = $query->where('allotments.financial_year_id', $financial_year_id);
        }

        $query = $this->applyLocationFilter($query, $request, !((bool) $program->is_office_wise_budget));
        $query = $query
            ->selectRaw('allotments.*, payrolls.allotment_id, payrolls.total_beneficiaries as saved_beneficiaries, payrolls.is_rejected as is_rejected')
            ->with('upazila', 'cityCorporation', 'districtPourosova', 'location')->groupBy('allotments.id');


        return $query->orderBy('location_id')->paginate($perPage)->through(function ($allotmentArea) use ($installment_id, $request) {
            $allotmentArea->active_beneficiaries = $this->countActiveBeneficiaries($allotmentArea, $request);
            $allotmentArea->installment_id = $installment_id;
            $payroll = Payroll::where('allotment_id', $allotmentArea->id)->first();

            if (!$payroll) {
                $allotmentArea->total_sent = 0;
                $allotmentArea->total_approved = 0;
                $allotmentArea->total_rollback = 0;
            } else {
                $counts = PayrollDetail::where('payroll_id', $payroll->id)
                    ->selectRaw("
                        COUNT(CASE WHEN is_set = 1 THEN 1 END) as total_sent,
                        COUNT(CASE WHEN is_set = 1 AND status_id = 2 THEN 1 END) as total_approved,
                        COUNT(CASE WHEN is_set = 0 AND status_id = 3 THEN 1 END) as total_rollback
                    ")
                    ->first();

                $allotmentArea->total_sent = $counts->total_sent ?? 0;
                $allotmentArea->total_approved = $counts->total_approved ?? 0;
                $allotmentArea->total_rollback = $counts->total_rollback ?? 0;
            }

            return $allotmentArea;
        });
    }

    public function getAllotmentClassList(Request $request)
    {
        $installment_id = $request->query('installment_schedule_id');
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $perPage = $request->query('perPage', 100);
        $type_ids = (array) $request->class_id ?? [];

        $query = Allotment::query()
            ->leftJoin('payrolls', 'allotments.id', '=', 'payrolls.allotment_id');

        // $query = $query->where(function ($query) {
        //     return $query->where('payrolls.is_checked', 0)
        //         ->orWhere('payrolls.is_checked', null)
        //         ->orWhere('payrolls.is_rejected', 1);
        // });

        if ($program_id) {
            $query = $query->where('allotments.program_id', $program_id);
        }

        if ($financial_year_id) {
            $query = $query->where('allotments.financial_year_id', $financial_year_id);
        }

        $query->whereNotNull('type_id');
        $query->when(!empty($type_ids), function($q)use($type_ids){
            $q->whereIn('type_id', $type_ids);
        });

        $query = $this->applyLocationFilter($query, $request, false);
        $query = $query
            ->selectRaw('allotments.*, payrolls.allotment_id, payrolls.total_beneficiaries as saved_beneficiaries, payrolls.is_rejected as is_rejected')
            ->with('upazila', 'cityCorporation', 'districtPourosova', 'location')->groupBy('allotments.id');

        return $query->orderBy('location_id')->paginate($perPage)->through(function ($allotment) use ($installment_id, $request) {
            $allotment->active_beneficiaries = $this->countClassWiseActiveBeneficiaries($allotment, $request);
            $allotment->installment_id = $installment_id; // Add the installment ID to the resource
            $payroll = Payroll::where('allotment_id', $allotment->id)->first();

            if (!$payroll) {
                $allotment->total_sent = 0;
                $allotment->total_approved = 0;
                $allotment->total_rollback = 0;
            } else {
                $counts = PayrollDetail::where('payroll_id', $payroll->id)
                    ->selectRaw("
                        COUNT(CASE WHEN is_set = 1 THEN 1 END) as total_sent,
                        COUNT(CASE WHEN is_set = 1 AND status_id = 2 THEN 1 END) as total_approved,
                        COUNT(CASE WHEN is_set = 0 AND status_id = 3 THEN 1 END) as total_rollback
                    ")
                    ->first();

                $allotment->total_sent = $counts->total_sent ?? 0;
                $allotment->total_approved = $counts->total_approved ?? 0;
                $allotment->total_rollback = $counts->total_rollback ?? 0;
            }

            return $allotment;
        });
    }
    public function getAllotmentAreaStatistics($request, int $allotment_id): array
    {
        $installment_id = $request->installment_schedule_id;
        $program_id = $request->program_id;
        $fiscal_year_id = $request->financial_year_id;
        $financial = FinancialYear::where('id', $fiscal_year_id)->get(['financial_year', 'start_date', 'end_date'])->first();
        $allotment = Allotment::query()->with(['location','type'])->findOrFail($allotment_id);
        $query = Payroll::query()
            ->join('payroll_details', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->where('payrolls.allotment_id', $allotment_id)
            ->where('payrolls.installment_schedule_id', $installment_id)
            ->where('payrolls.is_rejected', 0)
            ->where('payroll_details.status_id', '!=', 3)
            ->get();

        $set_beneficiaries = $query->where('is_set', 1)->count();

        $program = AllowanceProgram::where('id', $program_id)->get(['id', 'name_en', 'name_bn', 'payment_cycle'])->first();

        $sum_of_all_previous_installments = $this->previousAllPayment($installment_id, $allotment_id, $program_id, $fiscal_year_id);

        $installment = $this->getCurrentInstallment($installment_id, $program);
        $amount = $installment ? $installment['amount'] : 0;
        $totalBeneficiary = $this->countActiveBeneficiaries($allotment, $request);
        $annualPayrollEligibleAmount = $allotment->total_amount;

        $data = $this->calculateAmount($amount, $allotment, $program_id, $fiscal_year_id, $program->payment_cycle);
        $payroll_eligible_amount = $data['payroll_eligible_amount'];
        $installmentSchedule = $this->getPaymentCycleDates($installment_id, $request->financial_year_id);

        return [
            'allotment_area' => $allotment->location,
            'office' => $allotment->office,
            'type' => $allotment->type,
            'program' => $program,
            'installment' => $installment,
            'payment_cycle_start_date' => $installmentSchedule->start_date,
            'payment_cycle_end_date' => $installmentSchedule->end_date,
            'allocated_beneficiaries' => $allotment->total_beneficiaries ?? 0,
            'total_beneficiary' => $totalBeneficiary,
            'sum_of_all_previous_installments' => round($sum_of_all_previous_installments, 2),
            'set_beneficiaries' => $set_beneficiaries ?? 0,
            'payroll_eligible_amount' => round(($payroll_eligible_amount - $sum_of_all_previous_installments), 2) ?? 0,
            'annual_payroll_eligible_amount' => round($annualPayrollEligibleAmount, 2) ?? 0,
            'remaining_amount' => $payroll_eligible_amount ?? 0,
        ];
    }
    public function getActiveBeneficiaries(Request $request, int $allotment_id)
    {
        $gender_id = (array) $request->gender_id ?? [];
        $type_id = (array) $request->class_id ?? [];
        $perPage = (int) $request->perPage ?? 10;
        $page = (int) $request->page ?? 1;
        $installment_schedule_id = $request->installment_schedule_id;
        $allotmentArea = Allotment::findOrFail($allotment_id);
        $currentInstallment = $this->getPaymentCycleDates($installment_schedule_id, $request->financial_year_id);
        // Get the previous installment
        $prevInstallment = DB::table('payroll_installment_schedules')
            ->where('installment_number', '<', $currentInstallment->installment_number)
            ->where('payment_cycle', '=', $currentInstallment->payment_cycle)
            ->first(['id', 'installment_name', 'installment_number', 'payment_cycle']);

        if ($prevInstallment) {
            $previousInstallment = $this->getPaymentCycleDates($prevInstallment->id, $request->financial_year_id);
        } else {
            $previousInstallment = (object) [
                'start_date' => Carbon::now()->startOfYear()->addMonths(6)->toDateString(),
                'end_date' => Carbon::now()->endOfYear()->addMonths(6)->subDay()->toDateString(),
                'installment_number' => 0,
                'payment_cycle' => $currentInstallment->payment_cycle
            ];
        }
        $query = Beneficiary::query()
            ->leftJoin('payroll_details', function ($join) {
                $join->on('beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
                    ->where('payroll_details.is_set', '=', 0);
            })
            ->leftJoin('allowance_programs', 'beneficiaries.program_id', '=', 'allowance_programs.id')
            ->leftJoin('allowance_program_ages', function ($join) {
                $join->on('beneficiaries.program_id', '=', 'allowance_program_ages.allowance_program_id')
                    ->on('beneficiaries.gender_id', '=', 'allowance_program_ages.gender_id');
            })
            ->leftJoin('allowance_program_amounts', function ($join) {
                $join->on('allowance_program_amounts.allowance_program_id', '=', 'beneficiaries.program_id')
                    ->on('allowance_program_amounts.type_id', '=', 'beneficiaries.type_id');
            })
            ->where('beneficiaries.program_id', $allotmentArea->program_id)
            ->where('beneficiaries.payment_start_date', '<', $currentInstallment->end_date)
            // ->where('beneficiaries.status', 1)
            ->whereHas('verifyLogs', function($q)use($request){
                return $q->where('financial_year_id', $request->financial_year_id);
            })
            ->where('beneficiaries.status', 1)
            ->whereNotExists(function ($subquery) {
                $subquery->select(DB::raw(1))
                    ->from('payroll_details')
                    ->whereColumn('payroll_details.beneficiary_id', 'beneficiaries.beneficiary_id')
                    ->where('payroll_details.is_set', '=', 1);
            })->addSelect([
                    'beneficiaries.*',
                    DB::raw('
                CASE
                    WHEN EXISTS (
                        SELECT 1
                        FROM payroll_details pd
                        WHERE pd.beneficiary_id = beneficiaries.beneficiary_id
                        AND pd.status_id = 3
                    ) THEN 1
                    ELSE 0
                END as isRejected
            ')
                ]);

        if ($allotmentArea->city_corp_id) {
            $query = $query->where('beneficiaries.permanent_city_corp_id', $allotmentArea->city_corp_id);
        }
        if ($allotmentArea->district_pourashava_id) {
            $query = $query->where('beneficiaries.permanent_district_pourashava_id', $allotmentArea->district_pourashava_id);
        }
        if ($allotmentArea->upazila_id) {
            $query = $query->where('beneficiaries.permanent_upazila_id', $allotmentArea->upazila_id);
        }
        if ($allotmentArea->pourashava_id) {
            $query = $query->where('beneficiaries.permanent_pourashava_id', $allotmentArea->pourashava_id);
        }
        if ($allotmentArea->thana_id) {
            $query = $query->where('beneficiaries.permanent_thana_id', $allotmentArea->thana_id);
        }
        ;
        if ($allotmentArea->union_id) {
            $query = $query->where('beneficiaries.permanent_union_id', $allotmentArea->union_id);
        }
        if ($allotmentArea->ward_id) {
            $query = $query->where('beneficiaries.permanent_ward_id', $allotmentArea->ward_id);
        }
        if ($allotmentArea->office_id) {
            $ward_ids = $allotmentArea->office->wards()->pluck('ward_id');
            $query = $query->whereIn('beneficiaries.permanent_ward_id', $ward_ids);
        }
        if ($allotmentArea->type_id) {
            $query = $query->where('beneficiaries.type_id', $allotmentArea->type_id);
        }
        if ($gender_id) {
            $query = $query->whereIn('beneficiaries.gender_id', $gender_id);
        }
        // if ($type_id) {
        //     $query = $query->whereIn('beneficiaries.type_id', $type_id);
        // }
        $query = $this->getBeneficiaryTrackingStatus($query, $currentInstallment, $previousInstallment);
        $beneficiaries = $this->query($query, $currentInstallment);
        $beneficiaries = $beneficiaries->paginate($perPage);
        $beneficiaries->paginate($perPage);
        $duplicateAcc = $this->getDuplicateAccountNumbers();
        foreach($beneficiaries as $key => $row){
            $beneficiaries[$key]->isDuplicateAccount = in_array($row->account_number, $duplicateAcc);
        }
        return $beneficiaries;
        // return $beneficiaries;
    }

    public function searchActiveBeneficiaries(Request $request, int $allotment_id)
    {

        return $this->getActiveBeneficiaries($request, $allotment_id);

        // $beneficiary_id = $request->beneficiary_id;
        // $nominee_name = $request->nominee_name;
        // $account_number = $request->account_number;
        // $nid = $request->nid;
        // $gender_id = (array) $request->gender_id ?? [];
        // $type_id = (array) $request->class_id ?? [];
        // $remarks_ids = $request->remarks_ids ?? [];
        // $mobile = $request->mobile;
        // $bank_or_mfs = $request->bank_or_mfs;
        // $installment_schedule_id = $request->installment_schedule_id;
        // $allotmentArea = Allotment::findOrFail($allotment_id);
        // $currentInstallment = $this->getPaymentCycleDates($installment_schedule_id);

        // // Get the previous installment
        // $prevInstallment = DB::table('payroll_installment_schedules')
        //     ->where('installment_number', '<', $currentInstallment->installment_number)
        //     ->where('payment_cycle', '=', $currentInstallment->payment_cycle)
        //     ->first(['id', 'installment_name', 'installment_number', 'payment_cycle']);

        // if ($prevInstallment) {
        //     $previousInstallment = $this->getPaymentCycleDates($prevInstallment->id);
        // } else {
        //     $previousInstallment = (object) [
        //         'start_date' => Carbon::now()->startOfYear()->addMonths(6)->toDateString(),
        //         'end_date' => Carbon::now()->endOfYear()->addMonths(6)->subDay()->toDateString(),
        //         'installment_number' => 0,
        //         'payment_cycle' => $currentInstallment->payment_cycle
        //     ];
        // }

        // $query = Beneficiary::query()
        //     ->leftJoin('payroll_details', function ($join) {
        //         $join->on('beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
        //             ->where('payroll_details.is_set', '=', 0);
        //     })
        //     ->leftJoin('allowance_programs', 'beneficiaries.program_id', '=', 'allowance_programs.id')
        //     ->leftJoin('allowance_program_ages', function ($join) {
        //         $join->on('beneficiaries.program_id', '=', 'allowance_program_ages.allowance_program_id')
        //             ->on('beneficiaries.gender_id', '=', 'allowance_program_ages.gender_id');
        //     })
        //     ->leftJoin('allowance_program_amounts', function ($join) {
        //         $join->on('allowance_program_amounts.allowance_program_id', '=', 'beneficiaries.program_id')
        //             ->on('allowance_program_amounts.type_id', '=', 'beneficiaries.type_id');
        //     })
        //     ->where('beneficiaries.program_id', $allotmentArea->program_id)
        //     ->where('beneficiaries.payment_start_date', '<', $currentInstallment->end_date)
        //     ->where('beneficiaries.status', 1)
        //     ->whereNotExists(function ($subquery) {
        //         $subquery->select(DB::raw(1))
        //             ->from('payroll_details')
        //             ->whereColumn('payroll_details.beneficiary_id', 'beneficiaries.beneficiary_id')
        //             ->where('payroll_details.is_set', '=', 1);
        //     })->addSelect([
        //             'beneficiaries.*',
        //             DB::raw('
        //         CASE
        //             WHEN EXISTS (
        //                 SELECT 1
        //                 FROM payroll_details pd
        //                 WHERE pd.beneficiary_id = beneficiaries.beneficiary_id
        //                 AND pd.status_id = 3
        //             ) THEN 1
        //             ELSE 0
        //         END as isRejected
        //     ')
        //         ]);

        // if ($allotmentArea->division_id) {
        //     $query = $query->where('beneficiaries.permanent_division_id', $allotmentArea->division_id);
        // }
        // if ($allotmentArea->district_id) {
        //     $query = $query->where('beneficiaries.permanent_district_id', $allotmentArea->district_id);
        // }
        // if ($allotmentArea->city_corp_id) {
        //     $query = $query->where('beneficiaries.permanent_city_corp_id', $allotmentArea->city_corp_id);
        // }
        // if ($allotmentArea->district_pourashava_id) {
        //     $query = $query->where('beneficiaries.permanent_district_pourashava_id', $allotmentArea->district_pourashava_id);
        // }
        // if ($allotmentArea->upazila_id) {
        //     $query = $query->where('beneficiaries.permanent_upazila_id', $allotmentArea->upazila_id);
        // }
        // if ($allotmentArea->pourashava_id) {
        //     $query = $query->where('beneficiaries.permanent_pourashava_id', $allotmentArea->pourashava_id);
        // }
        // if ($allotmentArea->thana_id) {
        //     $query = $query->where('beneficiaries.permanent_thana_id', $allotmentArea->thana_id);
        // }
        // ;
        // if ($allotmentArea->union_id) {
        //     $query = $query->where('beneficiaries.permanent_union_id', $allotmentArea->union_id);
        // }
        // if ($allotmentArea->ward_id) {
        //     $query = $query->where('beneficiaries.permanent_ward_id', $allotmentArea->ward_id);
        // }
        // if ($gender_id) {
        //     $query = $query->whereIn('beneficiaries.gender_id', $gender_id);
        // }
        // if ($type_id) {
        //     $query = $query->whereIn('beneficiaries.type_id', $type_id);
        // }
        // if (!is_null($beneficiary_id)) {
        //     $query = $query->where('beneficiaries.beneficiary_id', $beneficiary_id);
        // }
        // if (!is_null($account_number)) {
        //     $query = $query->where('beneficiaries.account_number', $account_number);
        // }
        // if (!is_null($nominee_name)) {
        //     $query = $query->where(function ($q) use ($nominee_name) {
        //         $q->where('beneficiaries.nominee_en', 'LIKE', '%' . $nominee_name . '%')
        //             ->orWhere('beneficiaries.nominee_bn', 'LIKE', '%' . $nominee_name . '%');
        //     });
        // }
        // if (!is_null($bank_or_mfs)) {
        //     $query = $query->where(function ($q) use ($bank_or_mfs) {
        //         $q->where('beneficiaries.bank_name', 'LIKE', '%' . $bank_or_mfs . '%')
        //             ->orWhere('beneficiaries.mfs_name', 'LIKE', '%' . $bank_or_mfs . '%');
        //     });
        // }
        // if (!is_null($nid)) {
        //     $query = $query->where('beneficiaries.verification_number', $nid);
        // }
        // if (!is_null($mobile)) {
        //     $query = $query->where('beneficiaries.mobile', $mobile);
        // }

        // if (count($remarks_ids) > 0) {
        //     $query = $query->where(function ($q) use ($remarks_ids, $currentInstallment) {
        //         if (in_array(1, $remarks_ids) && $currentInstallment) {
        //             $q = $q->orWhereBetween('approve_date', [$currentInstallment->start_date, $currentInstallment->end_date]);
        //         }
        //         if (in_array(2, $remarks_ids)) {
        //             $q = $q->orWhereExists(function (Builder $qry) {
        //                 $qry->select(DB::raw(1))
        //                     ->from('beneficiary_change_trackings')
        //                     ->whereColumn('beneficiary_change_trackings.beneficiary_id', 'beneficiaries.id')
        //                     ->whereRaw('month(now()) = month(beneficiary_change_trackings.created_at)');
        //             });
        //         }
        //         if (in_array(3, $remarks_ids)) {

        //             $q = $q->orWhere('is_replaced', true);
        //         }
        //         if (in_array(4, $remarks_ids)) {
        //             $q->orWhere(function ($subQuery) use ($currentInstallment) {
        //                 $subQuery->whereNotNull('approve_date')
        //                     ->where(
        //                         'approve_date',
        //                         '<',
        //                         $currentInstallment->start_date
        //                     );
        //             });
        //         }
        //         return $q;
        //     });
        // }

        // $query = $this->getBeneficiaryTrackingStatus($query, $currentInstallment, $previousInstallment);
        // $beneficiaries = $this->query($query, $currentInstallment);
        // return $beneficiaries;
    }
    private function getOfficeIdByLocationId($locationId)
    {
        // Start with the given location ID
        $currentLocationId = $locationId;

        while ($currentLocationId) {
            // Check if an office exists for the current location
            $office = Office::where('assign_location_id', $currentLocationId)->first();

            if ($office) {
                // If an office is found, return it
                return $office->id;
            }

            // Step up to the parent location
            $currentLocationId = Location::where('id', $currentLocationId)->value('parent_id');
        }

        // Return null if no office is found for the entire hierarchy
        return null;
    }
    public function processPayroll(SavePayrollRequest $request)
    {
        $total_beneficiaries = 0;
        $sub_total_amount = 0;
        $total_charge = 0;
        $total_amount = 0;
        $payroll_id = null;

        try {
            $payroll_eligible_amount = (int) $request->input('payroll_eligible_amount');
            $allotment_id = $request->post('allotment_id');
            $allotment = Allotment::findOrFail($allotment_id);
            $office_id = $this->getOfficeIdByLocationId($allotment->location_id);
            $max_beneficiary_limit = $allotment->total_beneficiaries;
            $validatedPayrollDetailsData = $request->validated('payroll_details');
            $total_payroll_amount = collect($validatedPayrollDetailsData)->sum('amount');

            // Check for beneficiary limit and budget amount
            if (count($validatedPayrollDetailsData) > $max_beneficiary_limit) {
                return [
                    'message' => 'Maximum beneficiary limit reached',
                    'status' => '210',
                ];
            }

            if ($total_payroll_amount > $payroll_eligible_amount) {
                return [
                    'message' => 'Budget amount has been exceeded',
                    'status' => '211',
                ];
            }

            $payroll = Payroll::query()
                ->where('program_id', $allotment->program_id)
                ->where('financial_year_id', $allotment->financial_year_id)
                ->where('allotment_id', $allotment_id)
                ->where('installment_schedule_id', $request->post('installment_schedule_id'))
                // ->where(function ($query) {
                //     $query->where('is_approved', 0)
                //         ->where('is_rejected', 0)
                //         ->where('is_verified', 0);
                // })
                ->first();

            DB::beginTransaction();

            if ($payroll) {
                // Delete all match records from log first
                $details_id = PayrollDetail::where('payroll_id', $payroll->id)->where('is_set', 0)->pluck('id')->toArray();
                if (!empty($details_id)) {
                    BeneficiaryPayrollPaymentStatusLog::whereIn('payroll_details_id', $details_id)->where('status_id', 1)->forceDelete();
                }
                // Delete all match records from details first
                $payroll->payrollDetails()->where('is_set', 0)->forceDelete();
                $remainingBeneficiary = $max_beneficiary_limit - $payroll->payrollDetails()->count();

                if (count($validatedPayrollDetailsData) > $remainingBeneficiary) {
                    return [
                        'message' => 'Beneficiary not found',
                        'status' => '212',
                    ];
                }

                $total_beneficiaries = $payroll->payrollDetails()->count();
                $sub_total_amount = $payroll->payrollDetails()->sum('amount');
                $total_charge = $payroll->payrollDetails()->sum('charge');
                $total_amount = $sub_total_amount + $total_charge;

                // Update the payroll with the new totals
                $payroll->update([
                    'total_beneficiaries' => $total_beneficiaries,
                    'sub_total_amount' => $sub_total_amount,
                    'total_charge' => $total_charge,
                    'total_amount' => $total_amount,
                ]);
            } else {
                $payroll = Payroll::create([
                    'program_id' => $request->program_id,
                    'financial_year_id' => $request->financial_year_id,
                    'office_id' => $office_id ?: auth()->user()->id,
                    'allotment_id' => $allotment_id,
                    'installment_schedule_id' => $request->installment_schedule_id,
                    'total_beneficiaries' => 0,
                    'sub_total_amount' => 0,
                    'total_charge' => 0,
                    'total_amount' => 0,
                ]);
            }

            foreach ($validatedPayrollDetailsData as $payrollDetailsData) {
                $dtlAmount = (float) $payrollDetailsData['amount'] ?? 0;
                $dtlCharge = (float) $payrollDetailsData['charge'] ?? 0;
                $dtlTotalAmount = (float) $payrollDetailsData['total'];

                $payrollDetailsData['payroll_id'] = $payroll->id;
                $payrollDetailsData['charge'] = $dtlCharge;
                $payrollDetailsData['total_amount'] = $dtlTotalAmount;
                $payrollDetailsData['status_id'] = 1;
                $payrollDetailsData['status'] = 'Pending';
                $payrollDetailsData['updated_by_id'] = auth()->user()->id;

                $total_beneficiaries++;
                $sub_total_amount += $dtlAmount;
                $total_charge += $dtlCharge;
                $total_amount += $dtlTotalAmount;

                $payrollDetail = PayrollDetail::create($payrollDetailsData);

                BeneficiaryPayrollPaymentStatusLog::create([
                    'beneficiary_id' => $payrollDetailsData['beneficiary_id'],
                    'payroll_details_id' => $payrollDetail->id,
                    'created_by' => auth()->user()->id,
                    'created_at' => now(),
                    'status_id' => 1,
                ]);
            }

            // Final update after processing all payroll details
            $payroll->update([
                'total_beneficiaries' => $total_beneficiaries,
                'sub_total_amount' => $sub_total_amount,
                'total_charge' => $total_charge,
                'total_amount' => $total_amount,
            ]);

            DB::commit();
            return $payroll;
        } catch (\Illuminate\Database\QueryException $e) {
            DB::rollBack();
            throw $e;
            // Handle the duplicate entry exception
            if ($e->getCode() == 23000) {
                abort(409,'Duplicate entry detected for payroll_id');
                // return response()->json([
                //     'message' => 'Duplicate entry detected for payroll_id',
                //     'error' => $e->getMessage(),
                // ], 409); // Conflict HTTP status
            }
            abort(500,'An error occurred');

            // return response()->json([
            //     'message' => 'An error occurred',
            //     'error' => $e->getMessage(),
            // ], 500);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $th->getMessage();
        }
    }
    public function rollback($allotment_id)
    {
        try {
            $payroll = Payroll::where('allotment_id', $allotment_id)->first();
            if (!empty($payroll->payrollDetails)) {
                foreach ($payroll->payrollDetails as $p_detail) {
                    BeneficiaryPayrollPaymentStatusLog::where('beneficiary_id', $p_detail->beneficiary_id)->update(['status_id' => 1]);
                    $p_detail->status = "Pending";
                    $p_detail->status_id = 1;
                    $p_detail->save();
                }
                $payroll->is_rejected = 0;
                $payroll->rejected_by_id = null;
                $payroll->rejected_at = null;
                $payroll->rejected_note = null;
                $payroll->rejected_doc = 0;
                $payroll->save();
            }
            return $payroll;
        } catch (\Exception $exception) {
            throw new \Exception($exception->getMessage());
        }
    }
    public function previewBeneficiaries(Request $request)
    {
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $installment_schedule_id = $request->query('installment_schedule_id');

        $query = Beneficiary::query()
            ->join('payroll_details', 'beneficiaries.beneficiary_id', '=', 'payroll_details.beneficiary_id')
            ->join('payrolls', 'payrolls.id', '=', 'payroll_details.payroll_id')
            ->join('allotments', 'allotments.id', '=', 'payrolls.allotment_id');

        $query = $query->where(function ($query) {
            return $query
                // ->where('payrolls.is_checked', 0)
                ->where('payroll_details.is_set', '!=', 1)
                ->where('payroll_details.status_id', '!=', 3);
        });

        $query = $query->where('payrolls.program_id', $program_id);
        $query = $query->where('payrolls.financial_year_id', $financial_year_id);
        $query = $query->where('payrolls.installment_schedule_id', $installment_schedule_id);

        $query->when(!empty($request->search), function($q)use($request){
            $search = $request->search;
            $q->where(function($q) use($search){
                $q->where('beneficiaries.beneficiary_id', 'like', "%$search%")
                ->orWhere('name_en', 'like', "%$search%")
                ->orWhere('name_bn', 'like', "%$search%")
                ->orWhere('mother_name_en', 'like', "%$search%")
                ->orWhere('mother_name_bn', 'like', "%$search%")
                ->orWhere('father_name_en', 'like', "%$search%")
                ->orWhere('father_name_bn', 'like', "%$search%")
                ->orWhere('mobile', 'like', "%$search%")
                ->orWhere('account_number', 'like', "%$search%");
            });
        });

        $query = $this->applyLocationFilter($query, $request);

        $query = $query
            ->selectRaw('beneficiaries.*, payrolls.allotment_id as allotment_id,payroll_details.payroll_id, payroll_details.id as payroll_detail_id, payroll_details.amount, payroll_details.charge, payroll_details.total_amount')
            ->with('permanentCityCorporation', 'permanentDistrictPourashava', 'permanentUpazila', 'permanentPourashava', 'permanentUnion', 'permanentWard');
        return $query->orderBy('beneficiaries.permanent_city_corp_id')
            ->orderBy('beneficiaries.permanent_district_pourashava_id')
            ->orderBy('beneficiaries.permanent_upazila_id')
            ->orderBy('beneficiaries.permanent_pourashava_id')
            ->orderBy('beneficiaries.permanent_thana_id')
            ->orderBy('beneficiaries.permanent_union_id')
            ->orderBy('beneficiaries.permanent_ward_id')
            ->get();
    }
    public function submitPayroll(SubmitPayrollRequest $request)
    {
        DB::beginTransaction();
        try {
            $requestData = $request->validated();
            $payroll_ids = array_unique(Arr::pluck($requestData['payroll_details'], 'payroll_id'));
            $payroll_details_ids = array_unique(Arr::pluck($requestData['payroll_details'], 'id'));
            $payrolls = Payroll::whereIn('id', $payroll_ids)
                ->with('allotment', 'payrollDetails')
                // ->where(function ($query) {
                //     $query->where('is_approved', 0)
                //         ->where('is_rejected', 0)
                //         ->where('is_verified', 0);
                // })
                ->get();
            if (!empty($payrolls)) {
                PayrollDetail::whereIn('id', $payroll_details_ids)->update(['is_set' => 1]);
                foreach ($payrolls as $payroll) {
                    $allotment = $payroll->allotment;
                    $detailsCount = collect($requestData['payroll_details'])->where('payroll_id', $payroll->id)->count();
                    $is_checked = $detailsCount === $allotment->total_beneficiaries ? 1 : 0;
                    $user = auth()->user();
                    $payroll->is_submitted = 1;
                    $payroll->submitted_by_id = $user?->id;
                    $payroll->is_checked = $is_checked;
                    $payroll->submitted_at = now();
                    $payroll->save();
                    DB::commit();
                }
                return response()->json(['message' => 'Payroll submitted successfully', 'payroll' => $payrolls], 200);
            } else {
                throw new HttpResponseException(response()->json([
                    'message' => 'Payroll does not exist',
                ], 200));
            }
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function approvalList(Request $request)
    {
        $program_id = $request->program_id;
$financial_year_id = $request->financial_year_id;
$installment_schedule_id = $request->installment_id;
$office_id = $request->office_id;
$status_id = $request->status_id;
$division_id = $request->division_id;
$district_id = $request->district_id;
$upazila_id = $request->upazila_id;
$city_corp_id = $request->city_corp_id;
$district_pourashava_id = $request->district_pourashava_id;
$perPage = $request->perPage ?? 10;

// Subquery to count payroll details
$detailCountsSub = DB::table('payroll_details')
    ->join('payrolls', 'payroll_details.payroll_id', '=', 'payrolls.id')
    ->where('payroll_details.is_set', 1)
    ->whereNull('payroll_details.deleted_at')
    ->selectRaw('
        payrolls.program_id,
        payrolls.financial_year_id,
        payrolls.installment_schedule_id,
        payrolls.office_id,
        SUM(CASE WHEN status_id = 2 THEN 1 ELSE 0 END) AS approve_count,
        SUM(CASE WHEN status_id = 1 THEN 1 ELSE 0 END) AS waiting_count,
        SUM(CASE WHEN status_id IN (9,10) THEN 1 ELSE 0 END) AS rollback_count
    ')
    ->groupBy([
        'payrolls.program_id',
        'payrolls.financial_year_id',
        'payrolls.installment_schedule_id',
        'payrolls.office_id'
    ]);

$detailCountsSql = $detailCountsSub->toSql();
$detailCountsBindings = $detailCountsSub->getBindings();

$query = Payroll::query()
    ->selectRaw('
        payrolls.program_id,
        payrolls.financial_year_id,
        payrolls.installment_schedule_id,
        payrolls.office_id,
        MAX(payrolls.allotment_id) AS allotment_id,
        SUM(allotments.total_beneficiaries) AS total_allotment_beneficiaries,
        SUM(payrolls.total_beneficiaries) AS total_beneficiaries,
        SUM(payrolls.total_charge) AS total_charge,
        SUM(payrolls.sub_total_amount) AS sub_total_amount,
        SUM(payrolls.total_amount) AS total_amount,
        detail_counts.approve_count,
        detail_counts.waiting_count,
        detail_counts.rollback_count
    ')
    ->join('allotments', 'payrolls.allotment_id', '=', 'allotments.id')
    ->join(DB::raw("({$detailCountsSql}) as detail_counts"), function ($join) {
        $join->on('payrolls.program_id', '=', 'detail_counts.program_id')
            ->on('payrolls.financial_year_id', '=', 'detail_counts.financial_year_id')
            ->on('payrolls.installment_schedule_id', '=', 'detail_counts.installment_schedule_id')
            ->on('payrolls.office_id', '=', 'detail_counts.office_id');
    })
    ->where('payrolls.is_submitted', 1)
    ->when($request->exclued_zero_waiting == 1, fn ($q)=> $q->where('detail_counts.waiting_count', '>=', 1))
    ->with([
        'office',
        'program',
        'installmentSchedule',
        'financialYear'
    ])
    ->groupBy([
        'payrolls.program_id',
        'payrolls.financial_year_id',
        'payrolls.installment_schedule_id',
        'payrolls.office_id',
        'detail_counts.approve_count',
        'detail_counts.waiting_count',
        'detail_counts.rollback_count'
    ]);

// Inject subquery bindings
$query->addBinding($detailCountsBindings, 'join');

// Apply filters
if ($program_id) $query->where('payrolls.program_id', $program_id);
if ($office_id) $query->where('payrolls.office_id', $office_id);
if ($financial_year_id) $query->where('payrolls.financial_year_id', $financial_year_id);
if ($installment_schedule_id) $query->where('payrolls.installment_schedule_id', $installment_schedule_id);

$query->when($upazila_id, fn($q) => $q->where('allotments.upazila_id', $upazila_id));
$query->when($district_pourashava_id, fn($q) => $q->where('allotments.district_pourashava_id', $district_pourashava_id));
$query->when($city_corp_id, fn($q) => $q->where('allotments.city_corp_id', $city_corp_id));
$query->when($district_id, fn($q) => $q->where('allotments.district_id', $district_id));
$query->when($division_id, fn($q) => $q->where('allotments.division_id', $division_id));

// Apply status filters
$query = $this->applyStatusFilter($query, $status_id);

$verification_settings = PayrollVerificationSetting::query()->first();
$is_verification_required = $verification_settings?->verification_type;

// Paginate and transform
return $query->paginate($perPage)->through(function ($payroll) use ($request, $is_verification_required) {
    $allotment = Allotment::with([
        'division',
        'district',
        'upazila',
        'cityCorporation',
        'districtPourosova',
        'location'
    ])->find($payroll->allotment_id);

    $this->applyLocationFilter1($allotment, $request);

    $payroll->division = $allotment?->division;
    $payroll->district = $allotment?->district;
    $payroll->upazila = $allotment?->upazila;
    $payroll->cityCorporation = $allotment?->cityCorporation;
    $payroll->districtPourosova = $allotment?->districtPourosova;
    $payroll->location = $allotment?->location;

    $payroll->is_verification_required = $is_verification_required === 'verification_process';

    return $payroll;
});

        

    }
    private function applyStatusFilter($query, $status_id)
    {
        $status_id = (int) $status_id;
        switch ($status_id) {
            case 1:
                // Pending: is_submitted = 1 and others = 0
                $query->where('is_submitted', 1)
                    ->where('is_approved', 0)
                    ->where('is_rejected', 0)
                    ->where('is_verified', 0);
                break;
            case 2:
                // Approved: is_approved = 1 and is_verified = 1, is_rejected = 0
                $query->where('is_approved', 1)
                    ->where('is_verified', 1)
                    ->where('is_rejected', 0);
                break;
            case 3:
                // Verified: is_verified = 1 and is_rejected = 0, is_approved = 0
                $query->where('is_verified', 1)
                    ->where('is_rejected', 0)
                    ->where('is_approved', 0);
                break;
            case 4:
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
        $locationType = $user->assign_location?->type;
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

        if ($division_id && $division_id > 0) {
            $query = $query->where('division_id', $division_id);
        }
        if ($district_id && $district_id > 0) {
            $query = $query->where('district_id', $district_id);
        }
        if ($city_corp_id && $city_corp_id > 0) {
            $query = $query->where('city_corp_id', $city_corp_id);
        }
        if ($district_pourashava_id && $district_pourashava_id > 0) {
            $query = $query->where('district_pourashava_id', $district_pourashava_id);
        }
        if ($upazila_id && $upazila_id > 0) {
            $query = $query->where('upazila_id', $upazila_id);
        }

        return $query;
    }
    public function verifyPayroll(VerifyPayrollRequest $request, int $id)
    {
        try {
            $payroll = Payroll::findOrFail($id);
            $payroll->is_verified = true;
            $payroll->verified_by_id = auth()->user()->id;
            $payroll->verified_at = now();
            if ($request->hasFile('image')) {
                $payroll->verified_document = $request->file('image')->store('public/payroll/verified_document');
            }
            $payroll->save();
            return $payroll;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    public function rejectPayroll($request)
    {
        try {
            $payrollDetails = $request->all();
            foreach ($payrollDetails as $key => $detail) {
                $payroll = Payroll::findOrFail($detail['payroll_id']);
                if ($payroll !== null && $payroll->payrollDetails->isNotEmpty()) {
                    DB::beginTransaction();

                    BeneficiaryPayrollPaymentStatusLog::create([
                        'beneficiary_id' => $detail['beneficiary_id'],
                        'payroll_details_id' => $detail['payroll_details_id'],
                        'created_by' => auth()->user()->id,
                        'created_at' => now(),
                        'status_id' => 3,
                    ]);

                    PayrollDetail::where('id', $detail['payroll_details_id'])
                        ->update([
                            'status_id' => 3,
                            'is_set' => 0,
                            'status' => 'Rejected',
                        ]);

                    DB::commit();
                } else {
                    return [
                        'message' => 'Payroll details not found for this payroll',
                    ];
                }
            }
            return $payrollDetails;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
    private function processExistingCycles(Payroll $payroll, Collection &$payrollDetails): void
    {
        $perCycleLimit = env('PER_CYCLE_BENEFICIARY_LIMIT', 300000);

        $existingCycles = PayrollPaymentCycle::where('financial_year_id', $payroll->financial_year_id)
            ->where('installment_schedule_id', $payroll->installment_schedule_id)
            ->where('program_id', $payroll->program_id)
            ->where('status', '=', 'Pending')
            ->where('total_beneficiaries', '<', $perCycleLimit)
            ->get();

        foreach ($existingCycles as $existingCycle) {
            $toTake = $perCycleLimit - $existingCycle->total_beneficiaries;
            $toInsert = $payrollDetails->splice(0, $toTake);

            $this->createPaymentCycleDetails($existingCycle->id, $payroll, $toInsert);
            $existingCycle->total_beneficiaries += $toInsert->count();
            $existingCycle->total_amount += $toInsert->sum('total_allowance_amount');
            $existingCycle->sub_total_amount += $toInsert->sum('amount');
            $existingCycle->total_charge += $toInsert->sum('charge');
            $existingCycle->update();
        }
    }

    private function createNewCycles(Payroll $payroll, Collection $payrollDetails): void
    {
        $perCycleLimit = env('PER_CYCLE_BENEFICIARY_LIMIT', 300000);
        $chunks = $payrollDetails->chunk($perCycleLimit);
        $cycle_index = 0;

        foreach ($chunks as $chunk) {
            $cycleName = $this->createCycleName($payroll->financial_year_id, $payroll->installment_schedule_id);

            while (PayrollPaymentCycle::where('name_en', $cycleName['formatted_months_en'] . ($cycle_index > 0 ? "_$cycle_index" : ""))->exists()) {
                $cycle_index++;
            }

            $cycle = new PayrollPaymentCycle();
            $cycle->payroll_id = $payroll->id;
            $cycle->financial_year_id = $payroll->financial_year_id;
            $cycle->installment_schedule_id = $payroll->installment_schedule_id;
            $cycle->program_id = $payroll->program_id;
            $cycle->name_en = $cycleName['formatted_months_en'] . ($cycle_index > 0 ? "_$cycle_index" : "");
            $cycle->name_bn = $cycleName['formatted_months_bn'] . ($cycle_index > 0 ? "_$cycle_index" : "");
            $cycle->total_beneficiaries = $chunk->count();
            $cycle->total_amount = $chunk->sum('total_allowance_amount');
            $cycle->sub_total_amount = $chunk->sum('amount');
            $cycle->total_charge = $chunk->sum('charge');
            $cycle->status = "Pending";
            $cycle->created_by_id = auth()->user()->id;
            $cycle->updated_by_id = auth()->user()->id;
            $cycle->save();

            $this->createPaymentCycleDetails($cycle->id, $payroll, $chunk);
        }
    }

    private function finalizePayrollApproval(Payroll $payroll): void
    {
        $payroll->update([
            'is_rejected' => false,
            'is_approved' => 1,
            'is_payment_cycle_generated' => 1,
            'approved_by_id' => auth()->user()->id,
            'approved_at' => Carbon::now(),
            'payment_cycle_generated_at' => Carbon::now(),
        ]);
    }

    public function approvePayroll(Request $request): array
    {
        \DB::beginTransaction();

        try {
            $payrollDetails = collect($request->all());
            $payrollIds = $payrollDetails->unique('payroll_id')->pluck('payroll_id');

            foreach ($payrollIds as $payrollId) {
                $payroll = Payroll::find($payrollId);
                if (!$payroll) {
                    return [
                        'statusCode' => 404,
                        'message' => 'Payroll not found for ID: ' . $payrollId
                    ];
                }

                $detailsForPayroll = $payrollDetails->where('payroll_id', $payrollId)->values();

                $this->processExistingCycles($payroll, $detailsForPayroll);
                $this->createNewCycles($payroll, $detailsForPayroll);
                $this->finalizePayrollApproval($payroll);
            }

            \DB::commit();
            return [
                'statusCode' => 200,
                'message' => 'Payrolls Approved Successfully',
                'data' => $payrollDetails
            ];
        } catch (Exception $e) {
            \DB::rollBack();
            return [
                'statusCode' => 0,
                'message' => $e->getMessage(),
            ];
        }
    }

    public function approveAllPayrollBeneficiaries(Request $request): array
    {
        \DB::beginTransaction();

        try {
            $pendingPayrolls = Payroll::query()
            ->where('program_id', $request->program_id)
            ->where('financial_year_id', $request->financial_year_id)
            ->where('office_id', $request->office_id)
            ->where('installment_schedule_id', $request->installment_id)
            // ->where('is_approved', 0)
            ->where('is_rejected', 0)->get();

            if ($pendingPayrolls->isEmpty()) {
                return [
                    'statusCode' => 404,
                    'message' => 'No pending payrolls found.'
                ];
            }

            if(PayrollDetail::where( 'status', 'Pending')->whereIn('payroll_id', $pendingPayrolls->pluck('id'))->count() == 0){
                return [
                    'statusCode' => 404,
                    'message' => 'No pending payroll beneficiaries found.'
                ];
            }

            foreach ($pendingPayrolls as $payroll) {
                $payrollDetails = PayrollDetail::where(['payroll_id' => $payroll->id, 'status' => 'Pending'])->get();

                if ($payrollDetails->isEmpty()) {
                    continue;
                }

                $this->processExistingCycles($payroll, $payrollDetails);
                $this->createNewCycles($payroll, $payrollDetails);
                $this->finalizePayrollApproval($payroll);
            }

            \DB::commit();
            return [
                'statusCode' => 200,
                'message' => 'All pending payrolls approved successfully.'
            ];
        } catch (\Exception $e) {
            \DB::rollBack();
            return [
                'statusCode' => 0,
                'message' => $e->getMessage()
            ];
        }
    }



    private function createPaymentCycleDetails($cycleId, $payroll, $payrollDetails): void
    {

        foreach ($payrollDetails as $payrollDetail) {
            if(is_object($payrollDetail)){
                $payroll_id = $payrollDetail->payroll_id;
                $payroll_detail_id = $payrollDetail->id;
                $beneficiary_id = $payrollDetail->beneficiary_id;
                $total_amount = $payrollDetail->total_amount;
                $amount = $payrollDetail->amount;
                $charge = $payrollDetail->charge;
            }else{
                $payroll_id = $payrollDetail['payroll_id'];
                $payroll_detail_id = $payrollDetail['payroll_details_id'];
                $beneficiary_id = $payrollDetail['beneficiary_id'];
                $total_amount = $payrollDetail['total_allowance_amount'];
                $amount = $payrollDetail['amount'];
                $charge = $payrollDetail['charge'];
            }
            $paymentCycleDetail = new PayrollPaymentCycleDetail();
            $paymentCycleDetail->payroll_payment_cycle_id = $cycleId;
            $paymentCycleDetail->payroll_id = $payroll_id;
            $paymentCycleDetail->payroll_detail_id = $payroll_detail_id;
            $paymentCycleDetail->beneficiary_id = $beneficiary_id;
            $paymentCycleDetail->total_amount = $total_amount;
            $paymentCycleDetail->amount = $amount;
            $paymentCycleDetail->charge = $charge;
            $paymentCycleDetail->status = "Pending";
            $paymentCycleDetail->financial_year_id = $payroll->financial_year_id;
            $paymentCycleDetail->installment_schedule_id = $payroll->installment_schedule_id;
            $paymentCycleDetail->program_id = $payroll->program_id;
            $paymentCycleDetail->updated_by_id = auth()->user()->id;
            $paymentCycleDetail->status_id = 4;
            $paymentCycleDetail->save();
            BeneficiaryPayrollPaymentStatusLog::create([
                'beneficiary_id' => $beneficiary_id,
                'payroll_details_id' => $payroll_detail_id,
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'status_id' => 4,
            ]);
            PayrollDetail::where('id', $payroll_detail_id)
                ->where('is_set', 1)
                ->update(['status_id' => 2, 'status' => 'Approved']);

            // Create beneficiary payment status log
            BeneficiaryPayrollPaymentStatusLog::create([
                'beneficiary_id' => $payrollDetail['beneficiary_id'],
                'payroll_details_id' => $payroll_detail_id,
                'created_by' => auth()->user()->id,
                'created_at' => now(),
                'status_id' => 2,
            ]);
        }
    }
    private function createCycleName($year_id, $id): array
    {
        $installment = PayrollInstallmentSchedule::find($id);
        $year = FinancialYear::find($year_id);
        if ($installment) {
            $installmentName = $installment->installment_name;
            $installmentNameBn = $installment->installment_name_bn;
            // $year = date('Y');
            // $formattedMonthsEn = $this->formatMonthEn($installmentName, $year);
            // $formattedMonthsBn = $this->formatMonthBn($installmentNameBn, $year);
            $formattedMonthsEn = $this->formatInstallmentPeriod($year, $installment, 'en');
            $formattedMonthsBn = $this->formatInstallmentPeriod($year, $installment, 'bn');
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
    private function formatInstallmentPeriod($financialYear, $installmentSchedule, $lang = 'en') {

        $startDate = Carbon::parse($financialYear->start_date);
        $endDate = Carbon::parse($financialYear->end_date);

        // Bangla month names
        $bnMonths = [
            'January' => 'জানুয়ারি', 'February' => 'ফেব্রুয়ারি', 'March' => 'মার্চ',
            'April' => 'এপ্রিল', 'May' => 'মে', 'June' => 'জুন',
            'July' => 'জুলাই', 'August' => 'আগস্ট', 'September' => 'সেপ্টেম্বর',
            'October' => 'অক্টোবর', 'November' => 'নভেম্বর', 'December' => 'ডিসেম্বর',
        ];

        $installmentName = $installmentSchedule->installment_name;

        // Extract months from name
        preg_match_all('/([A-Za-z]+)/', $installmentName, $matches);
        $months = array_filter($matches[0], function($value) {
            return in_array($value, [
                'January', 'February', 'March', 'April', 'May', 'June',
                'July', 'August', 'September', 'October', 'November', 'December'
            ]);
        });

        $months = array_values($months);

        if (count($months) === 1) {
            $month = $months[0];
            $date = (in_array($month, ['July', 'August', 'September', 'October', 'November', 'December']))
                ? $startDate->year
                : $endDate->year;
            $monthName = $lang === 'bn' ? $bnMonths[$month] : $month;
            return "{$monthName}/{$date}";
        }

        if (count($months) === 2) {
            [$startMonth, $endMonth] = $months;
            $startYear = in_array($startMonth, ['July', 'August', 'September', 'October', 'November', 'December'])
                ? $startDate->year
                : $endDate->year;
            $endYear = in_array($endMonth, ['July', 'August', 'September', 'October', 'November', 'December'])
                ? $startDate->year
                : $endDate->year;

            $startMonthName = $lang === 'bn' ? $bnMonths[$startMonth] : $startMonth;
            $endMonthName = $lang === 'bn' ? $bnMonths[$endMonth] : $endMonth;

            return "{$startMonthName}/{$startYear} - {$endMonthName}/{$endYear}";
        }

        return ''; // fallback if format is unexpected
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

    public static function checkPayrollCycleBeneficiaryIdFinancialYear($beneficiary_id, $program_id)
    {
        $financialYearId  = FinancialYear::where('status', 1)->value('id');

        $getActiveInstallments = PayrollInstallmentSetting::query()
            ->join('payroll_installment_schedules', 'payroll_installment_schedules.id', '=', 'payroll_installment_settings.installment_schedule_id')
            ->select('payroll_installment_schedules.*')
            ->where('payroll_installment_settings.program_id', $program_id)
            ->where('payroll_installment_settings.financial_year_id', $financialYearId)
            ->orderBy('payroll_installment_schedules.id')
            ->get();

        $installmentIds = $getActiveInstallments->pluck('id')->toArray();

//        \Log::info('checkPayrollCycleBeneficiaryIdFinancialYear installment: ', ['getActiveInstallments' => $getActiveInstallments]);
//        \Log::info('checkPayrollCycleBeneficiaryIdFinancialYear installment IDa: ', ['installmentIds' => $installmentIds]);

        $exists = PayrollPaymentCycleDetail::where('beneficiary_id', $beneficiary_id)
            ->where('financial_year_id', $financialYearId)
            ->whereIn('installment_schedule_id', $installmentIds)
            ->exists();

//        \Log::info('Payroll check result', ['exists' => $exists]);

        return !$exists;

//        getActiveInstallments
    }
}
