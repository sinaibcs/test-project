<?php

namespace App\Http\Services\Admin\Beneficiary;


use App\Http\Services\Admin\Payroll\PayrollService;
use App\Models\BeneficiaryPovertyValue;
use App\Models\Variable;
use Arr;
use Cache;
use Illuminate\Pagination\LengthAwarePaginator;
use Log;
use Carbon\Carbon;
use App\Models\Application;
use App\Models\Mfs;
use App\Models\Bank;
use App\Models\User;
use App\Models\Lookup;
use Mockery\Exception;
use App\Helpers\Helper;
use App\Models\Location;
use App\Models\Grievance;
use App\Models\BankBranch;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\PayrollDetail;
use App\Models\BeneficiaryExit;
use App\Models\BeneficiaryReplace;
use Illuminate\Support\Facades\DB;
use App\Models\AllowanceProgramAge;
use App\Models\BeneficiaryShifting;
use App\Constants\BeneficiaryStatus;
use App\Models\BeneficiaryVerifyLog;
use App\Models\BeneficiaryChangeType;
use App\Models\GrievanceStatusUpdate;
use App\Models\AllowanceProgramAmount;
use Illuminate\Database\Query\Builder;
use App\Notifications\BeneficiaryDeath;
use App\Models\BeneficiaryChangeTracking;
use App\Models\PayrollPaymentCycleDetail;
use App\Models\BeneficiaryLocationShifting;
use App\Notifications\BeneficiaryRemarriage;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Location\LocationResource;
use App\Http\Requests\Admin\Beneficiary\VerifyAllRequest;
use App\Http\Requests\Admin\Beneficiary\ApproveAllRequest;
use App\Http\Services\Admin\BudgetAllotment\AllotmentService;
use App\Http\Requests\Admin\Beneficiary\UpdateAccountInfoRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateBeneficiaryRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateContactInfoRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateNomineeInfoRequest;
use App\Http\Requests\Admin\Beneficiary\UpdatePersonalInfoRequest;

/**
 *
 */
class BeneficiaryService
{
    /**
     * @return FinancialYear|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function currentFinancialYear()
    {
        return FinancialYear::query()->where('status', 1)->first();
    }

    /**
     * @param $locationTypeId
     * @return LookupResource
     */
    public function getLocationType($locationTypeId): LookupResource
    {
        return new LookupResource(Lookup::find($locationTypeId));
    }

    /**
     * @return array
     */
    public function getUserLocation(): array
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignLocation = $user->assign_location;
        $locationType = $user->assign_location?->localtion_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation
        $type = $user->assign_location?->type;
        // division->district
        // localtion_type=1; district-pouroshava->ward
        // localtion_type=2; thana->{union/pouro}->ward
        // localtion_type=3; thana->ward
        $userLocation = [];
        if ($assignLocation?->type == 'ward') {
            $userLocation['ward'] = new LocationResource($assignLocation);
            // 1st parent
            if ($assignLocation?->parent?->type == 'union') {
                $userLocation['union'] = new LocationResource($assignLocation?->parent);
                $userLocation['sub_location_type'] = $assignLocation?->parent?->type;
            } elseif ($assignLocation?->parent?->type == 'pouro') {
                $userLocation['pourashava'] = new LocationResource($assignLocation?->parent);
                $userLocation['sub_location_type'] = $assignLocation?->parent?->type;
            } elseif ($assignLocation?->parent?->type == 'city') {
                $userLocation['district_pourashava'] = new LocationResource($assignLocation?->parent);
                $userLocation['location_type'] = $this->getLocationType($assignLocation?->parent?->location_type);
            } elseif ($assignLocation?->parent?->type == 'thana') {
                $userLocation['thana'] = new LocationResource($assignLocation?->parent);
                $userLocation['location_type'] = $this->getLocationType($assignLocation?->parent?->location_type);
            }

            // 2nd parent
            if ($assignLocation?->parent?->parent?->type == 'thana') {
                $userLocation['upazila'] = new LocationResource($assignLocation?->parent?->parent);
                $userLocation['location_type'] = $this->getLocationType($assignLocation?->parent?->parent?->location_type);
            } elseif ($assignLocation?->parent?->parent?->type == 'city') {
                $userLocation['city_corp'] = new LocationResource($assignLocation?->parent);
                $userLocation['location_type'] = $this->getLocationType($assignLocation?->parent?->parent?->location_type);
            }
            // 3rd parent
            $userLocation['district'] = new LocationResource($assignLocation?->parent?->parent);
            // 4th parent
            $userLocation['division'] = new LocationResource($assignLocation?->parent?->parent?->parent);
        } elseif ($assignLocation?->type == 'union' || $assignLocation?->type == 'pouro') {
            if ($assignLocation?->type == 'union')
                $userLocation['union'] = new LocationResource($assignLocation);
            elseif ($assignLocation?->type == 'pouro')
                $userLocation['pourashava'] = new LocationResource($assignLocation);
            $userLocation['sub_location_type'] = $assignLocation?->type;

            // parents
            $userLocation['location_type'] = $this->getLocationType($assignLocation?->parent?->location_type);
            $userLocation['upazila'] = new LocationResource($assignLocation?->parent);
            $userLocation['district'] = new LocationResource($assignLocation?->parent?->parent);
            $userLocation['division'] = new LocationResource($assignLocation?->parent?->parent?->parent);
        } elseif ($assignLocation?->type == 'thana') {
            $userLocation['location_type'] = $this->getLocationType($assignLocation?->location_type);
            if ($assignLocation?->location_type == 2) {
                $userLocation['upazila'] = new LocationResource($assignLocation);
                // parents
                $userLocation['district'] = new LocationResource($assignLocation?->parent);
                $userLocation['division'] = new LocationResource($assignLocation?->parent?->parent);
            } elseif ($assignLocation?->location_type == 3) {
                $userLocation['thana'] = new LocationResource($assignLocation);
                // parents
                $userLocation['city_corp'] = new LocationResource($assignLocation?->parent);
                $userLocation['district'] = new LocationResource($assignLocation?->parent?->parent);
                $userLocation['division'] = new LocationResource($assignLocation?->parent?->parent?->parent);
            }

        } elseif ($assignLocation?->type == 'city') {
            if ($assignLocation?->location_type == 1)
                $userLocation['district_pourashava'] = new LocationResource($assignLocation);
            elseif ($assignLocation?->location_type == 3)
                $userLocation['city_corp'] = new LocationResource($assignLocation);
            $userLocation['location_type'] = $this->getLocationType($assignLocation?->location_type);
            // parents
            $userLocation['district'] = new LocationResource($assignLocation?->parent);
            $userLocation['division'] = new LocationResource($assignLocation?->parent?->parent);
        } elseif ($assignLocation?->type == 'district') {
            $userLocation['district'] = new LocationResource($assignLocation);
            $userLocation['division'] = new LocationResource($assignLocation?->parent);
        } elseif ($assignLocation?->type == 'division')
            $userLocation['division'] = new LocationResource($assignLocation);
        return $userLocation;
    }

    /**
     * @param $query
     * @param $request
     * @return mixed
     */
    private function applyLocationFilter($query, $request): mixed
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation
        $locationType = $user->assign_location?->type;
        // division->district
        // localtion_type=1; district-pouroshava->ward
        // localtion_type=2; thana->{union/pouro}->ward
        // localtion_type=3; thana->ward

        $division_id = $request->query('division_id');
        $district_id = $request->query('district_id');
        $location_type_id = $request->query('location_type_id')?? $request->query('location_type');
        $city_corp_id = $request->query('city_corp_id');
        $district_pourashava_id = $request->query('district_pourashava_id');
        $upazila_id = $request->query('upazila_id');
//        $sub_location_type_id = $request->query('sub_location_type_id');
        $pourashava_id = $request->query('pourashava_id');
        $thana_id = $request->query('thana_id');
        $union_id = $request->query('union_id');
        $ward_id = $request->query('ward_id');

        if ($user->assign_location) {
            if ($locationType == 'ward') {
                $ward_id = $assignedLocationId;
                // $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = $thana_id = $pourashava_id = $union_id = -1;
            } elseif ($locationType == 'union') {
                $union_id = $assignedLocationId;
                // $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = $thana_id = $pourashava_id = -1;
            } elseif ($locationType == 'pouro') {
                $pourashava_id = $assignedLocationId;
                // $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = $thana_id = $union_id = -1;
            } elseif ($locationType == 'thana') {
                if ($subLocationType == 2) {
                    $upazila_id = $assignedLocationId;
                    // $division_id = $district_id = $city_corp_id = $district_pourashava_id = $thana_id = -1;
                } elseif ($subLocationType == 3) {
                    $thana_id = $assignedLocationId;
                    // $division_id = $district_id = $city_corp_id = $district_pourashava_id = $upazila_id = -1;
                } else {
                    $query = $query->where('id', -1); // wrong location type
                }
            } elseif ($locationType == 'city') {
                if ($subLocationType == 1) {
                    $district_pourashava_id = $assignedLocationId;
                    // $division_id = $district_id = $city_corp_id = $upazila_id = $thana_id = -1;
                } elseif ($subLocationType == 3) {
                    $city_corp_id = $assignedLocationId;
                    // $division_id = $district_id = $district_pourashava_id = $upazila_id = $thana_id = -1;
                } else {
                    $query = $query->where('id', -1); // wrong location type
                }
            } elseif ($locationType == 'district') {
                $district_id = $assignedLocationId;
                // $division_id = -1;
            } elseif ($locationType == 'division') {
                $division_id = $assignedLocationId;
            } else {
                $query = $query->where('id', -1); // wrong location assigned
            }
        }

        if($location_type_id == 1){
            $upazila_id = -1;
            $union_id = -1;
            $pourashava_id = -1;
            $city_corp_id = -1;
            $thana_id = -1;
        }elseif($location_type_id == 2){
            $thana_id = -1;
            $city_corp_id = -1;
            $district_pourashava_id = -1;
        }elseif($location_type_id == 3){
            $upazila_id = -1;
            $union_id = -1;
            $pourashava_id = -1;
            $district_pourashava_id = -1;
        }

        if ($division_id && $division_id > 0)
            $query = $query->where('permanent_division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('permanent_district_id', $district_id);
        if ($location_type_id && $location_type_id > 0)
            $query = $query->where('permanent_location_type_id', $location_type_id);
        if ($city_corp_id && $city_corp_id > 0)
            $query = $query->where('permanent_city_corp_id', $city_corp_id);
        if ($district_pourashava_id && $district_pourashava_id > 0)
            $query = $query->where('permanent_district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && $upazila_id > 0)
            $query = $query->where('permanent_upazila_id', $upazila_id);
        if ($pourashava_id && $pourashava_id > 0)
            $query = $query->where('permanent_pourashava_id', $pourashava_id);
        if ($thana_id && $thana_id > 0)
            $query = $query->where('permanent_thana_id', $thana_id);
        if ($union_id && $union_id > 0){
            $query = $query->where('permanent_union_id', $union_id);
        }

        if ($ward_id && $ward_id > 0)
            $query = $query->where('permanent_ward_id', $ward_id);

        $union_ids = $user->unions()->where('type', 'union')->pluck('id');
        $pouro_ids = $user->unions()->where('type', 'pouro')->pluck('id');
        if(count($union_ids) > 0 && count($pouro_ids) > 0){
            $query->where(function($q)use($union_ids, $pouro_ids){
                $q->whereIn('permanent_union_id', $union_ids)->orWhereIn('permanent_pourashava_id', $pouro_ids);
            });
        }elseif(count($union_ids) > 0){
            $query->whereIn('permanent_union_id', $union_ids);
        }elseif(count($pouro_ids) > 0){
            $query->whereIn('permanent_pourashava_id', $pouro_ids);
        }

        if ($user->userWards()->exists())
            $query->whereIn('permanent_ward_id', $user->userWards()->pluck('id'));

        return $query;
    }

    /**
     * @param $query
     * @param $request
     * @return mixed
     */
    private function applyLocationFilter2($query, $request): mixed
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation
        $locationType = $user->assign_location?->type;
        // division->district
        // localtion_type=1; district-pouroshava->ward
        // localtion_type=2; thana->{union/pouro}->ward
        // localtion_type=3; thana->ward

        $division_id = $request->query('division_id');
        $district_id = $request->query('district_id');
        $location_type_id = $request->query('location_type_id');
        $city_corp_id = $request->query('city_corp_id');
        $district_pourashava_id = $request->query('district_pourashava_id');
        $upazila_id = $request->query('upazila_id');
//        $sub_location_type_id = $request->query('sub_location_type_id');
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

        if ($division_id && $division_id > 0)
            $query = $query->where('beneficiaries.permanent_division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('beneficiaries.permanent_district_id', $district_id);
        if ($location_type_id && $location_type_id > 0)
            $query = $query->where('beneficiaries.permanent_location_type_id', $location_type_id);
        if ($city_corp_id && $city_corp_id > 0)
            $query = $query->where('beneficiaries.permanent_city_corp_id', $city_corp_id);
        if ($district_pourashava_id && $district_pourashava_id > 0)
            $query = $query->where('beneficiaries.permanent_district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && $upazila_id > 0)
            $query = $query->where('beneficiaries.permanent_upazila_id', $upazila_id);
        if ($pourashava_id && $pourashava_id > 0)
            $query = $query->where('beneficiaries.permanent_pourashava_id', $pourashava_id);
        if ($thana_id && $thana_id > 0)
            $query = $query->where('beneficiaries.permanent_thana_id', $thana_id);
        if ($union_id && $union_id > 0)
            $query = $query->where('beneficiaries.permanent_union_id', $union_id);
        if ($ward_id && $ward_id > 0)
            $query = $query->where('beneficiaries.permanent_ward_id', $ward_id);
        return $query;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function list2(Request $request, $forPdf = false)
    {
        ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2G');

//        $program_id = $request->query('program_id');
        $program_id = $request->has('program_id') ? $request->query('program_id') : [];
        $beneficiary_id = $request->query('beneficiary_id');
        $nominee_name = $request->query('nominee_name');
        $beneficiary_name = $request->query('beneficiary_name');
        $account_number = $request->query('account_number');
        $verification_number = $request->query('nid');
//        $status = $request->query('status');
        $status = $request->has('status') ? $request->query('status') : [];
        $remarks_ids = $request->has('remarks_ids') ? $request->query('remarks_ids') : [];
        $verify_id = $request->query('verify_id');
        $account_status_id = $request->has('account_status_id') ? $request->query('account_status_id') : [];
        $currentFinancialYear = $this->currentFinancialYear();
        $inactive_cause_id = $request->has('inactive_cause_id') ? $request->query('inactive_cause_id') : [];

        $account_change_status_id = $request->has('account_change_status_id') ? $request->query('account_change_status_id') : null;

        $perPage = $request->query('perPage');
        $page = $request->query(key: 'page');
        $sortByColumn = $request->query('sortBy', 'created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = Beneficiary::query();
//        if ($program_id)
//            $query = $query->where('program_id', $program_id);
        if (gettype($program_id) === 'array' && count($program_id) > 0)
            $query = $query->whereIn('program_id', $program_id);
        elseif (gettype($program_id) === 'string')
            $query = $query->where('program_id', $program_id);

        $query = $this->applyLocationFilter($query, $request);

        // advance search
        if (!empty($beneficiary_id))
            $query = $query->whereIn('beneficiary_id', is_array($beneficiary_id)? $beneficiary_id : explode(',',$beneficiary_id));
        if ($nominee_name){
            $query = $query->whereRaw('UPPER(nominee_en) LIKE "%' . strtoupper($nominee_name) . '%"');
            $query = $query->orWhereRaw('nominee_bn LIKE "%' . $nominee_name . '%"');
        }
        if ($beneficiary_name){
            $query = $query->whereRaw('UPPER(name_en) LIKE "%' . strtoupper($beneficiary_name) . '%"');
            $query = $query->orWhereRaw('name_bn LIKE "%' . $beneficiary_name . '%"');
        }
        if ($account_number)
            $query = $query->where('account_number', $account_number);
        if ($verification_number)
            $query = $query->where('verification_number', $verification_number);
//        if ($status)
//            $query = $query->where('status', $status);
        if (gettype($status) === 'array' && count($status) > 0)
            $query = $query->whereIn('status', $status);
        elseif (gettype($status) === 'string')
            $query = $query->where('status', $status);

        if (gettype($inactive_cause_id) === 'array' && count($inactive_cause_id) > 0)
            $query = $query->whereIn('inactive_cause_id', $inactive_cause_id);
        elseif (gettype($inactive_cause_id) === 'string')
            $query = $query->where('inactive_cause_id', $inactive_cause_id);

        if ($verify_id != null) {
            $query = $query->where(function ($query) use ($currentFinancialYear, $verify_id) {
                if (!$verify_id) {
                    $query->where('last_ver_fin_year_id', '!=', $currentFinancialYear->id)
                        ->orWhere('is_verified', false)
                        ->orWhereNull('last_ver_fin_year_id');
                } else {
                    $query->where('last_ver_fin_year_id', '=', $currentFinancialYear->id)
                        ->where('is_verified', true);
                }
                return $query;
            });
        }
        if (count($remarks_ids) > 0) {
            $query = $query->where(function ($q) use ($remarks_ids, $currentFinancialYear) {
                if (in_array(1, $remarks_ids) && $currentFinancialYear) {
                    $q = $q->orWhereBetween('approve_date', [Carbon::parse($currentFinancialYear->start_date), Carbon::parse($currentFinancialYear->end_date)]);
                }
                if (in_array(2, $remarks_ids)) {
                    $q = $q->orWhereExists(function (Builder $qry) {
                        $qry->select(DB::raw(1))
                            ->from('beneficiary_change_trackings')
                            ->whereColumn('beneficiary_change_trackings.beneficiary_id', 'beneficiaries.id')
                            ->whereRaw('month(now()) = month(beneficiary_change_trackings.created_at)');
                    });
                }
                if (in_array(3, $remarks_ids))
                    $q = $q->orWhere('is_replaced', true);

                if (in_array(4, $remarks_ids) && $currentFinancialYear) {
                    $q = $q->orWhere(function ($qr) use ($currentFinancialYear) {
                        $qr->whereDate('approve_date', '<', Carbon::parse($currentFinancialYear->start_date))
                            ->orWhereNull('approve_date');
                    });
                }


                return $q;
            });
        }
        if ($request->has('account_change_status_id')) {
//            Log::info('Account Change Status ID before: ' . $account_change_status_id);
//            Log::info('Account Change Status ID after: ' . $account_change_status_id);

            $query = $query->whereHas('changeTrackings', function ($query) use ($account_change_status_id) {
                $query->where('change_type_id', 3)
                    ->where(function ($query) use ($account_change_status_id) {
                        if ($account_change_status_id !== null) {
                            // When the status is a specific value (e.g., 1 or 0)
                            $query->where('status', $account_change_status_id);
                        } else {
                            // When the status is NULL
                            $query->whereNull('status');
                        }
                    });

//                \Log::info('SQL Query: ' . $query->toSql());
//                \Log::info('Bindings: ' . json_encode($query->getBindings()));

            });
        }
        if ($request->has('account_status_id')){
            if($account_status_id == 1){
                $query = $query->whereNotNull('bank_id');
            } else if($account_status_id == 2){
                $query = $query->whereNull('bank_id');
            } else if($account_status_id == 3){
                $query = $query->whereNotNull('mfs_id');
            } else if($account_status_id == 4){
                $query = $query->whereNull('mfs_id');
            }
        }
        if ($request->has('age_range')) {
            $ageRange = $request->query('age_range');
            if (count($ageRange) === 2) {
                if(!($ageRange[0] == 1 && $ageRange[1] == 130))
                $query->whereBetween('age', $ageRange);
            } elseif (count($ageRange) === 1) {
                $query->where('age', $ageRange[0]);
            }
        }
//  return $query->paginate($perPage, ['*'], 'page', $page);
        if ($forPdf)
            return $query->with('program',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard',
                'get_office_id_from_wards')
                // ->orderBy("$sortByColumn", "$orderByDirection")
                ->orderBy("beneficiary_id", 'asc')
                ->paginate($perPage, ['*'], 'page', $page);
        else
            $beneficiaries = $query->with('program',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard',
                'get_office_id_from_wards')
                // ->orderBy("$sortByColumn", "$orderByDirection")
                ->orderBy("beneficiary_id", 'asc')
                ->paginate($perPage);

        $beneficiaries->map(function ($beneficiary) use ($currentFinancialYear, $status) {
            // is_new_beneficiary
            if ($currentFinancialYear && $beneficiary->approve_date) {
                $start_date = Carbon::parse($currentFinancialYear->start_date);
                $end_date = Carbon::parse($currentFinancialYear->end_date);
                $approve_date = Carbon::parse($beneficiary->approve_date);
                $beneficiary->is_new_beneficiary = $approve_date->between($start_date, $end_date);
            } else
                $beneficiary->is_new_beneficiary = false;

            // is_account_changed
            $beneficiary_account_change = BeneficiaryChangeTracking::query()
                ->join('beneficiary_change_types', 'beneficiary_change_types.id', '=', 'beneficiary_change_trackings.change_type_id')
                ->where('beneficiary_change_trackings.beneficiary_id', $beneficiary->id)
                ->where('beneficiary_change_types.keyword', 'ACCOUNT_CHANGE')
                ->orderBy('beneficiary_change_trackings.created_at', 'desc')
                ->first();
            if ($beneficiary_account_change) {
                $currentMonth = now()->month;
                $changedOnMonth = Carbon::parse($beneficiary_account_change->created_at)->month;
                $payment_cycle = $beneficiary->program?->payment_cycle ?? 'Monthly';
                $factor = match ($payment_cycle) {
                    'Monthly' => 12,
                    'Quarterly' => 4,
                    'Half Yearly' => 2,
                    'Yearly' => 1,
                };
                $currentInstallmentNo = floor(($currentMonth * $factor) / 12);
                $changedOnInstallmentNo = floor(($changedOnMonth * $factor) / 12);
                if ($currentInstallmentNo == $changedOnInstallmentNo)
                    $beneficiary->is_account_changed = true;
                else
                    $beneficiary->is_account_changed = false;
            } else
                $beneficiary->is_account_changed = false;

            // is_replaced
            $beneficiary->is_replaced = $beneficiary->is_replaced ?? false;

            // is_regular_beneficiary
            if ($currentFinancialYear && $beneficiary->approve_date) {
                $start_date = Carbon::parse($currentFinancialYear->start_date);
                $approve_date = Carbon::parse($beneficiary->approve_date);
                $beneficiary->is_regular_beneficiary = $approve_date->lt($start_date);
            } else
                $beneficiary->is_regular_beneficiary = false;

            // is_verified
            $is_verified = BeneficiaryVerifyLog::query()->where('beneficiary_id', $beneficiary->id)->where('financial_year_id', $currentFinancialYear->id)->count();
            $beneficiary->is_verified = (bool)$is_verified && $beneficiary->is_verified;

            if ($status == '2' && $beneficiary->inactive_cause_id) {
                $beneficiary->inactive_cause = Lookup::query()->find($beneficiary->inactive_cause_id);
            }

        });

        return $beneficiaries;
    }

    public function summeryReport(Request $request){
        $program_id = $request->query('program_id', []);
        $mfs_id = $request->mfs_id;
        $bank_id = $request->bank_id;
        $query = Beneficiary::query();
        $query->when($program_id, function($q) use($program_id, $request){
            if(is_array($program_id)){
                $q->whereIn('program_id', $program_id);
            }else{
                $q->where('program_id', $program_id);
            }
        });
        $query->when($bank_id, function ($q) use ($bank_id) {
            if (is_array($bank_id)) {
                $q->whereIn('bank_id', $bank_id);
            } else {
                $q->where('bank_id', $bank_id);
            }
        });

        $query->when($mfs_id, function ($q) use ($mfs_id) {
            if (is_array($mfs_id)) {
                $q->whereIn('mfs_id', $mfs_id);
            } else {
                $q->where('mfs_id', $mfs_id);
            }
        });
        $this->applyLocationFilter($query, $request);

        $totalActive = (clone $query)->where('status', 1)->count();
        $totalWaiting = (clone $query)->where('status', 3)->count();
        $currentFinancialYearId = app('CurrentFinancialYear')?->id??0;
        $totalVerified = (clone $query)->whereHas('verifyLogs', fn($q) => $q->where('financial_year_id', $currentFinancialYearId))->count();
        $totalNotVerified = (clone $query)->whereDoesntHave('verifyLogs', fn($q) => $q->where('financial_year_id', $currentFinancialYearId))->count();
        $totalAccountChange = (clone $query)->whereHas('changeTrackings', fn($q) => $q->where('change_type_id', 3)->whereNotNull('approved_by'))->count();

        return [
            'total_active' => $totalActive,
            'total_waiting' => $totalWaiting,
            'total_verified' => $totalVerified,
            'total_not_verified' => $totalNotVerified,
            'total_account_change' => $totalAccountChange,
        ];
    }

    public function list(Request $request, $forPdf = false, $returnQuery = false)
    {
        // ini_set('max_execution_time', '0');
        ini_set('memory_limit', '2G');


        $currentFinancialYear = $this->currentFinancialYear();

        $query = Beneficiary::query();
        // Manual index usages are turned off for beneficiary excel export
        $this->applyIndexes($query, $request);
        // if(!$returnQuery){
        // }
        $this->applyBasicFilters($query, $request);
        $this->applyAdvancedFilters($query, $request, $currentFinancialYear);
        $this->applyRemarksFilter($query, $request, $currentFinancialYear);
        $this->applyAccountChangeStatusFilter($query, $request);
        $this->applyAgeFilter($query, $request);

        $query->withCount(['verifyLogs' => function ($q) {
            $q->where('financial_year_id', app('CurrentFinancialYear')?->id);
        }]);
        // do not remove order by beneficiary_id asc. it will break the excel export
        $query->with([
            'program', 'mainProgram' , 'permanentDivision', 'permanentDistrict',
            'permanentCityCorporation', 'permanentDistrictPourashava',
            'permanentUpazila', 'permanentPourashava', 'permanentThana',
            'permanentUnion', 'permanentWard', 'get_office_id_from_wards', 'gender', 'allowance_class'
        ])->orderBy('beneficiary_id', 'asc');

        if($returnQuery){
            return $query;
        }
        $cacheKey = 'beneficiaries_list_' . md5(json_encode($request->all()));

        $perPage = $request->query('perPage')??10;
        $page = $request->query('page')??1;
        $beneficiaries = $forPdf
        ? $query->paginate($perPage, ['*'], 'page', $page)
        : Cache::tags(['user:' . auth()->id() . ':beneficiaries'])->remember($cacheKey, now()->addMinutes(env('CACHE_TIMEOUT')), function() use ($page, $perPage, $query){
            return $query->forPage( $page, $perPage)->get();
        });
        // $beneficiaries = $forPdf
        // ? $query->paginate($perPage, ['*'], 'page', $page)
        // : $query->paginate($perPage);

        if(!$forPdf){
            $cacheCountKey = 'beneficiaries_list_count' . md5(json_encode($request->except('page')));
            $totalCount = Cache::remember($cacheCountKey, now()->addMinutes(10), function () use ($query) {
                return (clone $query)->count();
            });

            $beneficiaries = new LengthAwarePaginator($beneficiaries, $totalCount, $perPage, $page);
        }

        $duplicates = $this->duplicateBeneficiaryHashMap();

        $this->mapBeneficiaryData($beneficiaries, $currentFinancialYear, $request->query('status'), $duplicates);

        return $beneficiaries;
    }
    private function benHash($item, $returnKey = false){
        $key = implode('|', [
            $item->name_en ?? '<null>',
            $item->father_name_en ?? '<null>',
            $item->mother_name_en ?? '<null>',
            $item->date_of_birth ?? '<null>',
        ]);
        if($returnKey){
            return $key;
        }
        return [$key => true];
    }
    private function duplicateBeneficiaryHashMap(){
        $duplicates = Cache::remember('duplicate_beneficiaries', now()->addDays(30), function(){
            return DB::table('beneficiaries')
            ->useIndex('idx_beneficiaries_duplicate_check')
            ->selectRaw('name_en, father_name_en, mother_name_en, date_of_birth, COUNT(*) as cnt')
            ->whereNull('deleted_at')
            ->groupBy('name_en', 'father_name_en', 'mother_name_en', 'date_of_birth')
            ->having('cnt', '>', 1)
            ->get();
        });
        return $duplicates->mapWithKeys( fn ($item) => $this->benHash($item));
    }

    private function applyIndexes(&$query, Request $request){
        if (
            !$request->hasAny([
                'division_id', 'district_id', 'upazila_id', 'district_pourashava_id',
                'city_corp_id', 'ward_id', 'union_id', 'pourashava_id'
            ])
        ) {
            if ($request->has('program_id')) {
                $query->useIndex('idx_ben_program_geo');
            } else {
                // $query->useIndex('status_deleted_at_composite_index'); // no filters at all
            }
        } else {
            if ($request->has('program_id')) {
                $query->useIndex('idx_ben_program_geo');
            } else {
                $query->useIndex('idx_ben_geo');
            }
        }

        if ($request->has('class_level')) {
            $query->useIndex('beneficiaries_type_id_deleted_at_index');
        }

        // if(!($request->has('division_id') || $request->has('district_id') || $request->has('upazila_id') || $request->has('district_pourashava_id') || $request->has('city_corp_id'))){
        //     if($request->has('program_id')){
        //         $query->useIndex('idx_program_deleted_beneficiary');
        //     }
        // }elseif($request->has('ward_id')){
        //     $query->useIndex('beneficiaries_permanent_ward_id_foreign');
        // }elseif($request->has('union_id')){
        //     $query->useIndex('beneficiaries_permanent_union_id_foreign');
        // }elseif($request->has('pourashava_id')){
        //     $query->useIndex('beneficiaries_permanent_pourashava_id_foreign');
        // }elseif($request->has('upazila_id')){
        //     $query->useIndex('beneficiaries_permanent_upazila_id_foreign');
        // }elseif($request->has('district_pourashava_id')){
        //     $query->useIndex('beneficiary_per_dist_poura_id_fk');
        // }elseif($request->has('city_corp_id')){
        //     $query->useIndex('beneficiaries_permanent_city_corp_id_foreign');
        // }elseif($request->has('district_id')){
        //     $query->useIndex('beneficiaries_permanent_district_id_foreign');
        // }elseif($request->has('division_id')){
        //     $query->useIndex('beneficiaries_permanent_division_id_foreign');
        // }
    }

    private function applyBasicFilters(&$query, Request $request)
    {
        $main_program_id = $request->query('main_program_id', []);
        $program_id = $request->query('program_id', []);
        $status = $request->query('status', []);
        $bank_id = $request->query('bank_id', []);
        $mfs_id = $request->query('mfs_id', []);
        $inactive_cause_id = $request->query('inactive_cause_id', []);
        $beneficiary_id = $request->query('beneficiary_id');
        $account_number = $request->query('account_number');
        $verification_number = $request->query('nid');

        $query->when($program_id, function($q) use($program_id, $request){
            if(is_array($program_id)){
                $q->whereIn('program_id', $program_id);
            }else{
                $q->where('program_id', $program_id);
            }
        });

        $query->when($main_program_id, function($q) use($main_program_id, $request){
            if(is_array($main_program_id)){
                $q->whereIn('main_program_id', $main_program_id);
            }else{
                $q->where('main_program_id', $main_program_id);
            }
        });

        $query->when($status, fn($q) => is_array($status)
            ? $q->whereIn('status', $status)
            : $q->where('status', $status));

        $query->when($bank_id, fn($q) => is_array($bank_id)
        ? $q->whereIn('bank_id', $bank_id)
        : $q->where('bank_id', $bank_id));

        $query->when($mfs_id, fn($q) => is_array($mfs_id)
        ? $q->whereIn('mfs_id', $mfs_id)
        : $q->where('mfs_id', $mfs_id));

        $query->when($inactive_cause_id, fn($q) => is_array($inactive_cause_id)
            ? $q->whereIn('inactive_cause_id', $inactive_cause_id)
            : $q->where('inactive_cause_id', $inactive_cause_id));

        $query->when($beneficiary_id, fn($q) =>
            $q->whereIn('beneficiary_id', is_array($beneficiary_id)
                ? $beneficiary_id
                : explode(',', $beneficiary_id)));

        $query->when($account_number, fn($q) =>
            $q->where('account_number', $account_number));

        $query->when($verification_number, fn($q) =>
            $q->where('verification_number', $verification_number));

        $this->applyLocationFilter($query, $request);
    }

    private function applyAdvancedFilters(&$query, Request $request, $year)
    {
        $query->when($request->query('nominee_name'), function ($q, $val) {
            $q->where(function ($sub) use ($val) {
                $sub->whereRaw('UPPER(nominee_en) LIKE ?', ['%' . strtoupper($val) . '%'])
                    ->orWhere('nominee_bn', 'LIKE', "%$val%");
            });
        });

        $query->when($request->query('beneficiary_name'), function ($q, $val) {
            $q->where(function ($sub) use ($val) {
                $sub->whereRaw('UPPER(name_en) LIKE ?', ['%' . strtoupper($val) . '%'])
                    ->orWhere('name_bn', 'LIKE', "%$val%");
            });
        });

        if (!is_null($request->query('verify_id'))) {
            $verify_id = $request->query('verify_id');
            $query->where(function ($q) use ($verify_id, $year) {
                $q->when(!$verify_id, function ($qr) use ($year) {
                    $qr->whereDoesntHave('verifyLogs', function($q){
                        return $q->where('financial_year_id', app('CurrentFinancialYear')?->id);
                    });
                }, function ($qr) use ($year) {
                    $qr->whereHas('verifyLogs', function($q){
                        return $q->where('financial_year_id', app('CurrentFinancialYear')?->id);
                    });
                });
            });
        }

        $query->when($request->query('class_level'), function ($q, $class_level) {
            $q->where('type_id', (int) $class_level);
        });
    }

    private function applyRemarksFilter(&$query, Request $request, $currentFinancialYear)
    {
        $remarks_ids = $request->query('remarks_ids', []);

        if (count($remarks_ids) === 0) return;

        $query->where(function ($q) use ($remarks_ids, $currentFinancialYear) {
            if (in_array(1, $remarks_ids) && $currentFinancialYear) {
                $q->orWhereBetween('approve_date', [
                    Carbon::parse($currentFinancialYear->start_date),
                    Carbon::parse($currentFinancialYear->end_date)
                ]);
            }

            if (in_array(2, $remarks_ids)) {
                $q->orWhereExists(function (Builder $subQuery) {
                    $subQuery->select(DB::raw(1))
                        ->from('beneficiary_change_trackings')
                        ->whereColumn('beneficiary_change_trackings.beneficiary_id', 'beneficiaries.id')
                        ->whereRaw('month(now()) = month(beneficiary_change_trackings.created_at)');
                });
            }

            if (in_array(3, $remarks_ids)) {
                $q->orWhere('is_replaced', true);
            }

            if (in_array(4, $remarks_ids) && $currentFinancialYear) {
                $q->orWhere(function ($qr) use ($currentFinancialYear) {
                    $qr->whereDate('approve_date', '<', Carbon::parse($currentFinancialYear->start_date))
                        ->orWhereNull('approve_date');
                });
            }
        });
    }

    private function applyAccountChangeStatusFilter(&$query, Request $request)
    {
        if (!$request->has('account_change_status_id')) return;

        $account_change_status_id = $request->query('account_change_status_id');

        $query->whereHas('changeTrackings', function ($subQuery) use ($account_change_status_id) {
            $subQuery->where('change_type_id', 3)
                ->where(function ($q) use ($account_change_status_id) {
                    if (!is_null($account_change_status_id)) {
                        $q->where('status', $account_change_status_id);
                    } else {
                        $q->whereNull('status');
                    }
                });
        });
    }

    private function applyAgeFilter(&$query, Request $request)
    {
        if (!$request->has('age_range')) return;

        $ageRange = $request->query('age_range');

        if (count($ageRange) === 2) {
            if (!($ageRange[0] == 1 && $ageRange[1] == 130)) {
                $query->whereBetween('age', $ageRange);
            }
        } elseif (count($ageRange) === 1) {
            $query->where('age', $ageRange[0]);
        }
    }


public function exportList(Request $request)
{
    ini_set('max_execution_time', '0');
    ini_set('memory_limit', '2G');

    $currentFinancialYear = $this->currentFinancialYear();

    // Use query builder instead of Eloquent
    $query = \DB::table('beneficiaries as b')
        ->select('b.*',
            'ap.name_en as program_name_en',
            'ap.name_bn as program_name_bn',
            'div.name_en as division_name_en',
            'div.name_bn as division_name_bn',
            'dist.name_en as district_name_en',
            'dist.name_bn as district_name_bn',
            'citycorp.name_en as city_corporation_name_en',
            'citycorp.name_bn as city_corporation_name_bn',
            'district_pourashava.name_en as district_pourashava_name_en',
            'district_pourashava.name_bn as district_pourashava_name_bn',
            'upz.name_en as upazila_name_en',
            'upz.name_bn as upazila_name_bn',
            'poura.name_en as pourashava_name_en',
            'poura.name_bn as pourashava_name_bn',
            'thana.name_en as thana_name_en',
            'thana.name_bn as thana_name_bn',
            'union_loc.name_en as union_name_en',
            'union_loc.name_bn as union_name_bn',
            'ward.name_en as ward_name_en',
            'ward.name_bn as ward_name_bn'
        )
        ->leftJoin('allowance_programs as ap', 'b.program_id', '=', 'ap.id')
        ->leftJoin('locations as div', 'b.permanent_division_id', '=', 'div.id')
        ->leftJoin('locations as dist', 'b.permanent_district_id', '=', 'dist.id')
        ->leftJoin('locations as citycorp', 'b.permanent_city_corp_id', '=', 'citycorp.id')
        ->leftJoin('locations as district_pourashava', 'b.permanent_district_pourashava_id', '=', 'district_pourashava.id')
        ->leftJoin('locations as upz', 'b.permanent_upazila_id', '=', 'upz.id')
        ->leftJoin('locations as poura', 'b.permanent_pourashava_id', '=', 'poura.id')
        ->leftJoin('locations as thana', 'b.permanent_thana_id', '=', 'thana.id')
        ->leftJoin('locations as union_loc', 'b.permanent_union_id', '=', 'union_loc.id')
        ->leftJoin('locations as ward', 'b.permanent_ward_id', '=', 'ward.id')
        ->orderBy('b.beneficiary_id', 'asc');

    $this->applyBasicFiltersQB($query, $request);
    $this->applyAdvancedFiltersQB($query, $request, $currentFinancialYear);
    $this->applyRemarksFilterQB($query, $request, $currentFinancialYear);
    $this->applyAccountChangeStatusFilterQB($query, $request);
    $this->applyAgeFilterQB($query, $request);
    $this->applyLocationFilterQB($query, $request);

    return $query;
}

private function applyBasicFiltersQB(&$query, Request $request)
{
    $query->when($request->filled('program_id'), function ($q) use ($request) {
        $ids = is_array($request->program_id) ? $request->program_id : [$request->program_id];
        $q->whereIn('b.program_id', $ids);
    });

    $query->when($request->filled('status'), function ($q) use ($request) {
        $status = is_array($request->status) ? $request->status : [$request->status];
        $q->whereIn('b.status', $status);
    });

    $query->when($request->filled('inactive_cause_id'), function ($q) use ($request) {
        $ids = is_array($request->inactive_cause_id) ? $request->inactive_cause_id : [$request->inactive_cause_id];
        $q->whereIn('b.inactive_cause_id', $ids);
    });

    $query->when($request->filled('beneficiary_id'), function ($q) use ($request) {
        $ids = is_array($request->beneficiary_id)
            ? $request->beneficiary_id
            : explode(',', $request->beneficiary_id);
        $q->whereIn('b.beneficiary_id', $ids);
    });

    $query->when($request->filled('account_number'), fn($q) => $q->where('b.account_number', $request->account_number));
    $query->when($request->filled('nid'), fn($q) => $q->where('b.verification_number', $request->nid));
}

private function applyAdvancedFiltersQB(&$query, Request $request, $year)
{
    $query->when($request->filled('nominee_name'), function ($q) use ($request) {
        $val = $request->nominee_name;
        $q->where(function ($sub) use ($val) {
            $sub->whereRaw('UPPER(b.nominee_en) LIKE ?', ['%' . strtoupper($val) . '%'])
                ->orWhere('b.nominee_bn', 'LIKE', "%$val%");
        });
    });

    $query->when($request->filled('beneficiary_name'), function ($q) use ($request) {
        $val = $request->beneficiary_name;
        $q->where(function ($sub) use ($val) {
            $sub->whereRaw('UPPER(b.name_en) LIKE ?', ['%' . strtoupper($val) . '%'])
                ->orWhere('b.name_bn', 'LIKE', "%$val%");
        });
    });

    if (!is_null($request->query('verify_id'))) {
        $verify_id = $request->query('verify_id');

        $query->where(function ($q) use ($verify_id, $year) {
            if (!$verify_id) {
                $q->where('b.last_ver_fin_year_id', '!=', $year->id)
                  ->orWhere('b.is_verified', false)
                  ->orWhereNull('b.last_ver_fin_year_id');
            } else {
                $q->where('b.last_ver_fin_year_id', $year->id)
                  ->where('b.is_verified', true);
            }
        });
    }
}

private function applyRemarksFilterQB(&$query, Request $request, $currentFinancialYear)
{
    $remarks_ids = $request->query('remarks_ids', []);

    if (count($remarks_ids) === 0) return;

    $query->where(function ($q) use ($remarks_ids, $currentFinancialYear) {
        if (in_array(1, $remarks_ids) && $currentFinancialYear) {
            $q->orWhereBetween('b.approve_date', [
                Carbon::parse($currentFinancialYear->start_date),
                Carbon::parse($currentFinancialYear->end_date)
            ]);
        }

        if (in_array(2, $remarks_ids)) {
            $q->orWhereExists(function ($sub) {
                $sub->select(DB::raw(1))
                    ->from('beneficiary_change_trackings as bct')
                    ->whereRaw('bct.beneficiary_id = b.beneficiary_id')
                    ->whereRaw('MONTH(NOW()) = MONTH(bct.created_at)');
            });
        }

        if (in_array(3, $remarks_ids)) {
            $q->orWhere('b.is_replaced', true);
        }

        if (in_array(4, $remarks_ids) && $currentFinancialYear) {
            $q->orWhere(function ($qr) use ($currentFinancialYear) {
                $qr->whereDate('b.approve_date', '<', Carbon::parse($currentFinancialYear->start_date))
                   ->orWhereNull('b.approve_date');
            });
        }
    });
}

private function applyAccountChangeStatusFilterQB(&$query, Request $request)
{
    if (!$request->has('account_change_status_id')) return;

    $status = $request->query('account_change_status_id');

    $query->whereExists(function ($sub) use ($status) {
        $sub->select(DB::raw(1))
            ->from('beneficiary_change_trackings as bct')
            ->whereRaw('bct.beneficiary_id = b.beneficiary_id')
            ->where('bct.change_type_id', 3)
            ->when(!is_null($status), function ($q) use ($status) {
                $q->where('bct.status', $status);
            }, function ($q) {
                $q->whereNull('bct.status');
            });
    });
}

private function applyAgeFilterQB(&$query, Request $request)
{
    if (!$request->has('age_range')) return;

    $ageRange = $request->query('age_range');

    if (is_array($ageRange)) {
        if (count($ageRange) === 2) {
            if (!($ageRange[0] == 1 && $ageRange[1] == 130)) {
                $query->whereBetween('b.age', $ageRange);
            }
        } elseif (count($ageRange) === 1) {
            $query->where('b.age', $ageRange[0]);
        }
    }
}

private function applyLocationFilterQB($query, $request): mixed
{
    $user = auth()->user()->load('assign_location.parent.parent.parent.parent', 'unions', 'userWards');

    $assignedLocationId = $user->assign_location?->id;
    $subLocationType = $user->assign_location?->location_type;
    $locationType = $user->assign_location?->type;

    $division_id = $request->query('division_id');
    $district_id = $request->query('district_id');
    $location_type_id = $request->query('location_type_id');
    $city_corp_id = $request->query('city_corp_id');
    $district_pourashava_id = $request->query('district_pourashava_id');
    $upazila_id = $request->query('upazila_id');
    $pourashava_id = $request->query('pourashava_id');
    $thana_id = $request->query('thana_id');
    $union_id = $request->query('union_id');
    $ward_id = $request->query('ward_id');

    // Force filters by assigned location
    if ($user->assign_location) {
        switch ($locationType) {
            case 'ward':
                $ward_id = $assignedLocationId;
                break;
            case 'union':
                $union_id = $assignedLocationId;
                break;
            case 'pouro':
                $pourashava_id = $assignedLocationId;
                break;
            case 'thana':
                if ($subLocationType == 2) {
                    $upazila_id = $assignedLocationId;
                } elseif ($subLocationType == 3) {
                    $thana_id = $assignedLocationId;
                } else {
                    $query->whereRaw('1 = 0'); // invalid
                }
                break;
            case 'city':
                if ($subLocationType == 1) {
                    $district_pourashava_id = $assignedLocationId;
                } elseif ($subLocationType == 3) {
                    $city_corp_id = $assignedLocationId;
                } else {
                    $query->whereRaw('1 = 0'); // invalid
                }
                break;
            case 'district':
                $district_id = $assignedLocationId;
                break;
            case 'division':
                $division_id = $assignedLocationId;
                break;
            default:
                // $query->whereRaw('1 = 0'); // invalid
        }
    }

    // Apply filters using aliases
    if ($division_id && $division_id > 0)
        $query->where('b.permanent_division_id', $division_id);
    if ($district_id && $district_id > 0)
        $query->where('b.permanent_district_id', $district_id);
    if ($location_type_id && $location_type_id > 0)
        $query->where('b.permanent_location_type_id', $location_type_id);
    if ($city_corp_id && $city_corp_id > 0)
        $query->where('b.permanent_city_corp_id', $city_corp_id);
    if ($district_pourashava_id && $district_pourashava_id > 0)
        $query->where('b.permanent_district_pourashava_id', $district_pourashava_id);
    if ($upazila_id && $upazila_id > 0)
        $query->where('b.permanent_upazila_id', $upazila_id);
    if ($pourashava_id && $pourashava_id > 0)
        $query->where('b.permanent_pourashava_id', $pourashava_id);
    if ($thana_id && $thana_id > 0)
        $query->where('b.permanent_thana_id', $thana_id);
    if ($union_id && $union_id > 0)
        $query->where('b.permanent_union_id', $union_id);
    if ($ward_id && $ward_id > 0)
        $query->where('b.permanent_ward_id', $ward_id);

    // Apply permissions from user-unions or pouros
    $union_ids = $user->unions->where('type', 'union')->pluck('id')->toArray();
    $pouro_ids = $user->unions->where('type', 'pouro')->pluck('id')->toArray();

    if (!empty($union_ids) && !empty($pouro_ids)) {
        $query->where(function ($q) use ($union_ids, $pouro_ids) {
            $q->whereIn('b.permanent_union_id', $union_ids)
              ->orWhereIn('b.permanent_pourashava_id', $pouro_ids);
        });
    } elseif (!empty($union_ids)) {
        $query->whereIn('b.permanent_union_id', $union_ids);
    } elseif (!empty($pouro_ids)) {
        $query->whereIn('b.permanent_pourashava_id', $pouro_ids);
    }

    // Apply user-assigned ward filter
    $ward_ids = $user->userWards->pluck('id')->toArray();
    if (!empty($ward_ids)) {
        $query->whereIn('b.permanent_ward_id', $ward_ids);
    }

    return $query;
}

    private function mapBeneficiaryData($beneficiaries, $currentFinancialYear, $status, $duplicates)
    {
        $beneficiaries->map(function ($beneficiary) use ($currentFinancialYear, $status, $duplicates) {
            $beneficiary->is_new_beneficiary = false;
            $beneficiary->is_regular_beneficiary = false;
            $beneficiary->is_verified = false;
            $beneficiary->is_account_changed = false;
            $beneficiary->is_replaced = $beneficiary->is_replaced ?? false;

            if ($currentFinancialYear && $beneficiary->approve_date) {
                $approveDate = Carbon::parse($beneficiary->approve_date);
                $start = Carbon::parse($currentFinancialYear->start_date);
                $end = Carbon::parse($currentFinancialYear->end_date);

                $beneficiary->is_new_beneficiary = $approveDate->between($start, $end);
                $beneficiary->is_regular_beneficiary = $approveDate->lt($start);
            }

            // Check if account changed this installment
            $accountChange = BeneficiaryChangeTracking::query()
                ->join('beneficiary_change_types', 'beneficiary_change_types.id', '=', 'beneficiary_change_trackings.change_type_id')
                ->where('beneficiary_change_trackings.beneficiary_id', $beneficiary->id)
                ->where('beneficiary_change_types.keyword', 'ACCOUNT_CHANGE')
                ->orderBy('beneficiary_change_trackings.created_at', 'desc')
                ->first();

            if ($accountChange) {
                $currentMonth = now()->month;
                $changeMonth = Carbon::parse($accountChange->created_at)->month;
                $cycle = $beneficiary->program?->payment_cycle ?? 'Monthly';

                $factor = match ($cycle) {
                    'Monthly' => 12,
                    'Quarterly' => 4,
                    'Half Yearly' => 2,
                    'Yearly' => 1,
                    default => 12
                };

                $currentInstallment = floor(($currentMonth * $factor) / 12);
                $changeInstallment = floor(($changeMonth * $factor) / 12);

                $beneficiary->is_account_changed = $currentInstallment === $changeInstallment;
            }
            $beneficiary->is_duplicate = isset($duplicates[$this->benHash($beneficiary, true)]);

            // Is Verified (from log table)
            $isVerified = BeneficiaryVerifyLog::query()
                ->where('beneficiary_id', $beneficiary->id)
                ->where('financial_year_id', $currentFinancialYear->id)
                ->exists();

            $beneficiary->is_verified = $isVerified && $beneficiary->is_verified;

            // Load inactive cause if status = 2
            if ($status == '2' && $beneficiary->inactive_cause_id) {
                $beneficiary->inactive_cause = Lookup::find($beneficiary->inactive_cause_id);
            }
        });
    }


    /**
     * @param Request $request
     * @return mixed
     */
    public function listDropDown(Request $request): mixed
    {
        $srcText = $request->query('srcText');
        $query = Beneficiary::query();
        $query = $query->where('status', 1); // only active
        if ($srcText)
            $query = $query->where('application_id', 'like', '%' . $srcText . '%');
        $query = $this->applyLocationFilter($query, $request);
        return $query->orderBy("application_id", "asc")->take(100)->get();
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function detail($id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $beneficiary = Beneficiary::query()->with('program',
            'gender',
            'ben_nationality',
            'ben_education_status',
            'ben_profession',
            'ben_religion',
            'ben_marital_status',
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
            'financialYear',
            'getBeneficiaryChangeTrackingAccountChange',
            // 'application.allowAddiFields.allowAddiFieldValues',
            )
            ->find($id);

        [$beneficiary->total_instalments, $beneficiary->total_payment_received, $beneficiary->last_payment_date, $beneficiary->last_payment_amount] = array_values($this->getPaymentSummary($beneficiary->beneficiary_id));

        $beneficiary->monthly_allowance = $this->getAllowanceAmount($beneficiary);

        $beneficiary->last_payroll_date = $this->lastPayrollDate($beneficiary->beneficiary_id);
        return $beneficiary;
    }

    public function getPmtData($beneficiaryId)
    {
        $pmt = BeneficiaryPovertyValue::where('beneficiary_id', $beneficiaryId)->first();

        if (!$pmt) {
            return [];
        }

        $decoded = json_decode($pmt->values, true);
        $values = $decoded['values'] ?? $decoded;

        if (empty($values) || !is_array($values)) {
            return [];
        }

        $variableIds = array_keys($values);
        $optionIds = collect($values)->flatten()->unique()->toArray();

        $variables = Variable::whereIn('id', $variableIds)->get()->keyBy('id');
        $options = Variable::whereIn('id', $optionIds)->get()->keyBy('id');

        $pmtData = [];

        foreach ($values as $varId => $optIds) {
            $var = $variables[$varId] ?? null;

            $question = [
                'id'         => (int) $varId,
                'name_en'    => $var->name_en ?? 'Unknown',
                'name_bn'    => $var->name_bn ?? 'অজানা প্রশ্ন',
                'parent_id'  => $var->parent_id ?? null,
                'field_type' => $var->field_type ?? null,
            ];

            $answers = [];

            foreach ($optIds as $optId) {
                $opt = $options[$optId] ?? null;
                if ($opt && (int) $opt->parent_id === (int) $varId) {
                    $answers[] = [
                        'id'         => $optId,
                        'name_en'    => $opt->name_en ?? 'Unknown',
                        'name_bn'    => $opt->name_bn ?? 'অজানা উত্তর',
                        'parent_id'  => $opt->parent_id ?? null,
                        'field_type' => $opt->field_type ?? null,
                    ];
                }
            }

            $pmtData[] = [
                'header' => $question,
                'values' => $answers,
            ];
        }
//        Log::info('Beneficiary PMT Data:', ['pmtData' => $pmtData]);

        return $pmtData;
    }

    private function lastPayrollDate($beneficiary_id){
        $payrollDetails =  PayrollDetail::where("beneficiary_id", $beneficiary_id)->with(['payroll.financialYear', 'payroll.installmentSchedule'])->latest()->first();
        if($payrollDetails){
            Log::debug($payrollDetails->payroll->financialYear);
            $range = $payrollDetails->payroll->installmentSchedule->getDateRangeById($payrollDetails->payroll->financialYear->start_date);
            return $range['end_date'];
        }
        return null;
    }

    /**
     * @param Beneficiary|null $beneficiary
     * @return mixed
     */
    public function getAllowanceAmount(?Beneficiary $beneficiary)
    {
        $program = $beneficiary->program;
        $per_beneficiary_amount = 0;
        if ($program->is_age_limit)
            $per_beneficiary_amount = AllowanceProgramAge::query()
                ->where('allowance_program_id', $program->id)
                ->when($beneficiary->age != null, function ($query) use ($beneficiary) {
                    $query->where('min_age', '<=', $beneficiary->age)
                        ->where('max_age', '>=', $beneficiary->age);
                })
                ->first()
                ?->value('amount')??0;
        else
            $per_beneficiary_amount = AllowanceProgramAmount::query()
                ->where('allowance_program_id', $program->id)
                ->when($beneficiary->type_id != null, function ($query) use ($beneficiary) {
                    $query->where('type_id', $beneficiary->type_id);
                })
                ->first()
                ?->value('amount')??0;
        return $per_beneficiary_amount;
    }

    /**
     * @param $id
     * @return mixed
     */
    public function get($id): mixed
    {
        return Beneficiary::with('program')->find($id);
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function idCard($id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return Beneficiary::query()
            ->with('program',
                'gender',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard')
            ->find($id);
    }

    /**
     * @param $beneficiary_id
     * @return mixed
     */
    public function getPaymentSummary($beneficiary_id): mixed
    {
        $total = PayrollPaymentCycleDetail::query()
            ->selectRaw('count(beneficiary_id) as total_instalments, sum(amount) as total_payment_received')
            ->where('beneficiary_id', $beneficiary_id)
            ->where('is_payment_disbursed', true)
            ->groupBy('beneficiary_id')
            ->first();
        $last = PayrollPaymentCycleDetail::query()
            ->where('beneficiary_id', $beneficiary_id)
            ->where('is_payment_disbursed', true)
            ->orderBy("payment_disbursed_at", "desc")
            ->first();
        return [
            'total_instalments' => $total?->total_instalments ?: 0,
            'total_payment_received' => $total?->total_payment_received ?: 0,
            'last_payment_date' => $last?->payment_disbursed_at,
            'last_payment_amount' => $last?->amount ?: 0
        ];
    }

    /**
     * @param $beneficiary_id
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getPaymentHistory($beneficiary_id): mixed
    {

        $Beneficiary = Beneficiary::with(
            'PayrollDetails.payroll.financialYear',
            'PayrollDetails.payroll.installmentSchedule',
            'PayrollDetails.payroll.office',
            'PayrollDetails.beneficiaryPayrollPaymentStatusLog.status',
            'PayrollDetails.beneficiaryPayrollPaymentStatusLog.user',
            )
            ->where('beneficiary_id', $beneficiary_id)
            ->first();
        return $Beneficiary;
    }

    /**
     * @param $beneficiary_id
     * @return array
     */
    public function getGrievanceSummary($beneficiary_id): array
    {
        $total = Grievance::query()
            ->selectRaw('count(beneficiary_id) as total_grievance, max(created_at) as last_grievance_at')
            ->where('beneficiary_id', $beneficiary_id)
            ->first();
        $last = Grievance::query()
            ->where('beneficiary_id', $beneficiary_id)
            ->with('grievanceType')
            ->orderBy('created_at', 'desc')
            ->first();
        return [
            'total_grievance' => $total?->total_grievance ?: 0,
            'last_grievance_type_en' => $last?->grievanceType?->title_en,
            'last_grievance_type_bn' => $last?->grievanceType?->title_bn
        ];
    }

    /**
     * @param $beneficiary_id
     * @return mixed
     */
    public function getGrievanceHistory($beneficiary_id): mixed
    {
        $grievance = Grievance::query()
            ->where('beneficiary_id', $beneficiary_id)
            ->with('grievanceType', 'grievanceSubject') //, 'resolver'
            ->get();
        $grievance->map(function ($item) {
            $item->resolveDetail = GrievanceStatusUpdate::query()
                ->where('grievance_id', $item->id)
                ->where('status', '2')
                ->orderBy('created_at', 'desc')
                ->with('role')
                ->first();
        });
        return $grievance;
    }

    /**
     * @param $beneficiary_id
     * @return mixed
     */
    public function getChangeTrackingSummary($beneficiary_id): mixed
    {
        $total_changes = BeneficiaryChangeTracking::query()->where('beneficiary_id', $beneficiary_id)->count();
        return [
            'total_changes' => $total_changes
        ];
    }

    /**
     * @param $beneficiary_id
     * @return \Illuminate\Database\Eloquent\Collection|array
     */
    public function getChangeTrackingHistory($beneficiary_id): \Illuminate\Database\Eloquent\Collection|array
    {
        return BeneficiaryChangeTracking::query()
            ->selectRaw('beneficiary_change_types.name_en as change_type_en, beneficiary_change_types.name_bn as change_type_bn, count(beneficiary_id) as total_changes')
            ->join('beneficiary_change_types', 'beneficiary_change_types.id', '=', 'beneficiary_change_trackings.change_type_id')
            ->where('beneficiary_id', $beneficiary_id)
            ->groupByRaw('beneficiary_change_types.name_en, beneficiary_change_types.name_bn')
            ->get();
    }


    /**
     * @param $beneficiary_id
     * @return array
     */
    public function getNomineeChangeHistory($beneficiary_id): array
    {
        $results = BeneficiaryChangeTracking::query()
            ->join('beneficiary_change_types', 'beneficiary_change_types.id', '=', 'beneficiary_change_trackings.change_type_id')
            ->where('beneficiary_id', $beneficiary_id)
            ->where('beneficiary_change_types.keyword', 'NOMINEE_CHANGE')
            ->orderBy('beneficiary_change_types.created_at', 'desc')
            ->get();
        $attributes = [
            'nominee_en' => '',
            'nominee_bn' => '',
            'nominee_verification_number' => '',
            'nominee_address' => '',
            'nominee_image' => '',
            'nominee_signature' => '',
            'nominee_relation_with_beneficiary' => '',
            'nominee_nationality' => '',
        ];
        $changeLogs = [];
        foreach ($results as $result) {
            $change_value = json_decode($result->change_value, true);
            $change_value['changed_at'] = Carbon::parse($result->created_at)->format('d/m/Y h:i:s A');
            $changeLogs[] = array_merge($attributes, $change_value);
        }
        return $changeLogs;
    }

    /**
     * @param $beneficiary_id
     * @return array
     */
    public function getAccountChangeHistory($beneficiary_id): array
    {
        $results = BeneficiaryChangeTracking::query()
            ->join('beneficiary_change_types', 'beneficiary_change_types.id', '=', 'beneficiary_change_trackings.change_type_id')
            ->where('beneficiary_id', $beneficiary_id)
            ->where('beneficiary_change_types.keyword', 'ACCOUNT_CHANGE')
            ->orderByDesc('beneficiary_change_trackings.created_at')
            ->get(['beneficiary_change_trackings.*']);

        // Collect all unique bank and branch IDs to minimize DB queries
        $bankIds = [];
        $branchIds = [];
        $mfsIds = [];

        foreach ($results as $result) {
            $change = json_decode($result->change_value, true);
            if (!is_array($change)) continue;

            if (($change['account_type'] ?? null) == '1') {
                if (!empty($change['bank_id'])) $bankIds[] = $change['bank_id'];
                if (!empty($change['bank_branch_id'])) $branchIds[] = $change['bank_branch_id'];
            } elseif (($change['account_type'] ?? null) == '2') {
                if (!empty($change['mfs_id'])) $mfsIds[] = $change['mfs_id'];
            }
        }


        $banks = Bank::query()->findMany(array_unique($bankIds))->keyBy('id');
        $mfses = Mfs::query()->findMany(array_unique($mfsIds))->keyBy('id');
        Log::info($mfses->toJson());
        $branches = BankBranch::query()->findMany(array_unique($branchIds))->keyBy('id');

        $changeLogs = [];

        foreach ($results as $result) {
            $change_value = json_decode($result->change_value, true);
            if (!is_array($change_value)) continue;
            $owner = $change_value['account_owner'] != null? Lookup::find($change_value['account_owner']) : null;
            $data = [
                'account_name' => $change_value['account_name'] ?? '',
                'account_owner_en' => $owner?->value_en ?? '',
                'account_owner_bn' => $owner?->value_bn ?? '',
                'account_number' => $change_value['account_number'] ?? '',
                'bank_name_en' => '',
                'bank_name_bn' => '',
                'branch_name_en' => '',
                'branch_name_bn' => '',
                'changed_at' => Carbon::parse($result->created_at)->format('d/m/Y h:i:s A'),
            ];

            if (($change_value['account_type'] ?? null) == '1') {
                $bank = $banks[$change_value['bank_id']] ?? null;
                $branch = $branches[$change_value['bank_branch_id']] ?? null;

                $data['bank_name_en'] = $bank->name_en ?? '';
                $data['bank_name_bn'] = $bank->name_bn ?? '';
                $data['branch_name_en'] = $branch->name_en ?? '';
                $data['branch_name_bn'] = $branch->name_bn ?? '';
            } elseif (($change_value['account_type'] ?? null) == '2') {
                $mfs = $mfses[$change_value['mfs_id']] ?? null;
                $data['bank_name_en'] = $mfs->name_en ?? '';
                $data['bank_name_bn'] = $mfs->name_bn ?? '';
            }

            $changeLogs[] = $data;
        }

        return $changeLogs;
    }


    /**
     * @param $beneficiary_id
     * @return mixed
     */
    public function getByBeneficiaryId($beneficiary_id): mixed
    {
        return Beneficiary::with('program')->where('beneficiary_id', $beneficiary_id)->first();
    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function edit($id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return Beneficiary::with('program',
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
            'financialYear')
            ->findOrFail($id);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function update(UpdateBeneficiaryRequest $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beforeUpdate = $beneficiary->replicate();
            $validatedData = $request->only([
                'nominee_en',
                'nominee_bn',
                'nominee_verification_number',
                'nominee_address',
                'nominee_relation_with_beneficiary',
                'nominee_nationality',
                'account_name',
                'account_owner',
                'account_number',
                'financial_year_id',
                'account_type',
                'bank_name',
                'branch_name',
                'monthly_allowance'
            ]);
            $beneficiary->fill($validatedData);

            if ($request->hasFile('nominee_image'))
                $beneficiary->nominee_image = $request->file('nominee_image')->store('public');

            if ($request->hasFile('nominee_signature'))
                $beneficiary->nominee_signature = $request->file('nominee_signature')->store('public');

            $beneficiary->save();

            // change log
            $changes = $beneficiary->getChanges();
            $nomineeOldValues = [];
            $nomineeNewValues = [];
            $nomineeAttributes = [
                'nominee_en',
                'nominee_bn',
                'nominee_verification_number',
                'nominee_address',
                'nominee_relation_with_beneficiary',
                'nominee_nationality'];
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
                'monthly_allowance'];
            foreach ($changes as $attribute => $newValue) {
                if (in_array($attribute, $nomineeAttributes)) {
                    $nomineeOldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                    $nomineeNewValues[$attribute] = $newValue;
                }
                if (in_array($attribute, $accountAttributes)) {
                    $accountOldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                    $accountNewValues[$attribute] = $newValue;
                }
            }
            if (count($nomineeNewValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'NOMINEE_CHANGE')->first();
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $id,
                    'change_type_id' => $changeType->id,
                    'previous_value' => json_encode($nomineeOldValues),
                    'change_value' => json_encode($nomineeNewValues),
                ]);
            }
            if (count($accountNewValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'ACCOUNT_CHANGE')->first();
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $id,
                    'change_type_id' => $changeType->id,
                    'previous_value' => json_encode($accountOldValues),
                    'change_value' => json_encode($accountNewValues),
                ]);
            }
            // change log end

            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary Updated!");

            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function updatePersonalInfo(UpdatePersonalInfoRequest $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beforeUpdate = $beneficiary->replicate();
            $attributes = [
                'name_en',
                'name_bn',
                'mother_name_en',
                'mother_name_bn',
                'father_name_en',
                'father_name_bn',
                'spouse_name_en',
                'spouse_name_bn',
                'identification_mark',
                'nationality',
                'gender_id',
                'religion',
                'marital_status',
                'education_status',
                'profession',
//            'date_of_birth',
                'image',
                'signature'
            ];
            $validatedData = $request->only($attributes);
            $beneficiary->fill($validatedData);

            if ($request->hasFile('image'))
                $beneficiary->nominee_image = $request->file('image')->store('public');

            if ($request->hasFile('signature'))
                $beneficiary->nominee_signature = $request->file('signature')->store('public');

            $beneficiary->save();

            // change log
            $changes = $beneficiary->getChanges();
            $oldValues = [];
            $newValues = [];

            foreach ($changes as $attribute => $newValue) {
                if (in_array($attribute, $attributes)) {
                    $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                    $newValues[$attribute] = $newValue;
                }
            }
            if (count($newValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'PERSONAL_INFO_CHANGE')->firstOrFail();
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $id,
                    'change_type_id' => $changeType?->id,
                    'previous_value' => json_encode($oldValues),
                    'change_value' => json_encode($newValues),
                ]);
            }
            // change log end

            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary Updated!");

            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function updateContactInfo(UpdateContactInfoRequest $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beforeUpdate = $beneficiary->replicate();
            $attributes = [
                'current_division_id',
                'current_district_id',
                'current_city_corp_id',
                'current_district_pourashava_id',
                'current_upazila_id',
                'current_pourashava_id',
                'current_thana_id',
                'current_union_id',
                'current_ward_id',
                'current_post_code',
                'current_address',
                'permanent_division_id',
                'permanent_district_id',
                'permanent_city_corp_id',
                'permanent_district_pourashava_id',
                'permanent_upazila_id',
                'permanent_pourashava_id',
                'permanent_thana_id',
                'permanent_union_id',
                'permanent_ward_id',
                'permanent_post_code',
                'permanent_address',
                'mobile',
                'email',
            ];
            $validatedData = $request->only($attributes);
            $beneficiary->fill($validatedData);

            $beneficiary->save();

            // change log
            $changes = $beneficiary->getChanges();
            $oldValues = [];
            $newValues = [];

            foreach ($changes as $attribute => $newValue) {
                if (in_array($attribute, $attributes)) {
                    $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                    $newValues[$attribute] = $newValue;
                }
            }
            if (count($newValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'CONTACT_INFO_CHANGE')->firstOrFail();
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $id,
                    'change_type_id' => $changeType?->id,
                    'previous_value' => json_encode($oldValues),
                    'change_value' => json_encode($newValues),
                ]);
            }
            // change log end

            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary Updated!");

            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function updateNomineeInfo(UpdateNomineeInfoRequest $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beforeUpdate = $beneficiary->replicate();
            $attributes = [
                'nominee_en',
                'nominee_bn',
                'nominee_verification_number',
                'nominee_address',
                'nominee_image',
                'nominee_signature',
                'nominee_relation_with_beneficiary',
                'nominee_nationality',
            ];
            $validatedData = $request->only($attributes);
            $beneficiary->fill($validatedData);

            if ($request->hasFile('nominee_image'))
                $beneficiary->nominee_image = $request->file('nominee_image')->store('public');

            if ($request->hasFile('nominee_signature'))
                $beneficiary->nominee_signature = $request->file('nominee_signature')->store('public');

            $beneficiary->save();

            // change log
//            $changes = $beneficiary->getChanges();
            $oldValues = [];
            $newValues = [];

//            foreach ($changes as $attribute => $newValue) {
//                if (in_array($attribute, $attributes)) {
//                    $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
//                    $newValues[$attribute] = $newValue;
//                }
//            }
            foreach ($attributes as $attribute) {
                $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                $newValues[$attribute] = $beneficiary->$attribute ?? null;
            }
            if (count($newValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'NOMINEE_CHANGE')->firstOrFail();
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $id,
                    'change_type_id' => $changeType?->id,
                    'previous_value' => json_encode($oldValues),
                    'change_value' => json_encode($newValues),
                ]);
            }
            // change log end

            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary Updated!");

            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function updateAccountInfo(UpdateAccountInfoRequest $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beforeUpdate = $beneficiary->replicate();
            $attributes = [
                'account_type',
                'bank_id',
                'mfs_id',
                'bank_branch_id',
                'account_name',
                'account_owner',
                'account_number',
                'financial_year_id',
                'monthly_allowance'
            ];
            $validatedData = $request->only($attributes);
            $beneficiary->fill($validatedData);

//            $beneficiary->save();

            // change log
//            $changes = $beneficiary->getChanges();
            $oldValues = [];
            $newValues = [];

//            foreach ($changes as $attribute => $newValue) {
//                if (in_array($attribute, $attributes)) {
//                    $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
//                    $newValues[$attribute] = $newValue;
//                }
//            }
            foreach ($attributes as $attribute) {
                $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                $newValues[$attribute] = $beneficiary->$attribute ?? null;
            }
            if (count($newValues) > 0) {
                $changeType = BeneficiaryChangeType::query()->where('keyword', 'ACCOUNT_CHANGE')->firstOrFail();

                $changeTracking = BeneficiaryChangeTracking::query()
                    ->where('beneficiary_id', $id)
                    ->where('change_type_id', 3) // for account change
                    ->where('status', 1)
                    ->first();

                if($changeTracking){
                    $changeTracking->previous_value = json_encode($oldValues);
                    $changeTracking->change_value = json_encode($newValues);
                    $changeTracking->created_by = auth()->user();
                    $changeTracking->save();
                }else{
                    BeneficiaryChangeTracking::create([
                        'beneficiary_id' => $id,
                        'change_type_id' => $changeType?->id,
                        'previous_value' => json_encode($oldValues),
                        'change_value' => json_encode($newValues),
                        'status' => 1,
                        'created_by' => auth()->user(),
                    ]);
                }

                // foreach ($changeTrackings as $changeTracking) {
                //     $changeTracking->delete();
                // }


            }
            // change log end

            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary Updated ACCOUNT CHANGE Request!");

            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function inactive(Request $request, $id): mixed
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beneficiary->status = 2; // Inactive
            $beneficiary->inactive_cause_id = $request->input('cause_id'); // Inactive cause
            $beneficiary->updated_at = now();
            $beneficiary->save();
            Helper::activityLogDelete($beneficiary, '', 'Beneficiary', 'Beneficiary Inactive!!');
            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function delete(Request $request, $id): mixed
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beneficiary->delete_cause = $request->input('delete_cause');
            $beneficiary->save();
            $beneficiary->delete();
            Helper::activityLogDelete($beneficiary, '', 'Beneficiary', 'Beneficiary Deleted!!');
            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $forPdf
     * @return mixed
     */
    public function deletedList(Request $request, $forPdf = false)
    {
        $main_program_id = $request->query('main_program_id', []);
        $program_id = $request->query('program_id', []);

        $beneficiary_id = $request->query('beneficiary_id');
        $nominee_name = $request->query('nominee_name');
        $account_number = $request->query('account_number');
        $verification_number = $request->query('nid');
        $status = $request->query('status');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'deleted_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = Beneficiary::query()->onlyTrashed();

        $query->when($program_id, function($q) use($program_id, $request){
            if(is_array($program_id)){
                $q->whereIn('program_id', $program_id);
            }else{
                $q->where('program_id', $program_id);
            }
        });

        $query->when($main_program_id, function($q) use($main_program_id, $request){
            if(is_array($main_program_id)){
                $q->whereIn('main_program_id', $main_program_id);
            }else{
                $q->where('main_program_id', $main_program_id);
            }
        });

        $query = $this->applyLocationFilter($query, $request);

        // advance search
        if (!empty($beneficiary_id))
            $query = $query->whereIn('application_id', $beneficiary_id);
        if ($nominee_name)
            $query = $query->whereRaw('UPPER(nominee_en) LIKE "%' . strtoupper($nominee_name) . '%"');
        if ($account_number)
            $query = $query->where('account_number', $account_number);
        if ($verification_number)
            $query = $query->where('verification_number', $verification_number);
        if ($status)
            $query = $query->where('status', $status);

        if ($forPdf)
            return $query->with('program',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard')->orderBy("$sortByColumn", "$orderByDirection")->get();
        else
            return $query->with('program',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard')->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function restore($id)
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::withTrashed()->findOrFail($id);
            $beneficiaryBeforeUpdate = $beneficiary->replicate();
            $beneficiary->delete_cause = '';
            $beneficiary->save();
            Helper::activityLogUpdate($beneficiary, $beneficiaryBeforeUpdate, "Beneficiary", "Beneficiary Restored!");
            Beneficiary::withTrashed()->find($id)->restore();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Throwable
     */
    public function restoreInactive($id)
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beforeUpdate = $beneficiary->replicate();
            $beneficiary->status = 1; // Active
            $beneficiary->inactive_cause_id = null;
            $beneficiary->save();
            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Inactive Beneficiary Restored!");
            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Throwable
     */
    public function restoreExit($id)
    {
        DB::beginTransaction();
        try {
            $beneficiaryExit = BeneficiaryExit::findOrFail($id);
            $beneficiary = Beneficiary::findOrFail($beneficiaryExit->beneficiary_id);
            $beforeUpdate = $beneficiary->replicate();
            $beneficiary->status = $beneficiaryExit->previous_status ?: 1;
            $beneficiary->save();
            Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Exited Beneficiary Restored!");
            $deleted = $beneficiaryExit->delete();
            DB::commit();
            return $deleted;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $id
     * @return bool
     * @throws \Throwable
     */
    public function restoreReplace($id)
    {
        DB::beginTransaction();
        try {
            $beneficiaryReplace = BeneficiaryReplace::findOrFail($id);

            $replacedBeneficiary = Beneficiary::where('beneficiary_id', '=', $beneficiaryReplace->beneficiary_id)->first();
            $replacedBeneficiaryBeforeUpdate = $replacedBeneficiary->replicate();
            $replacedBeneficiary->status = 1; // Active
            $replacedBeneficiary->inactive_cause_id = null;
            $replacedBeneficiary->save();
            Helper::activityLogUpdate($replacedBeneficiary, $replacedBeneficiaryBeforeUpdate, "Beneficiary", "Replaced Beneficiary Restored!");

            $replacedWithBeneficiary = Beneficiary::where('beneficiary_id', '=', $beneficiaryReplace->replace_with_ben_id)->first();
            $replacedWithBeneficiaryBeforeUpdate = $replacedWithBeneficiary->replicate();
            $replacedWithBeneficiary->status = 3; // Waiting
            $replacedWithBeneficiary->save();
            Helper::activityLogUpdate($replacedWithBeneficiary, $replacedWithBeneficiaryBeforeUpdate, "Beneficiary", "Replaced Beneficiary Restored!");

            $beneficiaryReplace->delete();

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function getListForReplace(Request $request): \Illuminate\Contracts\Pagination\Paginator
    {
        $exclude_beneficiary_id = $request->query('exclude_beneficiary_id');

        $beneficiary_id = $request->query('beneficiary_id');
        $nominee_name = $request->query('nominee_name');
        $account_number = $request->query('account_number');
        $verification_number = $request->query('nid');
        $status = $request->query('status');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $exclude_beneficiary = Beneficiary::find($exclude_beneficiary_id);

        $main_program_id = $exclude_beneficiary->main_program_id;
        $program_id = $exclude_beneficiary->program_id;

        $division_id    = $exclude_beneficiary->permanent_division_id ;
        $district_id    = $exclude_beneficiary->permanent_district_id ;
        $city_corp_id   = $exclude_beneficiary->permanent_city_corp_id ;
        $district_pourashava_id     = $exclude_beneficiary->permanent_district_pourashava_id ;
        $upazila_id     = $exclude_beneficiary->permanent_upazila_id;
        $pourashava_id  = $exclude_beneficiary->permanent_pourashava_id;
        $union_id       = $exclude_beneficiary->permanent_union_id;
        $ward_id        = $exclude_beneficiary->permanent_ward_id;

        $query = Beneficiary::query();
        if ($exclude_beneficiary_id)
            $query = $query->where('id', '!=', $exclude_beneficiary_id);

        // Default Program and location search
        if ($main_program_id)
            $query = $query->where('main_program_id', $main_program_id);
        if ($program_id)
            $query = $query->where('program_id', $program_id);
        if ($division_id)
            $query = $query->where('permanent_division_id', $division_id);
        if ($district_id)
            $query = $query->where('permanent_district_id', $district_id);
        if ($city_corp_id)
            $query = $query->where('permanent_city_corp_id', $city_corp_id)
                            ->where('permanent_ward_id', $ward_id);
        if ($district_pourashava_id)
            $query = $query->where('permanent_district_pourashava_id', $district_pourashava_id);
        if ($upazila_id)
            $query = $query->where('permanent_upazila_id', $upazila_id);
        if ($pourashava_id)
            $query = $query->where('permanent_pourashava_id', $pourashava_id);
//        if ($thana_id)
//            $query = $query->where('permanent_thana_id', $thana_id);
//        if ($thana_id)
//            $query = $query->where(function ($q) use ($thana_id) {
//                $q->where('permanent_thana_id', $thana_id)
//                    ->orWhere('permanent_upazila_id', $thana_id);
//            });
        if ($union_id)
            $query = $query->where(function ($q) use ($union_id) {
                $q->where('permanent_union_id', $union_id)
                    ->orWhere('permanent_pourashava_id', $union_id);
            });

//        if ($union_id)
//            $query = $query->where('permanent_union_id', $union_id);

//        if ($ward_id)
//            $query = $query->where('permanent_ward_id', $ward_id);

        // advance search
        if ($beneficiary_id)
            $query->when($beneficiary_id, fn($q) =>
                $q->whereIn('beneficiary_id', is_array($beneficiary_id)
                    ? $beneficiary_id
                    : explode(',', $beneficiary_id)));
        if ($nominee_name)
            $query = $query->whereRaw('UPPER(nominee_en) LIKE "%' . strtoupper($nominee_name) . '%"');
        if ($account_number)
            $query = $query->where('account_number', $account_number);
        if ($verification_number)
            $query = $query->where('verification_number', $verification_number);
//        if ($status)
        $query = $query->where('status', 3); // only waiting beneficiaries


        return $query->with('program',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard')->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     * @throws \Throwable
     */
    public function replaceSave(Request $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            if ($id == $request->input('replace_with_ben_id')) {
                throw new Exception("Can not replace same beneficiary");
            }
            $beneficiary = Beneficiary::findOrFail($id);
//            $beforeUpdate = $beneficiary->replicate();

            $replaceWithBeneficiaryId = $request->input('replace_with_ben_id');
            $effective_date = $request->input('effective_date') ? Carbon::parse($request->input('effective_date')) : now();
            $beneficiaryReplaceWith = Beneficiary::findOrFail($replaceWithBeneficiaryId);
//            $beneficiaryReplaceWithBeforeUpdate = $beneficiaryReplaceWith->replicate();

            $beneficiary->status = 2; // Inactive
            $beneficiary->inactive_cause_id = $request->input('cause_id'); // Inactive cause
            $beneficiary->updated_at = now();
            $beneficiary->save();

//            Helper::activityLogUpdate($beneficiary, $beforeUpdate, 'Beneficiary', 'Beneficiary replaced with: '. $beneficiaryReplaceWithBeforeUpdate->name_en);

            $beneficiaryReplaceWith->status = 1; // Active
            $beneficiaryReplaceWith->is_replaced = 1;
            $beneficiaryReplaceWith->approve_date = $effective_date;
            $beneficiaryReplaceWith->payment_start_date = $effective_date;
            $beneficiaryReplaceWith->updated_at = now();
            $beneficiaryReplaceWith->save();

//            Helper::activityLogUpdate($beneficiaryReplaceWith, $beneficiaryReplaceWithBeforeUpdate, 'Beneficiary', 'Beneficiary replaced with: '. $beforeUpdate->name_en);

            $beneficiaryReplace = new BeneficiaryReplace();
//            $beneficiaryReplace->beneficiary_id = $id;
//            $beneficiaryReplace->replace_with_ben_id = $replaceWithBeneficiaryId;
            $beneficiaryReplace->beneficiary_id = $beneficiary->beneficiary_id;
            $beneficiaryReplace->replace_with_ben_id = $beneficiaryReplaceWith->beneficiary_id;
            $beneficiaryReplace->cause_id = $request->input('cause_id');
            $beneficiaryReplace->cause_detail = $request->input('cause_detail');
            $beneficiaryReplace->cause_date = $request->input('cause_date') ? Carbon::parse($request->input('cause_date')) : now();
            $beneficiaryReplace->effective_date = $effective_date;
            if ($request->hasFile('cause_proof_doc'))
                $beneficiaryReplace->cause_proof_doc = $request->file('cause_proof_doc')->store('beneficiary/attachment');
            $beneficiaryReplace->created_at = now();
            $beneficiaryReplace->save();

            Helper::activityLogInsert($beneficiaryReplace, '', 'Beneficiary Replace', 'Beneficiary: ' . $beneficiary->name_en . '(' . $beneficiary->application_id . ') replaced with: ' . $beneficiaryReplaceWith->name_en . '(' . $beneficiaryReplaceWith->application_id . ')');

            DB::commit();

            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $forPdf
     * @return mixed
     */
    public function replaceList(Request $request, $forPdf = false)
    {
        $program_id = $request->query('program_id');
        $sub_program_id = $request->query('sub_program_id');
        $cause_type = $request->query('cause_type');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'beneficiary_replaces.created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = DB::table('beneficiary_replaces')
            ->join('beneficiaries', 'beneficiaries.beneficiary_id', '=', 'beneficiary_replaces.beneficiary_id')
            ->join('allowance_programs as main_allowance_programs', 'main_allowance_programs.id', '=', 'beneficiaries.main_program_id')
            ->join('allowance_programs', 'allowance_programs.id', '=', 'beneficiaries.program_id')
            ->join('locations AS division', 'division.id', '=', 'beneficiaries.permanent_division_id', 'left')
            ->join('locations AS district', 'district.id', '=', 'beneficiaries.permanent_district_id', 'left')
            ->join('locations AS city_corporation', 'city_corporation.id', '=', 'beneficiaries.permanent_city_corp_id', 'left')
            ->join('locations AS district_pourashava', 'district_pourashava.id', '=', 'beneficiaries.permanent_district_pourashava_id', 'left')
            ->join('locations AS upazila', 'upazila.id', '=', 'beneficiaries.permanent_upazila_id', 'left')
            ->join('beneficiaries AS replace_with_beneficiaries', 'replace_with_beneficiaries.beneficiary_id', '=', 'beneficiary_replaces.replace_with_ben_id')
            ->join('locations AS replace_with_division', 'replace_with_division.id', '=', 'replace_with_beneficiaries.permanent_division_id', 'left')
            ->join('locations AS replace_with_district', 'replace_with_district.id', '=', 'replace_with_beneficiaries.permanent_district_id', 'left')
            ->join('locations AS replace_with_city_corporation', 'replace_with_city_corporation.id', '=', 'replace_with_beneficiaries.permanent_city_corp_id', 'left')
            ->join('locations AS replace_with_district_pourashava', 'replace_with_district_pourashava.id', '=', 'replace_with_beneficiaries.permanent_district_pourashava_id', 'left')
            ->join('locations AS replace_with_upazila', 'replace_with_upazila.id', '=', 'replace_with_beneficiaries.permanent_upazila_id', 'left')
            ->join('locations AS replace_with_union', 'replace_with_union.id', '=', 'replace_with_beneficiaries.permanent_union_id', 'left')
            ->join('locations AS replace_with_thana', 'replace_with_thana.id', '=', 'replace_with_beneficiaries.permanent_thana_id', 'left')
            ->join('locations AS replace_with_pourashava', 'replace_with_pourashava.id', '=', 'replace_with_beneficiaries.permanent_pourashava_id', 'left')
            ->join('locations AS replace_with_ward', 'replace_with_ward.id', '=', 'replace_with_beneficiaries.permanent_ward_id', 'left')
            ->join('lookups AS replace_cause', 'replace_cause.id', '=', 'beneficiary_replaces.cause_id', 'left');
        $query = $query->whereNull('beneficiary_replaces.deleted_at');
        if ($program_id)
            $query = $query->where('beneficiaries.main_program_id', $program_id);
        if ($sub_program_id)
            $query = $query->where('beneficiaries.program_id', $sub_program_id);

        if ($cause_type)
            $query = $query->where('beneficiary_replaces.cause_id', $cause_type);

        $query = $this->applyLocationFilter2($query, $request);

        if ($forPdf)
            return $query->select('beneficiary_replaces.id',
                'beneficiaries.beneficiary_id as beneficiary_id',
                'replace_cause.value_en as replace_cause_en',
                'replace_cause.value_bn as replace_cause_bn',
                'beneficiary_replaces.cause_detail',
                'beneficiary_replaces.cause_date',
                'beneficiaries.application_id',
                'beneficiaries.name_en',
                'beneficiaries.name_bn',
                'beneficiaries.father_name_en',
                'beneficiaries.father_name_bn',
                'beneficiaries.mother_name_en',
                'beneficiaries.mother_name_bn',
                'main_allowance_programs.name_en as main_program_name_en',
                'main_allowance_programs.name_bn as main_program_name_bn',
                'allowance_programs.name_en as program_name_en',
                'allowance_programs.name_bn as program_name_bn',
                'division.name_en as division_name_en',
                'division.name_bn as division_name_bn',
                'district.name_en as district_name_en',
                'district.name_bn as district_name_bn',
                'city_corporation.name_en as city_corporation_name_en',
                'city_corporation.name_bn as city_corporation_name_bn',
                'district_pourashava.name_en as district_pourashava_name_en',
                'district_pourashava.name_bn as district_pourashava_name_bn',
                'upazila.name_en as upazila_name_en',
                'upazila.name_bn as upazila_name_bn',
                'replace_with_beneficiaries.beneficiary_id AS replace_with_beneficiary_id',
                'replace_with_beneficiaries.name_en AS replace_with_name_en',
                'replace_with_beneficiaries.name_bn AS replace_with_name_bn',
                'replace_with_beneficiaries.father_name_en AS replace_with_father_name_en',
                'replace_with_beneficiaries.father_name_bn AS replace_with_father_name_bn',
                'replace_with_beneficiaries.mother_name_en AS replace_with_mother_name_en',
                'replace_with_beneficiaries.mother_name_bn AS replace_with_mother_name_bn',
                'replace_with_division.name_en as replace_with_division_name_en',
                'replace_with_division.name_bn as replace_with_division_name_bn',
                'replace_with_district.name_en as replace_with_district_name_en',
                'replace_with_district.name_bn as replace_with_district_name_bn',
                'replace_with_city_corporation.name_en as replace_with_city_corporation_name_en',
                'replace_with_city_corporation.name_bn as replace_with_city_corporation_name_bn',
                'replace_with_district_pourashava.name_en as replace_with_district_pourashava_name_en',
                'replace_with_district_pourashava.name_bn as replace_with_district_pourashava_name_bn',
                'replace_with_upazila.name_en as replace_with_upazila_name_en',
                'replace_with_upazila.name_bn as replace_with_upazila_name_bn',
                'replace_with_union.name_en as replace_with_union_name_en',
                'replace_with_union.name_bn as replace_with_union_name_bn',
                'replace_with_thana.name_en as replace_with_thana_name_en',
                'replace_with_thana.name_bn as replace_with_thana_name_bn',
                'replace_with_pourashava.name_en as replace_with_pourashava_name_en',
                'replace_with_pourashava.name_bn as replace_with_pourashava_name_bn',
                'replace_with_ward.name_en as replace_with_ward_name_en',
                'replace_with_ward.name_bn as replace_with_ward_name_bn',
                'replace_with_beneficiaries.permanent_address as replace_with_address'
            )
                ->orderBy("$sortByColumn", "$orderByDirection")->get();
        else
            return $query->select('beneficiary_replaces.id',
                'beneficiaries.beneficiary_id as beneficiary_id',
                'replace_cause.value_en as replace_cause_en',
                'replace_cause.value_bn as replace_cause_bn',
                'beneficiary_replaces.cause_detail',
                'beneficiary_replaces.cause_date',
                'beneficiaries.application_id',
                'beneficiaries.name_en',
                'beneficiaries.name_bn',
                'beneficiaries.father_name_en',
                'beneficiaries.father_name_bn',
                'beneficiaries.mother_name_en',
                'beneficiaries.mother_name_bn',
                'main_allowance_programs.name_en as main_program_name_en',
                'main_allowance_programs.name_bn as main_program_name_bn',
                'allowance_programs.name_en as program_name_en',
                'allowance_programs.name_bn as program_name_bn',
                'division.name_en as division_name_en',
                'division.name_bn as division_name_bn',
                'district.name_en as district_name_en',
                'district.name_bn as district_name_bn',
                'city_corporation.name_en as city_corporation_name_en',
                'city_corporation.name_bn as city_corporation_name_bn',
                'district_pourashava.name_en as district_pourashava_name_en',
                'district_pourashava.name_bn as district_pourashava_name_bn',
                'upazila.name_en as upazila_name_en',
                'upazila.name_bn as upazila_name_bn',
                'replace_with_beneficiaries.beneficiary_id AS replace_with_beneficiary_id',
                'replace_with_beneficiaries.name_en AS replace_with_name_en',
                'replace_with_beneficiaries.name_bn AS replace_with_name_bn',
                'replace_with_beneficiaries.father_name_en AS replace_with_father_name_en',
                'replace_with_beneficiaries.father_name_bn AS replace_with_father_name_bn',
                'replace_with_beneficiaries.mother_name_en AS replace_with_mother_name_en',
                'replace_with_beneficiaries.mother_name_bn AS replace_with_mother_name_bn',
                'replace_with_division.name_en as replace_with_division_name_en',
                'replace_with_division.name_bn as replace_with_division_name_bn',
                'replace_with_district.name_en as replace_with_district_name_en',
                'replace_with_district.name_bn as replace_with_district_name_bn',
                'replace_with_city_corporation.name_en as replace_with_city_corporation_name_en',
                'replace_with_city_corporation.name_bn as replace_with_city_corporation_name_bn',
                'replace_with_district_pourashava.name_en as replace_with_district_pourashava_name_en',
                'replace_with_district_pourashava.name_bn as replace_with_district_pourashava_name_bn',
                'replace_with_upazila.name_en as replace_with_upazila_name_en',
                'replace_with_upazila.name_bn as replace_with_upazila_name_bn',
                'replace_with_union.name_en as replace_with_union_name_en',
                'replace_with_union.name_bn as replace_with_union_name_bn',
                'replace_with_thana.name_en as replace_with_thana_name_en',
                'replace_with_thana.name_bn as replace_with_thana_name_bn',
                'replace_with_pourashava.name_en as replace_with_pourashava_name_en',
                'replace_with_pourashava.name_bn as replace_with_pourashava_name_bn',
                'replace_with_ward.name_en as replace_with_ward_name_en',
                'replace_with_ward.name_bn as replace_with_ward_name_bn',
                'replace_with_beneficiaries.permanent_address as replace_with_address'
            )
                ->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }

    /**
     * @param Request $request
     * @param $forPdf
     * @return mixed
     */
    public function accountChangeList(Request $request, $forPdf = false)
    {
        $program_id = $request->query('program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'beneficiary_change_trackings.updated_at');
        $orderByDirection = $request->query('orderBy', 'desc');

        $query = DB::table('beneficiary_change_trackings')
            ->join('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_change_trackings.beneficiary_id')
            ->join('allowance_programs', 'allowance_programs.id', '=', 'beneficiaries.program_id')
            ->join('locations AS division', 'division.id', '=', 'beneficiaries.permanent_division_id', 'left')
            ->join('locations AS district', 'district.id', '=', 'beneficiaries.permanent_district_id', 'left')
            ->join('locations AS city_corporation', 'city_corporation.id', '=', 'beneficiaries.permanent_city_corp_id', 'left')
            ->join('locations AS district_pourashava', 'district_pourashava.id', '=', 'beneficiaries.permanent_district_pourashava_id', 'left')
            ->join('locations AS upazila', 'upazila.id', '=', 'beneficiaries.permanent_upazila_id', 'left');
        $query->whereNull('beneficiaries.deleted_at')->where('beneficiary_change_trackings.change_type_id', 3)
              ->where('beneficiary_change_trackings.status', 2);
        if ($program_id)
            $query = $query->where('beneficiaries.program_id', $program_id);
        if ($from_date)
            $query = $query->whereDate('beneficiary_change_trackings.updated_at', '>=', $from_date);
        if ($to_date)
            $query = $query->whereDate('beneficiary_change_trackings.updated_at', '<=', $to_date);

        $query = $this->applyLocationFilter2($query, $request);

        return $query->select('beneficiary_change_trackings.id',
            'beneficiaries.id as ben_id',
            'beneficiaries.beneficiary_id as beneficiary_id',
            'beneficiaries.application_id',
            'beneficiaries.verification_number',
            'beneficiaries.verification_type',
            'beneficiaries.mobile',
            'beneficiaries.name_en',
            'beneficiaries.name_bn',
            'beneficiaries.father_name_en',
            'beneficiaries.father_name_bn',
            'beneficiaries.mother_name_en',
            'beneficiaries.mother_name_bn',
            'allowance_programs.name_en as program_name_en',
            'allowance_programs.name_bn as program_name_bn',
            'division.name_en as division_name_en',
            'division.name_bn as division_name_bn',
            'district.name_en as district_name_en',
            'district.name_bn as district_name_bn',
            'city_corporation.name_en as city_corporation_name_en',
            'city_corporation.name_bn as city_corporation_name_bn',
            'district_pourashava.name_en as district_pourashava_name_en',
            'district_pourashava.name_bn as district_pourashava_name_bn',
            'upazila.name_en as upazila_name_en',
            'upazila.name_bn as upazila_name_bn',
            'beneficiary_change_trackings.previous_value as previous_value',
            'beneficiary_change_trackings.change_value as change_value',
            'beneficiary_change_trackings.created_by as created_by',
            'beneficiary_change_trackings.approved_by as approved_by',
            'beneficiary_change_trackings.updated_at as updated_at',
        )->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     * @throws \Throwable
     */
    public function exitSave(Request $request): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            if (!$request->has('beneficiaries')) {
                DB::rollBack();
                throw new \Exception('No beneficiaries was selected for exit!');
            }
            $exitDataList = [];
            $benList = [];
            foreach ($request->input('beneficiaries') as $beneficiary) {
                $exitDataList[] = [
                    'beneficiary_id' => $beneficiary['beneficiary_id'],
                    'exit_reason_id' => $request->input('exit_reason_id'),
                    'exit_reason_detail' => $request->input('exit_reason_detail'),
                    'exit_date' => $request->input('exit_date') ? Carbon::parse($request->input('exit_date')) : now(),
                ];

                $beneficiary = Beneficiary::findOrFail($beneficiary['beneficiary_id']);
                $beneficiaryBeforeUpdate = $beneficiary->replicate();
                $beneficiary->status = 2; // Inactive
                $beneficiary->save();
                Helper::activityLogUpdate($beneficiary, $beneficiaryBeforeUpdate, "Beneficiary", "Beneficiary Exited!");
            }
            BeneficiaryExit::insert($exitDataList);

            DB::commit();
            try{
                $user = auth()->user();
                foreach($benList as $ben){
                    $receivers = $this->getNotifiableAdmins($ben);
                    foreach($receivers as $receiver){
                        if($request->input('exit_reason_id') == 39){
                            \Notification::send($receiver, new BeneficiaryDeath($ben, $user, $receiver));
                        }elseif($request->input('exit_reason_id') == 156){
                            \Notification::send($receiver, new BeneficiaryRemarriage($ben, $user, $receiver));
                        }
                    }
                }
            }catch(\Throwable $throwable){
                throw $throwable;
            }
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    private function getNotifiableAdmins($beneficiary){
        $location = Location::find($beneficiary->permanent_location_id);
        $users = [];
        while($location != null){
            if($office = $location->office){
                $users = [...$users, ...User::where('office_id', $office->id)->get()];
            }else{
                $users = [$users, ...User::whereHas('unions', function($q)use($location){
                    $q->where('union_id', $location->id);
                })->orWhereHas('userWards', function($q)use($location){
                    $q->where('ward_id', $location->id);
                })->get()];
            }
            $location = $location->parent;
        }

        $users = [...$users, ...User::whereHas('office', function($q){
            $q->whereIn('office_type',[4,5]);
        })->get()];
        $users = [...$users, ...User::where('user_type', 1)->get()];
        return $users;
    }

//    public function exitSave(Request $request): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
//    {
//        DB::beginTransaction();
//        try {
//            if (!$request->has('beneficiaries')) {
//                DB::rollBack();
//                throw new \Exception('No beneficiaries was selected for replace!');
//            }
//            $exitDataList = [];
//            foreach ($request->input('beneficiaries') as $beneficiary) {
//                $exitDataList[] = [
//                    'beneficiary_id' => $beneficiary['beneficiary_id'],
//                    'exit_reason_id' => $request->input('exit_reason_id'),
//                    'exit_reason_detail' => $request->input('exit_reason_detail'),
//                    'exit_date' => $request->input('exit_date') ? Carbon::parse($request->input('exit_date')) : now(),
//                ];
//            }
//            BeneficiaryExit::insert($exitDataList);
//            $beneficiary_ids = Arr::pluck($exitDataList, 'beneficiary_id');
//
//            Beneficiary::whereIn('id', $beneficiary_ids)->update(['status' => 2]); // Inactive
//
//            DB::commit();
//            return true;
//        } catch (\Throwable $th) {
//            DB::rollBack();
//            throw $th;
//        }
//    }

    /**
     * @param Request $request
     * @param $forPdf
     * @return mixed
     */
    public function exitList(Request $request, $forPdf = false)
    {
        $program_id = $request->query('program_id');
        $sub_program_id = $request->query('sub_program_id');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'beneficiary_exits.created_at');
        $orderByDirection = $request->query('orderBy', 'asc');
        $fromDate = $request->query('from_date') ? Carbon::parse($request->query('from_date'))->startOfDay() : null;
        $toDate = $request->query('to_date') ? Carbon::parse($request->query('to_date'))->endOfDay() : null;

        $query = DB::table('beneficiary_exits')
            ->join('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_exits.beneficiary_id')
            ->join('allowance_programs as main_allowance_programs', 'main_allowance_programs.id', '=', 'beneficiaries.main_program_id')
            ->join('allowance_programs', 'allowance_programs.id', '=', 'beneficiaries.program_id')
            ->join('locations AS division', 'division.id', '=', 'beneficiaries.permanent_division_id', 'left')
            ->join('locations AS district', 'district.id', '=', 'beneficiaries.permanent_district_id', 'left')
            ->join('locations AS city_corporation', 'city_corporation.id', '=', 'beneficiaries.permanent_city_corp_id', 'left')
            ->join('locations AS district_pourashava', 'district_pourashava.id', '=', 'beneficiaries.permanent_district_pourashava_id', 'left')
            ->join('locations AS upazila', 'upazila.id', '=', 'beneficiaries.permanent_upazila_id', 'left')
            ->join('locations AS pourashava', 'pourashava.id', '=', 'beneficiaries.permanent_pourashava_id', 'left')
            ->join('locations AS thana', 'thana.id', '=', 'beneficiaries.permanent_thana_id', 'left')
            ->join('locations AS union', 'union.id', '=', 'beneficiaries.permanent_union_id', 'left')
            ->join('locations AS ward', 'ward.id', '=', 'beneficiaries.permanent_ward_id', 'left')
            ->join('lookups AS exit_reason', 'exit_reason.id', '=', 'beneficiary_exits.exit_reason_id', 'left');
        if ($program_id)
            $query = $query->where('beneficiaries.main_program_id', $program_id);
        if ($sub_program_id)
            $query = $query->where('beneficiaries.program_id', $sub_program_id);

        if ($fromDate)
            $query = $query->whereDate('beneficiary_exits.exit_date', '>=', $fromDate);

        if ($toDate)
            $query = $query->whereDate('beneficiary_exits.exit_date', '<=', $toDate);


        $query = $this->applyLocationFilter2($query, $request);

        if ($forPdf)
            return $query->select('beneficiary_exits.id',
                'exit_reason.value_en as exit_reason_en',
                'exit_reason.value_bn as exit_reason_bn',
                'beneficiary_exits.exit_reason_detail',
                'beneficiary_exits.exit_date',
                'beneficiaries.id as beneficiary_id',
                'beneficiaries.application_id',
                'beneficiaries.name_en',
                'beneficiaries.name_bn',
                'beneficiaries.father_name_en',
                'beneficiaries.father_name_bn',
                'beneficiaries.mother_name_en',
                'beneficiaries.mother_name_bn',
                'main_allowance_programs.name_en as main_program_name_en',
                'main_allowance_programs.name_bn as main_program_name_bn',
                'allowance_programs.name_en as program_name_en',
                'allowance_programs.name_bn as program_name_bn',
                'division.name_en as division_name_en',
                'division.name_bn as division_name_bn',
                'district.name_en as district_name_en',
                'district.name_bn as district_name_bn',
                'city_corporation.name_en as city_corporation_name_en',
                'city_corporation.name_bn as city_corporation_name_bn',
                'district_pourashava.name_en as district_pourashava_name_en',
                'district_pourashava.name_bn as district_pourashava_name_bn',
                'upazila.name_en as upazila_name_en',
                'upazila.name_bn as upazila_name_bn',
                'pourashava.name_en as pourashava_name_en',
                'pourashava.name_bn as pourashava_name_bn',
                'thana.name_en as thana_en',
                'thana.name_bn as thana_bn',
                'union.name_en as union_en',
                'union.name_bn as union_bn',
                'ward.name_en as ward_en',
                'ward.name_bn as ward_bn')->orderBy("$sortByColumn", "$orderByDirection")->get();
        else
            return $query->select('beneficiary_exits.id',
                'exit_reason.value_en as exit_reason_en',
                'exit_reason.value_bn as exit_reason_bn',
                'beneficiary_exits.exit_reason_detail',
                'beneficiary_exits.exit_date',
                'beneficiaries.id as beneficiary_id',
                'beneficiaries.application_id',
                'beneficiaries.name_en',
                'beneficiaries.name_bn',
                'beneficiaries.father_name_en',
                'beneficiaries.father_name_bn',
                'beneficiaries.mother_name_en',
                'beneficiaries.mother_name_bn',
                'main_allowance_programs.name_en as main_program_name_en',
                'main_allowance_programs.name_bn as main_program_name_bn',
                'allowance_programs.name_en as program_name_en',
                'allowance_programs.name_bn as program_name_bn',
                'division.name_en as division_name_en',
                'division.name_bn as division_name_bn',
                'district.name_en as district_name_en',
                'district.name_bn as district_name_bn',
                'city_corporation.name_en as city_corporation_name_en',
                'city_corporation.name_bn as city_corporation_name_bn',
                'district_pourashava.name_en as district_pourashava_name_en',
                'district_pourashava.name_bn as district_pourashava_name_bn',
                'upazila.name_en as upazila_name_en',
                'upazila.name_bn as upazila_name_bn',
                'pourashava.name_en as pourashava_name_en',
                'pourashava.name_bn as pourashava_name_bn',
                'thana.name_en as thana_en',
                'thana.name_bn as thana_bn',
                'union.name_en as union_en',
                'union.name_bn as union_bn',
                'ward.name_en as ward_en',
                'ward.name_bn as ward_bn')->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     * @throws \Throwable
     */
    public function shiftingSave(Request $request): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            if (!$request->has('beneficiaries')) {
                DB::rollBack();
                throw new \Exception('No beneficiaries was selected for shifting!');
            }
            $shiftingDataList = [];
            foreach ($request->input('beneficiaries') as $ben) {
                $beneficiary = Beneficiary::findOrFail($ben['beneficiary_id']);
                $shiftingDataList[] = [
                    'beneficiary_id' => $beneficiary->id,
                    'from_main_program_id' => $beneficiary->main_program_id,
                    'from_program_id' => $beneficiary->program_id,
                    'to_main_program_id' => $request->input('to_program_id'),
                    'to_program_id' => $request->input('to_sub_program_id') ?? $request->input('to_program_id'),
//                    'shifting_cause_id' => $request->input('shifting_cause_id'),
                    'shifting_cause' => $request->input('shifting_cause'),
                    'activation_date' => $request->input('activation_date') ? Carbon::parse($request->input('activation_date')) : now(),
                ];

                $beneficiaryBeforeUpdate = $beneficiary->replicate();
                $beneficiary->main_program_id = $request->input('to_program_id');
                $beneficiary->program_id = $request->input('to_sub_program_id');
                $beneficiary->save();
                Helper::activityLogUpdate($beneficiary, $beneficiaryBeforeUpdate, "Beneficiary", "Beneficiary Shifted!");
            }
            BeneficiaryShifting::insert($shiftingDataList);

            DB::commit();
            return $shiftingDataList;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $forPdf
     * @return mixed
     */
    public function shiftingList(Request $request, $forPdf = false)
    {
        $from_program_id = $request->query('from_program_id');
        $from_sub_program_id = $request->query('from_sub_program_id');
        $to_program_id = $request->query('to_program_id');
        $to_sub_program_id = $request->query('to_sub_program_id');

        $from_date  = $request->query('from_date');
        $to_date    = $request->query('to_date');
        if ($from_date > $to_date){
            $temp = $from_date;
            $from_date = $to_date;
            $to_date = $temp;
        }

        $beneficiary_id = $request->query('beneficiary_id');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'beneficiary_shiftings.created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = DB::table('beneficiary_shiftings')
            ->join('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_shiftings.beneficiary_id')
            ->join('allowance_programs AS from_main_program', 'from_main_program.id', '=', 'beneficiary_shiftings.from_main_program_id')
            ->join('allowance_programs AS to_main_program', 'to_main_program.id', '=', 'beneficiary_shiftings.to_main_program_id')
            ->join('allowance_programs AS from_program', 'from_program.id', '=', 'beneficiary_shiftings.from_program_id')
            ->join('allowance_programs AS to_program', 'to_program.id', '=', 'beneficiary_shiftings.to_program_id')
            ->join('locations AS division', 'division.id', '=', 'beneficiaries.permanent_division_id', 'left')
            ->join('locations AS district', 'district.id', '=', 'beneficiaries.permanent_district_id', 'left')
            ->join('locations AS city_corporation', 'city_corporation.id', '=', 'beneficiaries.permanent_city_corp_id', 'left')
            ->join('locations AS district_pourashava', 'district_pourashava.id', '=', 'beneficiaries.permanent_district_pourashava_id', 'left')
            ->join('locations AS upazila', 'upazila.id', '=', 'beneficiaries.permanent_upazila_id', 'left')
            ->join('locations AS pourashava', 'pourashava.id', '=', 'beneficiaries.permanent_pourashava_id', 'left')
            ->join('locations AS thana', 'thana.id', '=', 'beneficiaries.permanent_thana_id', 'left')
            ->join('locations AS union', 'union.id', '=', 'beneficiaries.permanent_union_id', 'left')
            ->join('locations AS ward', 'ward.id', '=', 'beneficiaries.permanent_ward_id', 'left');
        if (!empty($from_program_id))
            $query = $query->where('beneficiary_shiftings.from_main_program_id', $from_program_id);
        if (!empty($from_sub_program_id))
            $query = $query->where('beneficiary_shiftings.from_program_id', $from_sub_program_id);
        if (!empty($to_program_id))
            $query = $query->where('beneficiary_shiftings.to_main_program_id', $to_program_id);
        if (!empty($to_sub_program_id))
            $query = $query->where('beneficiary_shiftings.to_program_id', $to_sub_program_id);
        if(!empty($from_date))
            $query = $query->where('beneficiary_shiftings.activation_date', '>=', $from_date);
        if(!empty($to_date))
            $query = $query->where('beneficiary_shiftings.activation_date', '<=', $to_date);
        if(!empty($beneficiary_id)){
            $query->when($beneficiary_id, fn($q) =>
                $q->whereIn('beneficiaries.beneficiary_id', is_array($beneficiary_id)
                    ? $beneficiary_id
                    : explode(',', $beneficiary_id)));
        }

        $query = $this->applyLocationFilter($query, $request);

        if ($forPdf)
            return $query->select('beneficiary_shiftings.id',
                'beneficiary_shiftings.shifting_cause',
                'beneficiary_shiftings.activation_date',
                'beneficiaries.id as beneficiary_id',
                'beneficiaries.beneficiary_id as beneficiary_id_system',
                'beneficiaries.application_id',
                'beneficiaries.name_en',
                'beneficiaries.name_bn',
                'beneficiaries.father_name_en',
                'beneficiaries.father_name_bn',
                'beneficiaries.mother_name_en',
                'beneficiaries.mother_name_bn',
                'from_main_program.name_en as from_main_program_name_en',
                'from_main_program.name_bn as from_main_program_name_bn',
                'to_main_program.name_en as to_main_program_name_en',
                'to_main_program.name_bn as to_main_program_name_bn',
                'from_program.name_en as from_program_name_en',
                'from_program.name_bn as from_program_name_bn',
                'to_program.name_en as to_program_name_en',
                'to_program.name_bn as to_program_name_bn',
                'division.name_en as division_name_en',
                'division.name_bn as division_name_bn',
                'district.name_en as district_name_en',
                'district.name_bn as district_name_bn',
                'city_corporation.name_en as city_corporation_name_en',
                'city_corporation.name_bn as city_corporation_name_bn',
                'district_pourashava.name_en as district_pourashava_name_en',
                'district_pourashava.name_bn as district_pourashava_name_bn',
                'upazila.name_en as upazila_name_en',
                'upazila.name_bn as upazila_name_bn',
                'pourashava.name_en as pourashava_name_en',
                'pourashava.name_bn as pourashava_name_bn',
                'thana.name_en as thana_en',
                'thana.name_bn as thana_bn',
                'union.name_en as union_en',
                'union.name_bn as union_bn',
                'ward.name_en as ward_en',
                'ward.name_bn as ward_bn')->orderBy("$sortByColumn", "$orderByDirection")->get();
        else
            return $query->select('beneficiary_shiftings.id',
                'beneficiary_shiftings.shifting_cause',
                'beneficiary_shiftings.activation_date',
                'beneficiaries.id as beneficiary_id',
                'beneficiaries.beneficiary_id as beneficiary_id_system',
                'beneficiaries.application_id',
                'beneficiaries.name_en',
                'beneficiaries.name_bn',
                'beneficiaries.father_name_en',
                'beneficiaries.father_name_bn',
                'beneficiaries.mother_name_en',
                'beneficiaries.mother_name_bn',
                'from_main_program.name_en as from_main_program_name_en',
                'from_main_program.name_bn as from_main_program_name_bn',
                'to_main_program.name_en as to_main_program_name_en',
                'to_main_program.name_bn as to_main_program_name_bn',
                'from_program.name_en as from_program_name_en',
                'from_program.name_bn as from_program_name_bn',
                'to_program.name_en as to_program_name_en',
                'to_program.name_bn as to_program_name_bn',
                'division.name_en as division_name_en',
                'division.name_bn as division_name_bn',
                'district.name_en as district_name_en',
                'district.name_bn as district_name_bn',
                'city_corporation.name_en as city_corporation_name_en',
                'city_corporation.name_bn as city_corporation_name_bn',
                'district_pourashava.name_en as district_pourashava_name_en',
                'district_pourashava.name_bn as district_pourashava_name_bn',
                'upazila.name_en as upazila_name_en',
                'upazila.name_bn as upazila_name_bn',
                'pourashava.name_en as pourashava_name_en',
                'pourashava.name_bn as pourashava_name_bn',
                'thana.name_en as thana_en',
                'thana.name_bn as thana_bn',
                'union.name_en as union_en',
                'union.name_bn as union_bn',
                'ward.name_en as ward_en',
                'ward.name_bn as ward_bn')->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
     * @throws \Throwable
     */
    public function locationShiftingSave(Request $request): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
//            dump($request->input('beneficiaries'));
            if (!$request->has('beneficiaries')) {
                DB::rollBack();
                throw new \Exception('No beneficiaries was selected for shifting!');
            }
            $shiftingDataList = [];
            foreach ($request->input('beneficiaries') as $beneficiary) {
                $beneficiaryInstance = Beneficiary::findOrFail($beneficiary['beneficiary_id']);
                $beneficiaryBeforeUpdate = $beneficiaryInstance->replicate();
//                dump($beneficiaryInstance);
                $shiftingDataList[] = [
                    'beneficiary_id' => $beneficiary['beneficiary_id'],
                    'from_division_id' => $beneficiaryInstance->permanent_division_id,
                    'from_district_id' => $beneficiaryInstance->permanent_district_id,
                    'from_city_corp_id' => $beneficiaryInstance->permanent_city_corp_id,
                    'from_district_pourashava_id' => $beneficiaryInstance->permanent_district_pourashava_id,
                    'from_upazila_id' => $beneficiaryInstance->permanent_upazila_id,
                    'from_pourashava_id' => $beneficiaryInstance->permanent_pourashava_id,
                    'from_thana_id' => $beneficiaryInstance->permanent_thana_id,
                    'from_union_id' => $beneficiaryInstance->permanent_union_id,
                    'from_ward_id' => $beneficiaryInstance->permanent_ward_id,
                    'from_location_type_id' => $beneficiaryInstance->permanent_location_type_id,
//                    'from_location_id' => $beneficiaryInstance->permanent_location_id,
                    'to_division_id' => $request->input('to_division_id'),
                    'to_district_id' => $request->input('to_district_id'),
                    'to_city_corp_id' => $request->input('to_city_corp_id'),
                    'to_district_pourashava_id' => $request->input('to_district_pourashava_id'),
                    'to_upazila_id' => $request->input('to_upazila_id'),
                    'to_pourashava_id' => $request->input('to_pourashava_id'),
                    'to_thana_id' => $request->input('to_thana_id'),
                    'to_union_id' => $request->input('to_union_id'),
                    'to_ward_id' => $request->input('to_ward_id'),
                    'to_location_type_id' => $request->input('to_location_type_id'),
                    'to_location_id' => $request->input('to_location_id'),
                    'shifting_cause' => $request->input('shifting_cause'),
                    'effective_date' => $request->input('effective_date') ? Carbon::parse($request->input('effective_date')) : now(),
                ];

                $beneficiaryInstance->permanent_division_id = $request->input('to_division_id');
                $beneficiaryInstance->permanent_district_id = $request->input('to_district_id');
                $beneficiaryInstance->permanent_city_corp_id = $request->input('to_city_corp_id');
                $beneficiaryInstance->permanent_district_pourashava_id = $request->input('to_district_pourashava_id');
                $beneficiaryInstance->permanent_upazila_id = $request->input('to_upazila_id');
                $beneficiaryInstance->permanent_pourashava_id = $request->input('to_pourashava_id');
                $beneficiaryInstance->permanent_thana_id = $request->input('to_thana_id');
                $beneficiaryInstance->permanent_union_id = $request->input('to_union_id');
                $beneficiaryInstance->permanent_ward_id = $request->input('to_ward_id');
                $beneficiaryInstance->permanent_location_type_id = $request->input('to_location_type_id');
                $beneficiaryInstance->save();
                Helper::activityLogUpdate($beneficiaryInstance, $beneficiaryBeforeUpdate, "Beneficiary", "Beneficiary Location Shifted!");

            }
            BeneficiaryLocationShifting::insert($shiftingDataList);

            DB::commit();
            return $shiftingDataList;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $forPdf
     * @return mixed
     */
    public function locationShiftingList(Request $request, $forPdf = false)
    {
//        \Log::info("locationShiftingList request: ", $request->toArray());

        $from_division_id = $request->query('from_division_id');
        $from_district_id = $request->query('from_district_id');
        $from_city_corp_id = $request->query('from_city_corp_id');
        $from_district_pourashava_id = $request->query('from_district_pourashava_id');
        $from_upazila_id = $request->query('from_upazila_id');
        $from_pourashava_id = $request->query('from_pourashava_id');
        $from_thana_id = $request->query('from_thana_id');
        $from_union_id = $request->query('from_union_id');
        $from_ward_id = $request->query('from_ward_id');

        $to_division_id = $request->query('to_division_id');
        $to_district_id = $request->query('to_district_id');
        $to_city_corp_id = $request->query('to_city_corp_id');
        $to_district_pourashava_id = $request->query('to_district_pourashava_id');
        $to_upazila_id = $request->query('to_upazila_id');
        $to_pourashava_id = $request->query('to_pourashava_id');
        $to_thana_id = $request->query('to_thana_id');
        $to_union_id = $request->query('to_union_id');
        $to_ward_id = $request->query('to_ward_id');

        $from_date  = $request->query('from_date');
        $to_date    = $request->query('to_date');
        if ($from_date > $to_date){
            $temp = $from_date;
            $from_date = $to_date;
            $to_date = $temp;
        }

        $beneficiary_id = $request->query('beneficiary_id');
        $program_id = $request->query('program_id');
        $nominee_name = $request->query('nominee_name');
        $account_number = $request->query('account_number');
        $verification_number = $request->query('nid');
        $status = $request->query('status');

        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'beneficiary_location_shiftings.created_at');
        $orderByDirection = $request->query('orderBy', 'asc');


        $query = BeneficiaryLocationShifting::query()
            ->join('beneficiaries', 'beneficiaries.id', '=', 'beneficiary_location_shiftings.beneficiary_id')
            ->join('allowance_programs as program', 'program.id', '=', 'beneficiaries.program_id')

            // Join from_* locations
            ->leftJoin('locations as from_division', 'from_division.id', '=', 'beneficiary_location_shiftings.from_division_id')
            ->leftJoin('locations as from_district', 'from_district.id', '=', 'beneficiary_location_shiftings.from_district_id')
            ->leftJoin('locations as from_city_corporation', 'from_city_corporation.id', '=', 'beneficiary_location_shiftings.from_city_corp_id')
            ->leftJoin('locations as from_district_pourashava', 'from_district_pourashava.id', '=', 'beneficiary_location_shiftings.from_district_pourashava_id')
            ->leftJoin('locations as from_upazila', 'from_upazila.id', '=', 'beneficiary_location_shiftings.from_upazila_id')
            ->leftJoin('locations as from_pourashava', 'from_pourashava.id', '=', 'beneficiary_location_shiftings.from_pourashava_id')
            ->leftJoin('locations as from_thana', 'from_thana.id', '=', 'beneficiary_location_shiftings.from_thana_id')
            ->leftJoin('locations as from_union', 'from_union.id', '=', 'beneficiary_location_shiftings.from_union_id')
            ->leftJoin('locations as from_ward', 'from_ward.id', '=', 'beneficiary_location_shiftings.from_ward_id')

            // Join to_* locations
            ->leftJoin('locations as to_division', 'to_division.id', '=', 'beneficiary_location_shiftings.to_division_id')
            ->leftJoin('locations as to_district', 'to_district.id', '=', 'beneficiary_location_shiftings.to_district_id')
            ->leftJoin('locations as to_city_corporation', 'to_city_corporation.id', '=', 'beneficiary_location_shiftings.to_city_corp_id')
            ->leftJoin('locations as to_district_pourashava', 'to_district_pourashava.id', '=', 'beneficiary_location_shiftings.to_district_pourashava_id')
            ->leftJoin('locations as to_upazila', 'to_upazila.id', '=', 'beneficiary_location_shiftings.to_upazila_id')
            ->leftJoin('locations as to_pourashava', 'to_pourashava.id', '=', 'beneficiary_location_shiftings.to_pourashava_id')
            ->leftJoin('locations as to_thana', 'to_thana.id', '=', 'beneficiary_location_shiftings.to_thana_id')
            ->leftJoin('locations as to_union', 'to_union.id', '=', 'beneficiary_location_shiftings.to_union_id')
            ->leftJoin('locations as to_ward', 'to_ward.id', '=', 'beneficiary_location_shiftings.to_ward_id')
            ->select('beneficiary_location_shiftings.*');

        if ($from_division_id)
            $query = $query->where('beneficiary_location_shiftings.from_division_id', $from_division_id);
        if ($from_district_id)
            $query = $query->where('beneficiary_location_shiftings.from_district_id', $from_district_id);
        if ($from_city_corp_id)
            $query = $query->where('beneficiary_location_shiftings.from_city_corp_id', $from_city_corp_id);
        if ($from_district_pourashava_id)
            $query = $query->where('beneficiary_location_shiftings.from_district_pourashava_id', $from_district_pourashava_id);
        if ($from_upazila_id)
            $query = $query->where('beneficiary_location_shiftings.from_upazila_id', $from_upazila_id);
        if ($from_pourashava_id)
            $query = $query->where('beneficiary_location_shiftings.from_pourashava_id', $from_pourashava_id);
        if ($from_thana_id)
            $query = $query->where('beneficiary_location_shiftings.from_thana_id', $from_thana_id);
        if ($from_union_id)
            $query = $query->where('beneficiary_location_shiftings.from_union_id', $from_union_id);
        if ($from_ward_id)
            $query = $query->where('beneficiary_location_shiftings.from_ward_id', $from_ward_id);

        if ($to_division_id)
            $query = $query->where('beneficiary_location_shiftings.to_division_id', $to_division_id);
        if ($to_district_id)
            $query = $query->where('beneficiary_location_shiftings.to_district_id', $to_district_id);
        if ($to_city_corp_id)
            $query = $query->where('beneficiary_location_shiftings.to_city_corp_id', $to_city_corp_id);
        if ($to_district_pourashava_id)
            $query = $query->where('beneficiary_location_shiftings.to_district_pourashava_id', $to_district_pourashava_id);
        if ($to_upazila_id)
            $query = $query->where('beneficiary_location_shiftings.to_upazila_id', $to_upazila_id);
        if ($to_pourashava_id)
            $query = $query->where('beneficiary_location_shiftings.to_pourashava_id', $to_pourashava_id);
        if ($to_thana_id)
            $query = $query->where('beneficiary_location_shiftings.to_thana_id', $to_thana_id);
        if ($to_union_id)
            $query = $query->where('beneficiary_location_shiftings.to_union_id', $to_union_id);
        if ($to_ward_id)
            $query = $query->where('beneficiary_location_shiftings.to_ward_id', $to_ward_id);

        // advance search
        if ($beneficiary_id)
            $query = $query->where('beneficiaries.beneficiary_id', $beneficiary_id);
        if ($program_id)
            $query = $query->where('beneficiaries.program_id', $program_id);
        if ($nominee_name)
            $query = $query->whereRaw('UPPER(beneficiaries.nominee_en) LIKE ?', ['%' . strtoupper($nominee_name) . '%']);
        if ($account_number)
            $query = $query->where('beneficiaries.account_number', $account_number);
        if ($verification_number)
            $query = $query->where('beneficiaries.verification_number', $verification_number);
        if ($status){
            $query = $query->where('beneficiaries.status', $status);
            // $query->whereHas('verifyLogs', function($q)use($request){
            //     return $q->where('financial_year_id', app('CurrentFinancialYear')?->id);
            // });
        }

        if(!empty($from_date))
            $query = $query->where('beneficiary_location_shiftings.effective_date', '>=', $from_date);
        if(!empty($to_date))
            $query = $query->where('beneficiary_location_shiftings.effective_date', '<=', $to_date);

        $query = $this->applyLocationFilter($query, $request);

//        \Log::info(
//            vsprintf(str_replace(['?'], ['\'%s\''], $query->toSql()), $query->getBindings())
//        );

        $selectFields = [
            'beneficiary_location_shiftings.id',
            'beneficiary_location_shiftings.shifting_cause',
            'beneficiary_location_shiftings.effective_date',
            'beneficiaries.id as beneficiary_id',
            'beneficiaries.beneficiary_id as beneficiary_id_system',
            'beneficiaries.application_id',
            'beneficiaries.name_en',
            'beneficiaries.name_bn',
            'beneficiaries.father_name_en',
            'beneficiaries.father_name_bn',
            'beneficiaries.mother_name_en',
            'beneficiaries.mother_name_bn',
            'program.name_en as program_name_en',
            'program.name_bn as program_name_bn',
            'from_division.name_en as from_division_name_en',
            'from_division.name_bn as from_division_name_bn',
            'from_district.name_en as from_district_name_en',
            'from_district.name_bn as from_district_name_bn',
            'from_city_corporation.name_en as from_city_corporation_name_en',
            'from_city_corporation.name_bn as from_city_corporation_name_bn',
            'from_district_pourashava.name_en as from_district_pourashava_name_en',
            'from_district_pourashava.name_bn as from_district_pourashava_name_bn',
            'from_upazila.name_en as from_upazila_name_en',
            'from_upazila.name_bn as from_upazila_name_bn',
            'from_pourashava.name_en as from_pourashava_name_en',
            'from_pourashava.name_bn as from_pourashava_name_bn',
            'from_thana.name_en as from_thana_en',
            'from_thana.name_bn as from_thana_bn',
            'from_union.name_en as from_union_en',
            'from_union.name_bn as from_union_bn',
            'from_ward.name_en as from_ward_en',
            'from_ward.name_bn as from_ward_bn',
            'to_division.name_en as to_division_name_en',
            'to_division.name_bn as to_division_name_bn',
            'to_district.name_en as to_district_name_en',
            'to_district.name_bn as to_district_name_bn',
            'to_city_corporation.name_en as to_city_corporation_name_en',
            'to_city_corporation.name_bn as to_city_corporation_name_bn',
            'to_district_pourashava.name_en as to_district_pourashava_name_en',
            'to_district_pourashava.name_bn as to_district_pourashava_name_bn',
            'to_upazila.name_en as to_upazila_name_en',
            'to_upazila.name_bn as to_upazila_name_bn',
            'to_pourashava.name_en as to_pourashava_name_en',
            'to_pourashava.name_bn as to_pourashava_name_bn',
            'to_thana.name_en as to_thana_en',
            'to_thana.name_bn as to_thana_bn',
            'to_union.name_en as to_union_en',
            'to_union.name_bn as to_union_bn',
            'to_ward.name_en as to_ward_en',
            'to_ward.name_bn as to_ward_bn'
        ];

        if ($forPdf) {
            return $query->select($selectFields)->orderBy($sortByColumn, $orderByDirection)->get();
        } else {
            return $query->select($selectFields)->orderBy($sortByColumn, $orderByDirection)->paginate($perPage);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
     * @throws \Throwable
     */
    public function verify(Request $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::query()->findOrFail($id);
            $currentFinancialYear = $this->currentFinancialYear();
            $beneficiaryVerifyLog = new BeneficiaryVerifyLog();
            $beneficiaryVerifyLog->beneficiary_id = $beneficiary->id;
            $beneficiaryVerifyLog->financial_year_id = $currentFinancialYear->id;
            $beneficiaryVerifyLog->remarks = $request->input('remarks');
            $beneficiaryVerifyLog->verified_at = now();
            $beneficiaryVerifyLog->verified_by_id = \Auth::user()->id;
            $beneficiaryVerifyLog->save();
            // update beneficiary
            $beneficiary->is_verified = true;
            $beneficiary->last_ver_fin_year_id = $currentFinancialYear->id;
            $beneficiary->last_verified_at = now();
            $beneficiary->save();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
    public function rollbackVerification($ids): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $currentFinancialYearId = app('CurrentFinancialYear')?->id??0;
        DB::beginTransaction();
        try {
            $beneficiaries = Beneficiary::whereIn('id', $ids)->get();
            foreach ($beneficiaries as $beneficiary) {
                // Log::info($beneficiary->id);
                BeneficiaryVerifyLog::where([
                    'beneficiary_id' => $beneficiary->id,
                    'financial_year_id' => $currentFinancialYearId
                ])->delete();
                $previouseLog = $beneficiary->verifyLogs()->orderBy('verified_at','desc')->first();
                // update beneficiary
                $beneficiary->is_verified = false;
                $beneficiary->last_ver_fin_year_id = $previouseLog?->financial_year_id;
                $beneficiary->last_verified_at = $previouseLog?->verified_at;
                $beneficiary->save();
            }
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }


    /**
     * @param VerifyAllRequest $request
     * @return true
     * @throws \Throwable
     */
    public function verifyAll(VerifyAllRequest $request)
    {
        DB::beginTransaction();
        try {
            $remarks = $request->input('remarks');
            $beneficiary_ids = $request->has('beneficiary_ids') ? $request->get('beneficiary_ids') : [];
            $currentFinancialYear = $this->currentFinancialYear();
            $currentFinancialYearId = $currentFinancialYear?->id;
            $verified_at = now();
            $verified_by_id = \Auth::user()->id;
            $logData = [];
            foreach ($beneficiary_ids as $beneficiary_id) {
                $logData[] = [
                    'beneficiary_id' => $beneficiary_id,
                    'financial_year_id' => $currentFinancialYearId,
                    'remarks' => $remarks,
                    'verified_at' => $verified_at,
                    'verified_by_id' => $verified_by_id,
                ];

                $verifyData = [
                    'is_verified' => true,
                    'last_ver_fin_year_id' => $currentFinancialYearId,
                    'last_verified_at' => $verified_at,
                ];
                Beneficiary::query()->where('id', $beneficiary_id)->update($verifyData);
            }
            if (count($logData) > 0)
                BeneficiaryVerifyLog::upsert($logData, uniqueBy: ['beneficiary_id', 'financial_year_id']);
//                BeneficiaryVerifyLog::insert($logData);

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function verifyAccountChange(Request $request, $id)
    : \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary_change_tracking = BeneficiaryChangeTracking:: query()
                ->where('beneficiary_id', $id)
                ->where('change_type_id', 3)
                ->orderByDesc('id')
                ->firstOrFail();

            $beneficiary_change_tracking_before = $beneficiary_change_tracking;

            $beneficiary_change_tracking = BeneficiaryChangeTracking:: query()
                ->where('beneficiary_id', $id)
                ->where('change_type_id', 3)
                ->orderByDesc('id')
                ->firstOrFail();
            $beneficiary_change_tracking->status = 1;
            $beneficiary_change_tracking->verified_by = auth()->user();

            $beneficiary_change_tracking->save();

            $beneficiary = Beneficiary::query()->findOrFail($id);

            Helper::activityLogUpdate($beneficiary, $beneficiary, "Beneficiary", "Beneficiary ACCOUNT CHANGE Verified!");

            DB::commit();
            return true;

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function approveAccountChange(Request $request, $id) : \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        DB::beginTransaction();
        try {
            $beneficiary_change_tracking = BeneficiaryChangeTracking:: query()
                ->where('beneficiary_id', $id)
                ->where('change_type_id', 3)
                ->orderByDesc('id')
                ->firstOrFail();

            $beneficiary_change_tracking_before = $beneficiary_change_tracking;

            $beneficiary_change_tracking = BeneficiaryChangeTracking:: query()
                ->where('beneficiary_id', $id)
                ->where('change_type_id', 3)
                ->orderByDesc('id')
                ->firstOrFail();
            $beneficiary_change_tracking->status = 2;
            $beneficiary_change_tracking->approved_by = auth()->user();

            $beneficiary_change_tracking->save();

            // BeneficiaryChangeTracking::query()
            //     ->where('beneficiary_id', $id)
            //     ->where('change_type_id', 3)
            //     ->where('id', '!=', $beneficiary_change_tracking->id)
            //     ->delete();

            $beneficiary = Beneficiary::query()->findOrFail($id);
            $beneficiaryBefore = $beneficiary;

            $change_value = json_decode($beneficiary_change_tracking['change_value']);

            $beneficiary->account_type = $change_value->account_type;
            $beneficiary->bank_id = $change_value->bank_id;
            $beneficiary->mfs_id = $change_value->mfs_id;
            $beneficiary->bank_branch_id = $change_value->bank_branch_id;
            $beneficiary->account_name = $change_value->account_name;
            $beneficiary->account_owner = $change_value->account_owner;
            $beneficiary->account_number = $change_value->account_number;
            $beneficiary->financial_year_id = $change_value->financial_year_id;
            $beneficiary->monthly_allowance = $change_value->monthly_allowance;

            $beneficiary->save();

            Helper::activityLogUpdate($beneficiary, $beneficiaryBefore, "Beneficiary", "Beneficiary ACCOUNT CHANGE Approved!");

            DB::commit();
            return true;


//            return response()->json([
//                'success' => true,
//                'message' => "test",
//                'id' => $id,
//                'beneficiary_change_tracking' => $beneficiary_change_tracking,
//                'beneficiary' => $beneficiary,
//                'change_value' => $change_value,
//            ], 200);

        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
     * @throws \Throwable
     */
    public function toWaiting(Request $request, $id): array
    {
        DB::beginTransaction();

        try {
            $beneficiary = Beneficiary::findOrFail($id);

            $canUpdate = PayrollService::checkPayrollCycleBeneficiaryIdFinancialYear($beneficiary->beneficiary_id, $beneficiary->program_id);

            if ($canUpdate) {
                $beforeUpdate = $beneficiary->replicate();
                $beneficiary->status = 3; // Waiting
                $beneficiary->save();

                Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary status changed to Waiting");

                DB::commit();

                return [
                    'success' => true,
                    'message' => 'Beneficiary successfully moved to Waiting status.',
                    'data' => $beneficiary,
                ];
            }
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Cannot move to Waiting: Payroll data already exists for the current financial year and installment.',
            ];

        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'An error occurred while updating the beneficiary.',
                'error' => $th->getMessage(),
            ];
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
     * @throws \Throwable
     */
    public function approve(Request $request, $id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $remainingSpace = AllotmentService::remainingSpace($id);

        if($remainingSpace < 1){
            throw new \Exception("Number of selected beneficiaries exceeds the allotment beneficiaries");
        }


        DB::beginTransaction();
        try {
            $beneficiary = Beneficiary::findOrFail($id);
            $beneficiaryBeforeUpdate = $beneficiary->replicate();
            $beneficiary->status = 1; // Active
            $beneficiary->save();

            $application = Application::where('application_id', $beneficiary->application_id)->first();

            if (!empty($application)){
                $applicationBeforeUpdate = $application->replicate();
                $application->status = 2; // Application Approved
                $application->save();
                Helper::activityLogUpdate($application, $applicationBeforeUpdate, "Application", "Application Approved!");
            }

            Helper::activityLogUpdate($beneficiary, $beneficiaryBeforeUpdate, "Beneficiary", "Beneficiary Approved!");

            DB::commit();
            return $beneficiary;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param ApproveAllRequest $request
     * @return true
     * @throws \Throwable
     */
    public function approveAll(ApproveAllRequest $request)
    {
        DB::beginTransaction();
        try {
            $beneficiary_ids = $request->has('beneficiary_ids') ? $request->get('beneficiary_ids') : [];
            // Check for program_id
            $program = Beneficiary::whereIn('id', $beneficiary_ids)
            ->selectRaw("count(distinct(program_id)) as program_cnt")
            ->first();

            if ($program->program_cnt > 1) {
            throw new \Exception("Multiple allowance programs beneficiaries are not allowed, please select only a single type of allowance program's beneficiaries");
            }

            // Check for ward_id
            $ward = Beneficiary::whereIn('id', $beneficiary_ids)->whereNull('permanent_union_id')
            ->selectRaw("count(distinct(permanent_ward_id)) as ward_cnt")
            ->first();

            if ($ward->ward_cnt > 1) {
            throw new \Exception("Multiple wards beneficiaries are not allowed, please select only a single ward's beneficiaries");
            }

            // Check for union_id
            $union = Beneficiary::whereIn('id', $beneficiary_ids)
            ->selectRaw("count(distinct(permanent_union_id)) as union_cnt")
            ->first();

            if ($union->union_cnt > 1) {
            throw new \Exception("Multiple unions beneficiaries are not allowed, please select only a single union's beneficiaries");
            }


            $remainingSpace = AllotmentService::remainingSpace($beneficiary_ids[0]);

            if($remainingSpace < count($beneficiary_ids)){
                throw new \Exception("Number of selected beneficiaries exceeds the allotment beneficiaries");
            }
            // Log::info($bens);
            foreach ($beneficiary_ids as $beneficiary_id) {
                $beneficiary = Beneficiary::findOrFail($beneficiary_id);
                $beforeUpdate = $beneficiary->replicate();
                $beneficiary->status = 1; // Active
                $beneficiary->save();
                Helper::activityLogUpdate($beneficiary, $beforeUpdate, "Beneficiary", "Beneficiary Approved!");

            }

            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function getStatusWiseTotalBeneficiaries(): \Illuminate\Support\Collection
    {
        return DB::table('beneficiaries')
            ->select(DB::raw('count(*) as beneficiary_count, status'))
            ->groupBy('status')
            ->get();
    }

    /**
     * @return int
     */
    public function getTotalReplacedBeneficiaries(): int
    {
        return DB::table('beneficiary_replaces')->count();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getLocationWiseBeneficiaries(Request $request): \Illuminate\Support\Collection
    {
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
//        $program_id = $request->query('program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');
        $query = DB::table('locations as l')
            ->join('beneficiaries as b', 'b.permanent_division_id', '=', 'l.id', 'left')
            ->select(DB::raw('l.name_en, l.name_bn, count(b.id) as value'));
        $query = $query->where("l.type", '=', "division");
        $query = $query->where(function ($q) {
            return $q->where('b.status', BeneficiaryStatus::ACTIVE)
                ->orWhereNull('b.status');
        });
//        if ($program_id) {
//            $query = $query->where(function ($q) use ($program_id) {
//                return $q->where('b.program_id', $program_id)
//                    ->orWhereNull('b.program_id');
//            });
//        }
        if (count($program_ids) > 0) {
            $query = $query->whereIn('b.program_id', $program_ids);
        }
        if ($from_date) {
            $query = $query->where(function ($q) use ($from_date) {
                return $q->whereDate('b.approve_date', '>=', Carbon::parse($from_date)->format('Y-m-d'))
                    ->orWhereNull('b.approve_date');
            });
        }
        if ($to_date) {
            $query = $query->where(function ($q) use ($to_date) {
                return $q->whereDate('b.approve_date', '<=', Carbon::parse($to_date)->format('Y-m-d'))
                    ->orWhereNull('b.approve_date');
            });
        }
        return $query->groupBy('l.name_en', 'l.name_bn')->orderBy('l.name_en')
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getGenderWiseBeneficiaries(Request $request): \Illuminate\Support\Collection
    {
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
//        $program_id = $request->query('program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');
        $query = DB::table('beneficiaries')
            ->join('lookups', 'beneficiaries.gender_id', '=', 'lookups.id', 'left')
            ->select(DB::raw('lookups.value_en AS name_en, lookups.value_bn AS name_bn, count(*) as value'));
        $query = $query->where('status', BeneficiaryStatus::ACTIVE);
//        if ($program_id)
//            $query = $query->where('program_id', $program_id);
        if (count($program_ids) > 0)
            $query = $query->whereIn('program_id', $program_ids);
        if ($from_date)
            $query = $query->whereDate('approve_date', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        if ($to_date)
            $query = $query->whereDate('approve_date', '<=', Carbon::parse($to_date)->format('Y-m-d'));


        return $query->groupBy('lookups.value_en', 'lookups.value_bn')
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getYearWiseBeneficiaries(Request $request): \Illuminate\Support\Collection
    {
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
//        $program_id = $request->query('program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');

        $query = DB::table('beneficiaries')
            ->select(DB::raw('year(approve_date) as year, status, count(status) as value'));
//        $query = $query->where('status', BeneficiaryStatus::ACTIVE);
//        if ($program_id)
//            $query = $query->where('program_id', $program_id);
        if (count($program_ids) > 0)
            $query = $query->whereIn('program_id', $program_ids);
        if ($from_date)
            $query = $query->whereDate('approve_date', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        if ($to_date)
            $query = $query->whereDate('approve_date', '<=', Carbon::parse($to_date)->format('Y-m-d'));

        return $query->groupByRaw('year(approve_date), status')
//            ->orderByRaw('year(approve_date), status')
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getProgramWiseBeneficiaries(Request $request): \Illuminate\Support\Collection
    {
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
        $program_id = $request->query('program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');
        $query = DB::table('beneficiaries')
            ->select(DB::raw('YEAR(approve_date) as year, COUNT(*) beneficiaries'));
        $query = $query->where('status', BeneficiaryStatus::ACTIVE);
//        if ($program_id)
//            $query = $query->where('program_id', $program_id);
        if (count($program_ids) > 0)
            $query = $query->whereIn('program_id', $program_ids);
        if ($from_date)
            $query = $query->whereDate('approve_date', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        if ($to_date)
            $query = $query->whereDate('approve_date', '<=', Carbon::parse($to_date)->format('Y-m-d'));

        return $query->groupByRaw('YEAR(beneficiaries.approve_date)')
            ->orderByRaw('YEAR(beneficiaries.approve_date)')
//            ->limit(7)
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getAgeWiseBeneficiaries(Request $request): \Illuminate\Support\Collection
    {
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
//        $program_id = $request->query('program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');
        $query = DB::table('beneficiaries')
            ->select(DB::raw("SUM(IF(age < 20, 1, 0)) as 'Under 20',
                                    SUM(IF(age BETWEEN 20 and 29, 1, 0)) as '20 - 29',
                                    SUM(IF(age BETWEEN 30 and 39, 1, 0)) as '30 - 39',
                                    SUM(IF(age BETWEEN 40 and 49, 1, 0)) as '40 - 49',
                                    SUM(IF(age BETWEEN 50 and 59, 1, 0)) as '50 - 59',
                                    SUM(IF(age BETWEEN 60 and 69, 1, 0)) as '60 - 69',
                                    SUM(IF(age BETWEEN 70 and 79, 1, 0)) as '70 - 79',
                                    SUM(IF(age BETWEEN 80 and 89, 1, 0)) as '80 - 89',
                                    SUM(IF(age BETWEEN 90 and 99, 1, 0)) as '90 - 99',
                                    SUM(IF(age > 89, 1, 0)) as 'Above 99'"));
        $query = $query->where('status', BeneficiaryStatus::ACTIVE);
//        if ($program_id)
//            $query = $query->where('program_id', $program_id);
        if (count($program_ids) > 0) {
            $query = $query->whereIn('program_id', $program_ids);
        }
        if ($from_date)
            $query = $query->whereDate('approve_date', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        if ($to_date)
            $query = $query->whereDate('approve_date', '<=', Carbon::parse($to_date)->format('Y-m-d'));

        return $query->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Support\Collection
     */
    public function getYearWiseProgramShifting(Request $request): \Illuminate\Support\Collection
    {
        $from_program_ids = $request->has('from_program_ids') ? $request->get('from_program_ids') : [];
        $to_program_ids = $request->has('to_program_ids') ? $request->get('to_program_ids') : [];
//        $from_program_id = $request->query('from_program_id');
//        $to_program_id = $request->query('to_program_id');
        $from_date = $request->query('from_date');
        $to_date = $request->query('to_date');
        $query = DB::table('beneficiary_shiftings')
            ->select(DB::raw('YEAR(activation_date) AS year,	COUNT(*) AS beneficiaries'));
//        if ($from_program_id)
//            $query = $query->where('from_program_id', $from_program_id);
//        if ($to_program_id)
//            $query = $query->where('to_program_id', $to_program_id);
        if (count($from_program_ids) > 0)
            $query = $query->whereIn('from_program_id', $from_program_ids);
        if (count($to_program_ids) > 0)
            $query = $query->whereIn('to_program_id', $to_program_ids);
        if ($from_date)
            $query = $query->whereDate('activation_date', '>=', Carbon::parse($from_date)->format('Y-m-d'));
        if ($to_date)
            $query = $query->whereDate('activation_date', '<=', Carbon::parse($to_date)->format('Y-m-d'));

        return $query->groupByRaw('YEAR(activation_date)')
            ->orderByRaw('YEAR(activation_date)')
//            ->limit(7)
            ->get();
    }


    public function createBeneficiary($application, $status)
    {
//        \Log::info("INSIDE CreateBeneficiary for application: {$application}");
//        \Log::info('Auth user: ', ['user' => Auth::user()]);


        $fincurrentYear = FinancialYear::where('status',1)->first();
        $program_code = $application->program_id;
//        $district_geo_code = Application::permanentDistrict($application->permanent_location_id);
        $district_geo_code = \App\Models\Application::permanentDistrict($application->permanent_location_id);

        $district_geo_code = $district_geo_code->code;
        // $district_geo_code = 02;
        $remaining_digits = 11 - strlen($program_code) - strlen($district_geo_code);
        $incremental_value = DB::table('beneficiaries')->count() + 1;
        $incremental_value_formatted = str_pad($incremental_value, $remaining_digits, '0', STR_PAD_LEFT);
        $beneficiary_id = $program_code . $district_geo_code . $incremental_value_formatted;
        $is_unique = DB::table('beneficiaries')->where('beneficiary_id', $beneficiary_id)->doesntExist();
        while (!$is_unique) {
            $incremental_value++;
            $incremental_value_formatted = str_pad($incremental_value, $remaining_digits, '0', STR_PAD_LEFT);
            $beneficiary_id = $program_code . $district_geo_code . $incremental_value_formatted;
            $is_unique = DB::table('beneficiaries')->where('beneficiary_id', $beneficiary_id)->doesntExist();
        }
//        $currentFinancialYear = getCurrentFinancialYear();
//        $currentFinancialYearId = $currentFinancialYear?->id;
        $currentFinancialYearId = 15;
        $verified_at = now();
//        $verified_by_id = Auth::user()->id;
        $verified_by_id = 10;
        // $application->application_id = $application_id;
        $beneficiary = Beneficiary::firstOrNew(
            [
                "application_table_id" => $application->id
            ],

            [
                "main_program_id" => $application->main_program_id,
                "program_id" => $application->program_id,
                "application_id" => $application->application_id,
                "beneficiary_id" => $beneficiary_id,
                "name_en" => $application->name_en,
                "name_bn" => $application->name_bn,
                "mother_name_en" => $application->mother_name_en,
                "mother_name_bn" => $application->mother_name_bn,
                "father_name_en" => $application->father_name_en,
                "father_name_bn" => $application->father_name_bn,
                "spouse_name_en" => $application->spouse_name_en,
                "spouse_name_bn" => $application->spouse_name_bn,
                "identification_mark" => $application->identification_mark,
                "age" => $application->age,
                "date_of_birth" => $application->date_of_birth,
                "nationality" => $application->nationality,
                "gender_id" => $application->gender_id,
                "education_status" => $application->education_status,
                "profession" => $application->profession,
                "religion" => $application->religion,
                "marital_status" => $application->marital_status,
                "email" => $application->email,
                "verification_type" => $application->verification_type,
                "verification_number" => $application->verification_number,
                "image" => $application->image,
                "signature" => $application->signature,
                "current_division_id" => $application->current_division_id,
                "current_district_id" => $application->current_district_id,
                "current_city_corp_id" => $application->current_city_corp_id,
                "current_district_pourashava_id" => $application->current_district_pourashava_id,
                "current_upazila_id" => $application->current_upazila_id,
                "current_pourashava_id" => $application->current_pourashava_id,
                "current_thana_id" => $application->current_thana_id,
                "current_union_id" => $application->current_union_id,
                "current_ward_id" => $application->current_ward_id,
                "current_post_code" => $application->current_post_code,
                "current_address" => $application->current_address,
                "mobile" => $application->mobile,
                "permanent_division_id" => $application->permanent_division_id,
                "permanent_district_id" => $application->permanent_district_id,
                "permanent_city_corp_id" => $application->permanent_city_corp_id,
                "permanent_district_pourashava_id" => $application->permanent_district_pourashava_id,
                "permanent_upazila_id" => $application->permanent_upazila_id,
                "permanent_pourashava_id" => $application->permanent_pourashava_id,
                "permanent_thana_id" => $application->permanent_thana_id,
                "permanent_union_id" => $application->permanent_union_id,
                "permanent_ward_id" => $application->permanent_ward_id,
                "permanent_post_code" => $application->permanent_post_code,
                "permanent_address" => $application->permanent_address,
                "permanent_mobile" => $application->permanent_mobile,
                "nominee_en" => $application->nominee_en,
                "nominee_bn" => $application->nominee_bn,
                "nominee_verification_number" => $application->nominee_verification_number,
                "nominee_address" => $application->nominee_address,
                "nominee_date_of_birth" => $application->nominee_date_of_birth,
                "nominee_image" =>  $application->nominee_image,
                "nominee_signature" =>$application->nominee_signature,
                "nominee_relation_with_beneficiary" => $application->nominee_relation_with_beneficiary,
                "nominee_nationality" => $application->nominee_nationality,
                "account_name" => $application->account_name,
                "bank_id" => $application->bank_name,
                "mfs_id" => $application->mfs_name,
                "bank_branch_id" => $application->branch_name,
                "account_number" => $application->account_number,
                "account_owner" => $application->account_owner,
                "permanent_location_type_id" => $application->permanent_location_type_id,

                "score" => $application->score,
                "forward_committee_id" => $application->forward_committee_id,
                "remarks" => $application->remark,
//                "monthly_allowance" => $application->allowance_amount,
                "monthly_allowance" => 500,
                "application_date" => $application->created_at,
                "type_id" => $application->type_id,
                "payment_start_date" => $fincurrentYear->start_date,
                "is_verified" => $status == 1? true : false,
                "last_ver_fin_year_id" => $status === 1? $currentFinancialYearId : null,
                "last_verified_at" => $status === 1? $verified_at : null
            ]
        );

        $beneficiary->status = $status;
        $beneficiary->approve_date = $application->approve_date;
        $beneficiary->save();



        if($status === 1){
            $logData = [
                'beneficiary_id' => $beneficiary->id,
                'financial_year_id' => $currentFinancialYearId,
                'remarks' => null,
                'verified_at' => $verified_at,
                'verified_by_id' => $verified_by_id,
            ];

            BeneficiaryVerifyLog::upsert($logData, uniqueBy: ['beneficiary_id', 'financial_year_id']);
            $programName = AllowanceProgram::where('id',$application->program_id)->first('name_en');
            $program = $programName->name_en;





            //  $message = " Dear $application->name_en, "."\n We are thrilled to inform you that you have been selected as a recipient for the ". $program ."\n Sincerely,"."\nDepartment of Social Services";
            $message = "Dear $application->name_en,"."\nWe are thrilled to inform you that you have been selected as a recipient for the $program.\n\nYour Beneficiary ID is $beneficiary_id.\n\nSincerely,"."\nDepartment of Social Services";


            $message = " Dear $application->name_en, "."\n We are thrilled to inform you that you have been selected as a recipient for the ". $program ."\n Sincerely,"."\nDepartment of Social Services";

//            $this->SMSservice->sendSms($application->mobile, $message);
            if($application->email){
//                $this->dispatch(new SendEmail($application->email,$application->name_en, $program));

            }
        }
    }

    public function updateAccountFromExcelRow($beneficiary, array $data)
    {

        \DB::transaction(function()use($beneficiary, $data){
            $beforeUpdate = $beneficiary->replicate();
            $attributes = [
                'account_type',
                'bank_id',
                'mfs_id',
                'bank_branch_id',
                'account_name',
                'account_owner',
                'account_number',
                'financial_year_id',
                'monthly_allowance'
            ];

            if(!empty($data['account_number'])){
                $beneficiary->account_number = $data['account_number'];
            }
            if($data['account_number'] != "delete"){
                $beneficiary->account_type = $data['account_type'];
                $beneficiary->account_owner = $data['account_owner'];
                $beneficiary->account_name = $beneficiary->name_en;
                $beneficiary->mfs_id = $data['mfs_id'];
                $beneficiary->bank_id = $data['bank_id'];
                $beneficiary->bank_branch_id = $data['bank_branch_id'];
            } else {
                $beneficiary->account_number = null;
                $beneficiary->account_type = null;
                $beneficiary->account_owner = null;
                $beneficiary->account_name = null;
                $beneficiary->mfs_id = null;
                $beneficiary->bank_id = null;
                $beneficiary->bank_branch_id = null;
            }
            $beneficiary->save();
            Log::info('ben save');

            $oldValues = [];
            $newValues = [];

            foreach ($attributes as $attribute) {
                $oldValues[$attribute] = $beforeUpdate->$attribute ?? null;
                $newValues[$attribute] = $beneficiary->$attribute ?? null;
            }

            $changeTracking = BeneficiaryChangeTracking::query()
                    ->where('beneficiary_id', $beneficiary->id)
                    ->where('change_type_id', 3) // for account change
                    ->where('status', 0)
                    ->first();
            if($changeTracking){
                Log::info('update');
                $changeTracking->previous_value = json_encode($oldValues);
                $changeTracking->change_value = json_encode($newValues);
                $changeTracking->created_by = auth()->user();
                $changeTracking->status = 2;
                $changeTracking->approved_by = auth()->user();
                $changeTracking->save();
            }else{
                Log::info('insert');
                BeneficiaryChangeTracking::create([
                    'beneficiary_id' => $beneficiary->id,
                    'change_type_id' => 3,
                    'previous_value' => json_encode($oldValues),
                    'change_value' => json_encode($newValues),
                    'status' => 2,
                    'created_by' => auth()->user(),
                    'approved_by' => auth()->user(),
                ]);
            }

        }, 3);

        // BeneficiaryChangeTracking::where('beneficiary_id', $beneficiary->id)
        //     ->where('change_type_id', 3)
        //     ->delete();
    }

    public static function getMonthlyAllowanceAmount(Beneficiary $beneficiary){
        $amount = $beneficiary->monthly_allowance;
        $program = $beneficiary->program;
        if($program->is_age_limit == 1){
            $age = $beneficiary->age;
            if($beneficiary->date_of_birth != null){
                $age = Carbon::parse($beneficiary->date_of_birth)->diffInYears(now());
            }
            $amountData = $program->ages()->where('min_age', '<=', $age)
                    ->where('max_age', '>=', $age)
                    ->where('gender_id', $beneficiary->gender_id)
                    ->first();
            if($amountData){
                $amount = $amountData->amount;
            }
        }
        if($program->is_disable_class == 1 && $beneficiary->type_id){
            $amountData =  $program->classAmounts()
                            ->where('type_id', $beneficiary->type_id)->first();

            if($amountData){
                $amount = $amountData->amount;
            }
        }
        return $amount;
    }

    public function updateLocationFromExcelRow($beneficiary, array $data){

        $beneficiary_before_update = $beneficiary;

        $beneficiary->permanent_division_id         = $data['permanent_division_id'];
        $beneficiary->permanent_district_id         = $data['permanent_district_id'];
        $beneficiary->permanent_location_type_id    = $data['permanent_location_type_id'];
        $beneficiary->permanent_ward_id             = $data['permanent_ward_id'];
        if(!empty($data['permanent_post_code'])){
            $beneficiary->permanent_post_code           = $data['permanent_post_code'];
        }
        if(!empty($data['permanent_address'])){
            $beneficiary->permanent_address             = $data['permanent_address'];
        }

        if($data['permanent_location_type_id'] == 1){
            $beneficiary->permanent_district_pourashava_id  = $data['permanent_district_pourashava_id'];

            $beneficiary->permanent_upazila_id      = null;
            $beneficiary->permanent_union_id        = null;
            $beneficiary->permanent_pourashava_id   = null;
            $beneficiary->permanent_city_corp_id    = null;
            $beneficiary->permanent_thana_id        = null;

        } elseif ($data['permanent_location_type_id'] == 2){
            $beneficiary->permanent_upazila_id      = $data['permanent_upazila_id'];
            if($data['permanent_union_id'] != null ){
                $beneficiary->permanent_union_id        = $data['permanent_union_id'];
                $beneficiary->permanent_pourashava_id   = null;
            } else {
                $beneficiary->permanent_union_id        = null;
                $beneficiary->permanent_pourashava_id   = $data['permanent_pourashava_id'];
            }

            $beneficiary->permanent_city_corp_id    = null;
            $beneficiary->permanent_thana_id        = null;
            $beneficiary->permanent_district_pourashava_id  = null;

        } elseif ($data['permanent_location_type_id'] == 3){
            $beneficiary->permanent_city_corp_id    = $data['permanent_city_corp_id'];
            $beneficiary->permanent_thana_id        = $data['permanent_thana_id'];

            $beneficiary->permanent_upazila_id      = null;
            $beneficiary->permanent_union_id        = null;
            $beneficiary->permanent_pourashava_id   = null;
            $beneficiary->permanent_district_pourashava_id  = null;
        }

        $beneficiary->save();

        $beneficiary_location_shifting = new BeneficiaryLocationShifting;

        $beneficiary_location_shifting->beneficiary_id          = $beneficiary->id;

        $beneficiary_location_shifting->from_division_id        = $beneficiary_before_update->permanent_division_id;
        $beneficiary_location_shifting->from_district_id        = $beneficiary_before_update->permanent_district_id;
        $beneficiary_location_shifting->from_location_type_id   = $beneficiary_before_update->permanent_location_type_id;
        $beneficiary_location_shifting->from_city_corp_id       = $beneficiary_before_update->permanent_city_corp_id;
        $beneficiary_location_shifting->from_district_pourashava_id = $beneficiary_before_update->permanent_district_pourashava_id;
        $beneficiary_location_shifting->from_upazila_id         = $beneficiary_before_update->permanent_upazila_id;
        $beneficiary_location_shifting->from_pourashava_id      = $beneficiary_before_update->permanent_pourashava_id;
        $beneficiary_location_shifting->from_thana_id           = $beneficiary_before_update->permanent_thana_id;
        $beneficiary_location_shifting->from_union_id           = $beneficiary_before_update->permanent_union_id;
        $beneficiary_location_shifting->from_ward_id            = $beneficiary_before_update->permanent_ward_id;
        $beneficiary_location_shifting->from_location_id        = $beneficiary_before_update->permanent_ward_id;

        $beneficiary_location_shifting->to_division_id          = $beneficiary->permanent_division_id;
        $beneficiary_location_shifting->to_district_id          = $beneficiary->permanent_district_id;
        $beneficiary_location_shifting->to_location_type_id     = $beneficiary->permanent_location_type_id;
        $beneficiary_location_shifting->to_city_corp_id         = $beneficiary->permanent_city_corp_id;
        $beneficiary_location_shifting->to_district_pourashava_id = $beneficiary->permanent_district_pourashava_id;
        $beneficiary_location_shifting->to_upazila_id           = $beneficiary->permanent_upazila_id;
        $beneficiary_location_shifting->to_pourashava_id        = $beneficiary->permanent_pourashava_id;
        $beneficiary_location_shifting->to_thana_id             = $beneficiary->permanent_thana_id;
        $beneficiary_location_shifting->to_union_id             = $beneficiary->permanent_union_id;
        $beneficiary_location_shifting->to_ward_id              = $beneficiary->permanent_ward_id;
        $beneficiary_location_shifting->to_location_id          = $beneficiary->permanent_ward_id;

        $beneficiary_location_shifting->effective_date          = now();

        $beneficiary_location_shifting->save();
    }
}

