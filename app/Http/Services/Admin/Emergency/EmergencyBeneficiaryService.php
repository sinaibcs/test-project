<?php

namespace App\Http\Services\Admin\Emergency;

use App\Http\Requests\Admin\Emergency\EmergencyBeneficiaryRequest;
use App\Http\Requests\Admin\Emergency\UpdateEmergencyBeneficiaryRequest;
use App\Http\Traits\FileUploadTrait;
use App\Models\AllowanceProgram;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyBeneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class EmergencyBeneficiaryService
{
    use FileUploadTrait;

    public function list(Request $request, $forPdf = false)
    {
        $program_id = $request->program_id;
        $division_id = $request->division_id;
        $district_id = $request->district_id;
        $thana_id = $request->thana_id;
        $union_id = $request->union_id;
        $ward_id = $request->ward_id;
        $location_type = $request->location_type;
        $city_corp_id = $request->city_corp_id;
        $district_pourashava_id = $request->district_pourashava_id;
        $pourashava_id = $request->pourashava_id;
        $beneficiary_id = $request->beneficiary_id;
        $nominee_name = $request->nominee_name;
        $account_number = $request->account_number;
        $verification_number = $request->nid;
        $status = $request->status;
        $searchText = $request->searchText;


        $perPage = $request->perPage ?? 10;
        $sortByColumn = $request->sortBy ?? 'created_at';
        $orderByDirection = $request->orderBy ?? 'asc';
        $query = EmergencyBeneficiary::query();
        if ($program_id) {
            $query = $query->where('allotment_id', $program_id);
        }
        // $query = $query->where('isExisting', 0);

        if ($division_id) {
            $query = $query->where('permanent_division_id', $division_id);
        }
        if ($district_id) {
            $query = $query->where('permanent_district_id', $district_id);
        }
        if ($thana_id) {
            $query = $query->where('permanent_upazila_id', $thana_id);
        }
        // if ($location_type) {
        //     $query = $query->where('permanent_location_type', $location_type);
        // }
        if ($union_id) {
            $query = $query->where('permanent_union_id', $union_id);
        }
        if ($ward_id) {
            $query = $query->where('permanent_ward_id', $ward_id);
        }
        if ($city_corp_id) {
            $query = $query->where('permanent_city_corp_id', $city_corp_id);
        }
        if ($district_pourashava_id) {
            $query = $query->where('permanent_district_pourashava_id', $district_pourashava_id);
        }
        if ($pourashava_id) {
            $query = $query->where('permanent_pourashava_id', $pourashava_id);
        }
        if ($beneficiary_id) {
            $query = $query->where('beneficiary_id', $beneficiary_id);
        }
        if ($nominee_name) {
            $query = $query->whereRaw('UPPER(nominee_en) LIKE "%' . strtoupper($nominee_name) . '%"');
        }
        if ($account_number) {
            $query = $query->where('account_number', $account_number);
        }
        if ($verification_number) {
            $query = $query->where('verification_number', $verification_number);
        }
        if ($status) {
            $query = $query->where('status', $status);
        }

        if ($searchText) {
            $query = $query->where(function ($q) use ($searchText) {
                $q->where('beneficiary_id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('mother_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('mother_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('father_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('father_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('spouse_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('spouse_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('nominee_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('nominee_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('account_number', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('verification_number', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('mobile', 'LIKE', '%' . $searchText . '%');
            });
        }
        $query = $this->applyLocationFilter($query, $request);
        if ($forPdf) {
            return $query->with(
                'emergencyAllotment',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard'
            )->orderBy("$sortByColumn", "$orderByDirection")->get();
        } else {
            return $query->with(
                'emergencyAllotment',
                'permanentDivision',
                'permanentDistrict',
                'permanentCityCorporation',
                'permanentDistrictPourashava',
                'permanentUpazila',
                'permanentPourashava',
                'permanentThana',
                'permanentUnion',
                'permanentWard'
            )->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
        }
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

        $division_id = $request->division_id;
        $district_id = $request->district_id;
        $location_type_id = $request->location_type;
        $city_corp_id = $request->city_corp_id;
        $district_pourashava_id = $request->district_pourashava_id;
        $upazila_id = $request->upazila_id;
        $pourashava_id = $request->pourashava_id;
        $thana_id = $request->thana_id;
        $union_id = $request->union_id;
        $ward_id = $request->ward_id;

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
        } else {
            return $query;
        }

        if ($division_id && $division_id > 0) {
            $query = $query->where('permanent_division_id', $division_id);
        }
        if ($district_id && $district_id > 0) {
            $query = $query->where('permanent_district_id', $district_id);
        }
        if ($location_type_id && $location_type_id > 0) {
            $query = $query->where('permanent_location_type', $location_type_id);
        }
        if ($city_corp_id && $city_corp_id > 0) {
            $query = $query->where('permanent_city_corp_id', $city_corp_id);
        }
        if ($district_pourashava_id && $district_pourashava_id > 0) {
            $query = $query->where('permanent_district_pourashava_id', $district_pourashava_id);
        }
        if ($upazila_id && $upazila_id > 0) {
            $query = $query->where('permanent_upazila_id', $upazila_id);
        }
        if ($pourashava_id && $pourashava_id > 0) {
            $query = $query->where('permanent_pourashava_id', $pourashava_id);
        }
        if ($thana_id && $thana_id > 0) {
            $query = $query->where('permanent_thana_id', $thana_id);
        }
        if ($union_id && $union_id > 0) {
            $query = $query->where('permanent_union_id', $union_id);
        }
        if ($ward_id && $ward_id > 0) {
            $query = $query->where('permanent_ward_id', $ward_id);
        }

        return $query;
    }

    public function getExistingBeneficiaries($request): array|\Illuminate\Database\Eloquent\Collection
    {
        // dd($request->all());
        $emergency_beneficiary_ids = EmergencyBeneficiary::pluck('beneficiary_id')->toArray();

        $queryParams = $request->all();
        $query = Beneficiary::query();

        if (!empty($queryParams['division_id'])) {
            $query->where('permanent_division_id', $queryParams['division_id']);
        }


        if (!empty($queryParams['district_id'])) {
            $query->where('permanent_district_id', $queryParams['district_id']);
        }

        // if (!empty($queryParams['location_type'])) {
        //     $query->where('permanent_location_type_id', $queryParams['location_type']);
        // }

        if (!empty($queryParams['thana_id'])) {
            $query->where('permanent_upazila_id', $queryParams['thana_id']);
        }

        if (!empty($queryParams['union_id'])) {
            $query->where('permanent_union_id', $queryParams['union_id']);
        }

        if (!empty($queryParams['city_id'])) {
            $query->where('permanent_city_corp_id', $queryParams['city_id']);
        }

        if (!empty($queryParams['city_thana_id'])) {
            $query->where('permanent_thana_id', $queryParams['city_thana_id']);
        }

        if (!empty($queryParams['district_pouro_id'])) {
            $query->where('permanent_district_pourashava_id', $queryParams['district_pouro_id']);
        }
        if (!empty($queryParams['pouro_id'])) {
            $query->where('permanent_pourashava_id', $queryParams['pouro_id']);
        }
        if (!empty($queryParams['ward_id'])) {
            $query->where('permanent_ward_id', $queryParams['ward_id']);
        }
        if (!empty($queryParams['program_id'])) {
            $query->whereIn('program_id', $queryParams['program_id']);
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }


        $query->whereNotIn('beneficiary_id', $emergency_beneficiary_ids);

        return $query->with(
            'program',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard'
        )->get();
    }

    public function getNewBeneficiaries($request): \Illuminate\Database\Eloquent\Collection|array
    {
        $queryParams = $request->only([
            'division_id',
            'district_id',
            'location_type',
            'thana_id',
            'union_id',
            'city_id',
            'city_thana_id',
            'district_pouro_id',
            'status',
            'program_id',
            'perPage'
        ]);
        $query = EmergencyBeneficiary::query();
        if (!empty($queryParams['division_id'])) {
            $query->where('current_division_id', $queryParams['division_id']);
        }

        if (!empty($queryParams['district_id'])) {
            $query->where('current_district_id', $queryParams['district_id']);
        }

        if (!empty($queryParams['location_type'])) {
            $query->where('current_location_type', $queryParams['location_type']);
        }

        if (!empty($queryParams['thana_id'])) {
            $query->where('current_upazila_id', $queryParams['thana_id']);
        }

        if (!empty($queryParams['union_id'])) {
            $query->where('current_union_id', $queryParams['union_id']);
        }

        if (!empty($queryParams['city_id'])) {
            $query->where('current_city_corp_id', $queryParams['city_id']);
        }

        if (!empty($queryParams['city_thana_id'])) {
            $query->where('current_city_thana_id', $queryParams['city_thana_id']);
        }

        if (!empty($queryParams['district_pouro_id'])) {
            $query->where('current_district_pourashava_id', $queryParams['district_pouro_id']);
        }

        if (!empty($queryParams['status'])) {
            $query->where('status', $queryParams['status']);
        }
        if (!empty($queryParams['program_id'])) {
            $query->whereIn('program_id', $queryParams['program_id']);
        }
        $query->where('isExisting', 0);
        return $query->with(
            'emergencyAllotment',
            'program',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard'
        )->get();
    }

    public function getSelectedBeneficiaries(Request $request)
    {
        $allotment_id = $request->allotment_id;
        $searchText = $request->searchText;
        $perPage = $request->perPage ?? 10;
        $sortByColumn = $request->sortBy ?? 'created_at';
        $orderByDirection = $request->orderBy ?? 'asc';

        $query = EmergencyBeneficiary::query();

        $query = $query->where('isSelected', 1);
        if ($allotment_id) {
            $query = $query->where('allotment_id', $allotment_id);
        }
        if ($searchText) {
            $query = $query->where(function ($q) use ($searchText) {
                $q->where('beneficiary_id', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('mother_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('mother_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('father_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('father_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('spouse_name_en', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('spouse_name_bn', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('account_number', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('verification_number', 'LIKE', '%' . $searchText . '%')
                    ->orWhere('mobile', 'LIKE', '%' . $searchText . '%');
            });
        }
        $query = $this->applyLocationFilter($query, $request);
        return $query->with(
            'emergencyAllotment',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard'
        )->orderBy("$sortByColumn", "$orderByDirection")->paginate($perPage);
    }
    public function store($request): EmergencyBeneficiary
    {
        $program_id = $this->getAllotmentWiseProgramId($request->emergency_allotment_id);
        $allotment = EmergencyAllotment::where('id', $request->emergency_allotment_id)->first();
        $allowance_amount = $this->getAllotmentWiseMonthlyAllowance($request->emergency_allotment_id);
        try {
            $beneficiary = new EmergencyBeneficiary();
            $beneficiary->allotment_id = $request->emergency_allotment_id;
            $beneficiary->payment_start_date = $allotment->starting_period;
            $beneficiary->last_payment_date = $allotment->closing_period;
            $beneficiary->verification_type = $request->verification_type;
            $beneficiary->verification_number = $request->verification_number;
            $beneficiary->age = $request->age;
            $beneficiary->date_of_birth = $request->date_of_birth;
            $beneficiary->name_en = $request->name_en;
            $beneficiary->name_bn = $request->name_bn;
            $beneficiary->mother_name_en = $request->mother_name_en;
            $beneficiary->mother_name_bn = $request->mother_name_bn;
            $beneficiary->father_name_en = $request->father_name_en;
            $beneficiary->father_name_bn = $request->father_name_bn;
            $beneficiary->spouse_name_en = $request->spouse_name_en;
            $beneficiary->spouse_name_bn = $request->spouse_name_bn;
            $beneficiary->identification_mark = $request->identification_mark;
            $beneficiary->gender_id = $request->gender_id;
            $beneficiary->education_status = $request->education_status;
            $beneficiary->profession = $request->profession;
            $beneficiary->religion = $request->religion;
            $beneficiary->nationality = $request->nationality;
            $beneficiary->account_type = $request->account_type;
            $beneficiary->bank_id = $request->bank_id;
            $beneficiary->mfs_id = $request->mfs_id;
            $beneficiary->bank_branch_id  = $request->bank_branch_id;
            $beneficiary->branch_name = $request->branch_name;

            if ($request->has('ward_id_city') && $request->ward_id_city != null) {
                $beneficiary->current_location_id = $request->ward_id_city;
            }
            if ($request->has('ward_id_dist') && $request->ward_id_dist != null) {
                $beneficiary->current_location_id = $request->ward_id_dist;
            }
            if ($request->has('ward_id_union') && $request->ward_id_union != null) {
                $beneficiary->current_location_id = $request->ward_id_union;
            }
            if ($request->has('ward_id_pouro') && $request->ward_id_pouro != null) {
                $beneficiary->current_location_id = $request->ward_id_pouro;
            }
            $beneficiary->current_post_code = $request->post_code;
            $beneficiary->current_address = $request->address;
            $beneficiary->mobile = $request->mobile;
            if ($request->has('permanent_ward_id_city') && $request->permanent_ward_id_city !== null) {
                $beneficiary->permanent_location_id = $request->permanent_ward_id_city;
            }
            if ($request->has('permanent_ward_id_dist') && ($request->permanent_ward_id_dist !== null)) {
                $beneficiary->permanent_location_id = $request->permanent_ward_id_dist;
            }
            if ($request->has('permanent_ward_id_union') && ($request->permanent_ward_id_union !== null)) {
                $beneficiary->permanent_location_id = $request->permanent_ward_id_union;
            }
            if ($request->has('permanent_ward_id_pouro') && ($request->permanent_ward_id_pouro !== null)) {
                $beneficiary->permanent_location_id = $request->permanent_ward_id_pouro;
            }
            $beneficiary->current_division_id = $request->division_id;
            $beneficiary->current_district_id = $request->district_id;
            $beneficiary->current_location_type = $request->location_type;

            //Dist pouro
            if ($request->location_type == 1) {
                $beneficiary->current_district_pourashava_id = $request->district_pouro_id;
                $beneficiary->current_ward_id = $request->ward_id_dist;
            }

            //City corporation
            if ($request->location_type == 3) {
                $beneficiary->current_city_corp_id = $request->city_id;
                $beneficiary->current_thana_id = $request->city_thana_id;
                $beneficiary->current_ward_id = $request->ward_id_city;
            }

            //Upazila
            if ($request->location_type == 2) {
                $beneficiary->current_upazila_id = $request->thana_id;
                //union
                if ($request->sub_location_type == 2) {
                    $beneficiary->current_union_id = $request->union_id;
                    $beneficiary->current_ward_id = $request->ward_id_union;
                } else {
                    //pouro
                    $beneficiary->current_pourashava_id = $request->pouro_id;
                    $beneficiary->current_ward_id = $request->ward_id_pouro;
                }
            }

            $beneficiary->permanent_division_id = $request->permanent_division_id;
            $beneficiary->permanent_district_id = $request->permanent_district_id;
            $beneficiary->permanent_location_type = $request->permanent_location_type;

            //Dist pouro
            if ($request->permanent_location_type == 1) {
                $beneficiary->permanent_district_pourashava_id = $request->permanent_district_pouro_id;
                $beneficiary->permanent_ward_id = $request->permanent_ward_id_dist;
            }


            //City corporation
            if ($request->permanent_location_type == 3) {
                $beneficiary->permanent_city_corp_id = $request->permanent_city_id;
                $beneficiary->permanent_thana_id = $request->permanent_city_thana_id;
                $beneficiary->permanent_ward_id = $request->permanent_ward_id_city;
            }

            //Upazila
            if ($request->permanent_location_type == 2) {
                $beneficiary->permanent_upazila_id = $request->permanent_thana_id;
                //union
                if ($request->permanent_sub_location_type == 2) {
                    $beneficiary->permanent_union_id = $request->permanent_union_id;
                    $beneficiary->permanent_ward_id = $request->permanent_ward_id_union;
                } else {
                    //pouro
                    $beneficiary->permanent_pourashava_id = $request->permanent_pouro_id;
                    $beneficiary->permanent_ward_id = $request->permanent_ward_id_pouro;
                }
            }
            $beneficiary->permanent_post_code = $request->permanent_post_code;
            $beneficiary->permanent_address = $request->permanent_address;
            $beneficiary->permanent_mobile = $request->permanent_mobile;
            $beneficiary->nominee_en = $request->nominee_en;
            $beneficiary->nominee_bn = $request->nominee_bn;
            $beneficiary->nominee_verification_number = $request->nominee_verification_number;
            $beneficiary->nominee_address = $request->nominee_address;
            $beneficiary->nominee_date_of_birth = $request->nominee_date_of_birth;
            $beneficiary->nominee_relation_with_beneficiary = $request->nominee_relation_with_beneficiary;
            $beneficiary->nominee_nationality = $request->nominee_nationality;
            $beneficiary->account_name = $request->account_name;
            $beneficiary->account_number = $request->account_number;
            $beneficiary->account_owner = $request->account_owner;
            $beneficiary->marital_status = $request->marital_status;
            $beneficiary->email = $request->email;
            $beneficiary->status = 1;
            $beneficiary->isSelected = 1;
            $beneficiary->monthly_allowance = $allowance_amount;
            if ($request->hasFile('image')) {
                $imagePath = $request->file('image')->store('public/emergency/beneficiary');
                $beneficiary->image = $imagePath;
            }
            // Check if signature file is present and store it
            if ($request->hasFile('signature')) {
                $signaturePath = $request->file('signature')->store('public/emergency/beneficiary');
                $beneficiary->signature = $signaturePath;
            }

            // Check if nominee image file is present and store it
            if ($request->hasFile('nominee_image')) {
                $nominee_imagePath = $request->file('nominee_image')->store('public/emergency/beneficiary');
                $beneficiary->nominee_image = $nominee_imagePath;
            }

            // Check if nominee signature file is present and store it
            if ($request->hasFile('nominee_signature')) {
                $nominee_signaturePath = $request->file('nominee_signature')->store('public/emergency/beneficiary');
                $beneficiary->nominee_signature = $nominee_signaturePath;
            }
            $program_code = $program_id;
            $district_geo_code = EmergencyBeneficiary::permanentDistrictGeoCode($beneficiary->permanent_location_id);
            $district_geo_code = $district_geo_code->code;
            // $district_geo_code = 02;
            $remaining_digits = 11 - strlen($program_code) - strlen($district_geo_code);
            $incremental_value = DB::table('emergency_beneficiaries')->count() + 1;
            $incremental_value_formatted = str_pad($incremental_value, $remaining_digits, '0', STR_PAD_LEFT);
            $beneficiary_id = $program_code . $district_geo_code . $incremental_value_formatted;
            $is_unique = DB::table('emergency_beneficiaries')->where('beneficiary_id', $beneficiary_id)->doesntExist();
            while (!$is_unique) {
                $incremental_value++;
                $incremental_value_formatted = str_pad($incremental_value, $remaining_digits, '0', STR_PAD_LEFT);
                $beneficiary_id = $program_code . $district_geo_code . $incremental_value_formatted;
                $is_unique = DB::table('emergency_beneficiaries')->where('beneficiary_id', $beneficiary_id)->doesntExist();
            }
            $beneficiary->beneficiary_id = $beneficiary_id;
            $beneficiary->save();
            DB::commit();
            return EmergencyBeneficiary::whereId($beneficiary->id)->first();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    public function edit($id)
    {
        return EmergencyBeneficiary::with(
            'emergencyAllotment',
            'gender',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard',
            'currentDivision',
            'currentDistrict',
            'currentCityCorporation',
            'currentDistrictPourashava',
            'currentUpazila',
            'currentPourashava',
            'currentThana',
            'currentUnion',
            'currentWard',
        )->findOrFail($id);
    }

    public function details($id)
    {
        return EmergencyBeneficiary::with(
            'emergencyAllotment',
            'gender',
            'permanentDivision',
            'permanentDistrict',
            'permanentCityCorporation',
            'permanentDistrictPourashava',
            'permanentUpazila',
            'permanentPourashava',
            'permanentThana',
            'permanentUnion',
            'permanentWard',
            'currentDivision',
            'currentDistrict',
            'currentCityCorporation',
            'currentDistrictPourashava',
            'currentUpazila',
            'currentPourashava',
            'currentThana',
            'currentUnion',
            'currentWard',
        )->findOrFail($id);
    }

    public function storeMultipleData(Request $request)
    {
        $data = json_decode($request->getContent(), true);
        $ex_count = EmergencyBeneficiary::where('allotment_id', $data['allotment_id'])->where('isExisting', 1)->count();
        $allotment = EmergencyAllotment::where('id', $data['allotment_id'])->first();
        // $beneficiaryIds = array_column($data['data'], 'beneficiary_id');
        // Fetch existing beneficiaries from the database
        $existingBeneficiaries = EmergencyBeneficiary::whereIn('beneficiary_id', $data['data'])->get()->keyBy('beneficiary_id');

        $originalBeneficiaries = Beneficiary::whereIn('beneficiary_id', $data['data'])->get();
        // dd($originalBeneficiaries);
        // Saving all data
        if (count($data['data']) <= $allotment->no_of_existing_benificiariy - $ex_count ) {
            foreach ($originalBeneficiaries as $item) {
                // Check if the beneficiary already exists
                if (!$existingBeneficiaries->has($item->beneficiary_id)) {
                    $beneficiary = new EmergencyBeneficiary();
                    $beneficiary->allotment_id = $data['allotment_id'];
                    $beneficiary->program_id = $item->program_id ?? null;
                    $beneficiary->beneficiary_id = $item->beneficiary_id;
                    $beneficiary->verification_type = $item->verification_type;
                    $beneficiary->verification_number = $item->verification_number;
                    $beneficiary->age = $item->age;
                    $beneficiary->date_of_birth = $item->date_of_birth;
                    $beneficiary->name_en = $item->name_en;
                    $beneficiary->name_bn = $item->name_bn;
                    $beneficiary->mother_name_en = $item->mother_name_en;
                    $beneficiary->mother_name_bn = $item->mother_name_bn;
                    $beneficiary->father_name_en = $item->father_name_en;
                    $beneficiary->father_name_bn = $item->father_name_bn;
                    $beneficiary->spouse_name_en = $item->spouse_name_en;
                    $beneficiary->spouse_name_bn = $item->spouse_name_bn;
                    $beneficiary->identification_mark = $item->identification_mark;
                    $beneficiary->gender_id = $item->gender_id;
                    $beneficiary->education_status = $item->education_status;
                    $beneficiary->profession = $item->profession;
                    $beneficiary->religion = $item->religion;
                    $beneficiary->nationality = $item->nationality;
                    $beneficiary->account_type = $item->account_type;
                    $beneficiary->bank_id = $item->bank_id;
                    $beneficiary->mfs_id = $item->mfs_id;
                    $beneficiary->bank_branch_id = $item->bank_branch_id;
                    $beneficiary->permanent_post_code = $item->permanent_post_code;
                    $beneficiary->permanent_address = $item->permanent_address;
                    $beneficiary->current_post_code = $item->current_post_code;
                    $beneficiary->current_address = $item->current_address;
                    $beneficiary->permanent_mobile = $item->permanent_mobile;
                    $beneficiary->mobile = $item->mobile;
                    $beneficiary->nominee_en = $item->nominee_en;
                    $beneficiary->nominee_bn = $item->nominee_bn;
                    $beneficiary->nominee_verification_number = $item->nominee_verification_number;
                    $beneficiary->nominee_address = $item->nominee_address;
                    $beneficiary->nominee_date_of_birth = $item->nominee_date_of_birth;
                    $beneficiary->nominee_relation_with_beneficiary = $item->nominee_relation_with_beneficiary;
                    $beneficiary->nominee_nationality = $item->nominee_nationality;
                    $beneficiary->account_name = $item->account_name;
                    $beneficiary->account_number = $item->account_number;
                    $beneficiary->account_owner = $item->account_owner;
                    $beneficiary->marital_status = $item->marital_status;
                    $beneficiary->email = $item->email;
                    $beneficiary->isSelected = 1;
                    $beneficiary->isExisting = 1;
                    $beneficiary->status = $item->status;
                    $beneficiary->current_division_id = $item->current_division_id;
                    $beneficiary->current_district_id = $item->current_district_id;
                    $beneficiary->current_location_type = $item->current_location_type_id;
                    $beneficiary->current_district_pourashava_id = $item->current_district_pourashava_id;
                    $beneficiary->current_ward_id = $item->current_ward_id;
                    $beneficiary->current_city_corp_id = $item->current_city_corp_id;
                    $beneficiary->current_thana_id = $item->current_thana_id;
                    $beneficiary->current_ward_id = $item->current_ward_id;
                    $beneficiary->current_upazila_id = $item->current_upazila_id;
                    $beneficiary->current_union_id = $item->current_union_id;
                    $beneficiary->current_ward_id = $item->current_ward_id;
                    $beneficiary->current_pourashava_id = $item->current_pourashava_id;
                    $beneficiary->current_ward_id = $item->current_ward_id;
                
                    // Permanent Location Type
                    $beneficiary->permanent_division_id = $item->permanent_division_id;
                    $beneficiary->permanent_district_id = $item->permanent_district_id;
                    $beneficiary->permanent_location_type = $item->permanent_location_type_id;
                    
                    $beneficiary->permanent_district_pourashava_id = $item->permanent_district_pourashava_id;
                    $beneficiary->permanent_ward_id = $item->permanent_ward_id;
                    $beneficiary->permanent_city_corp_id = $item->permanent_city_corp_id;
                    $beneficiary->permanent_thana_id = $item->permanent_thana_id;
                    $beneficiary->permanent_ward_id = $item->permanent_ward_id;
                    $beneficiary->permanent_upazila_id = $item->permanent_upazila_id;
                    $beneficiary->permanent_union_id = $item->permanent_union_id;
                    $beneficiary->permanent_ward_id = $item->permanent_ward_id;
                    $beneficiary->permanent_pourashava_id = $item->permanent_pourashava_id;
                    $beneficiary->permanent_ward_id = $item->permanent_ward_id;


                    $beneficiary->payment_start_date = $allotment->starting_period;
                    $beneficiary->last_payment_date = $allotment->closing_period;
                
                    // Images and Signatures
                    $beneficiary->image = $item->image;
                    $beneficiary->signature = $item->signature;
                    $beneficiary->nominee_image = $item->nominee_image;
                    $beneficiary->nominee_signature = $item->nominee_signature;
                
                    // Monthly Allowance
                    $beneficiary->monthly_allowance = $this->getAllotmentWiseMonthlyAllowance($data['allotment_id']);
                    
                    // Save beneficiary
                    $beneficiary->save();
                } else {
                    $beneficiary = $existingBeneficiaries->get($item->beneficiary_id);
                    $beneficiary->isSelected = true;
                    $beneficiary->update();
                }
                
            }
            return $beneficiary;
        } else {
            return false;
        }
    }

    private function getAllotmentWiseMonthlyAllowance($id)
    {
        return DB::table('emergency_allotments')
            ->where('id', $id)
            ->value('amount_per_person');
    }

    public function update(UpdateEmergencyBeneficiaryRequest $request, $id)
    {

        $beneficiary = EmergencyBeneficiary::findOrFail($id);
        try {
            $beneficiary->fill($request->only([
                'nominee_en',
                'nominee_bn',
                'nominee_verification_number',
                'nominee_address',
                'nominee_relation_with_beneficiary',
                'nominee_nationality',
                'nominee_date_of_birth',
                'account_name',
                'account_number',
                'account_owner',
                'account_type',
                'bank_id',
                'mfs_id',
                'bank_branch_id',
                'email'
            ]));
            if ($request->hasFile('nominee_image')) {
                // Delete the old image if it exists
                if ($beneficiary->nominee_image) {
                    Storage::delete($beneficiary->nominee_image);
                }
                // Store the new image and save the path
                $beneficiary->nominee_image = $request->file('nominee_image')->store('public/emergency/beneficiary');
            }

            if ($request->hasFile('nominee_signature')) {
                // Delete the old signature if it exists
                if ($beneficiary->nominee_signature) {
                    Storage::delete($beneficiary->nominee_signature);
                }
                // Store the new signature and save the path
                $beneficiary->nominee_signature = $request->file('nominee_signature')->store('public/emergency/beneficiary');
            }

            $beneficiary->save();
            return $beneficiary;
        } catch (\Throwable $th) {
            throw $th;
        }
    }


    private function getAllotmentWiseProgramId($emergencyAllotmentId)
    {
        $allowanceProgram = DB::table('allowance_program_emergency_allotment')
            ->where('emergency_allotment_id', $emergencyAllotmentId)->first();
        return $allowanceProgram?->allowance_program_id;
    }

    public function base64Image($file): ?string
    {

        $position = strpos($file, ';');
        $sub = substr($file, 0, $position);
        $ext = explode('/', $sub)[1];
        if (isset($ext) && ($ext == "png" || $ext == "jpeg" || $ext == "jpg" || $ext == "pdf")) {
            $newImageName = time() . "." . $ext;
        } else {
            $ext2 = explode('.', $ext)[3];
            if ($ext2 == "document") {
                $ext = "docx";
                $newImageName = time() . "." . $ext;
            } elseif ($ext2 == "sheet") {
                $ext = "xlsx";
                $newImageName = time() . "." . $ext;
            } else {
                $newImageName = "";
            }
            $newImageName = time() . "." . $ext;
        }
        $this->validateFile($file);
        $this->createUploadFolder();
        $uploadedPath = $this->uploadFile($newImageName, $file);
        return $uploadedPath;
    }

    private function uploadFile($newImageName, $file)
    {

        $path = $this->uploadPath . '/' . $this->folderName . '/';
        if (Storage::putFileAs('public/' . $path, $file, $newImageName)) {
            return $path . $newImageName;
        }
    }

    public function destroy($id)
    {
        try {
            $beneficiary = EmergencyBeneficiary::findOrFail($id);
            $beneficiary->delete();
            return $beneficiary;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function beneficiariesInfo($id): array
    {
        $query = EmergencyBeneficiary::where('allotment_id', $id)->get();
        $allotment = EmergencyAllotment::with(['programs'])->where('id', $id)->get();

        // Count of beneficiaries where isExisting is equal to 1
        $selectedBeneficiariesCount = $query->where('isSelected', 1)->count();
        $existingBeneficiariesCount = $query->where('isExisting', 1)->count();
        // Count of beneficiaries where isExisting is not equal to 1
        $newBeneficiaries = $query->where('isExisting', '!=', 1)->count();
        if($allotment->count()  == 0) {
            return [
                'programCount' => 0,
                'totalCount' => $query->count(),
                'selectedBeneficiariesCount' => $selectedBeneficiariesCount,
                'existingBeneficiariesCount' => $existingBeneficiariesCount,
                'newBeneficiariesCount' => $newBeneficiaries,
                'from_existing_beneficiary' => 0,
                'from_new_beneficiary' => 0
            ];
        }
        return [
            'programCount' => count($allotment[0]->programs),
            'totalCount' => $query->count(),
            'selectedBeneficiariesCount' => $selectedBeneficiariesCount,
            'existingBeneficiariesCount' => $existingBeneficiariesCount,
            'newBeneficiariesCount' => $newBeneficiaries,
            'from_existing_beneficiary' => $allotment[0]['no_of_existing_benificiariy'],
            'from_new_beneficiary' => $allotment[0]['no_of_new_benificiariy']
        ];
    }
    public function getCoverageAreaWiseBankAndMfs($request)
    {

        // dd($request->all());
        $divisionId = $request->division_id;
        $districtId = $request->district_id;
        $locationTypeId = $request->location_type_id;
        $upazilaCityDistrictPauroId = $request->upazila_city_district_pauro_id ?? null;
        $subLocationTypeId = $request->sub_location_type_id ?? null;
        $locationId = (int)$request->location_id ?? null;
        $banks = collect();
        $mfs = collect();
        $paymentProcessors = DB::table('payroll_payment_processors as ppp')
            ->leftJoin('banks', 'ppp.bank_id', '=', 'banks.id')
            ->leftJoin('mfs', 'ppp.mfs_id', '=', 'mfs.id')
            ->join('payroll_payment_processor_areas as ppa', 'ppp.id', '=', 'ppa.payment_processor_id')
            ->where('ppa.division_id', $divisionId)
            ->where('ppa.district_id', $districtId)
            ->where('ppa.location_type', $locationTypeId)
            ->where(function ($query) use ($upazilaCityDistrictPauroId) {
                if ($upazilaCityDistrictPauroId !== null) {
                    $query->where('ppa.upazila_id', $upazilaCityDistrictPauroId)
                        ->orWhere('ppa.city_corp_id', $upazilaCityDistrictPauroId)
                        ->orWhere('ppa.district_pourashava_id', $upazilaCityDistrictPauroId);
                }
            })
            // ->when($subLocationTypeId, function ($query, $subLocationTypeId) {
            //     return $query->where('ppa.sub_location_type', $subLocationTypeId);
            // })
            // ->when($locationId, function ($query, $locationId) {
            //     return $query->where('ppa.location_id', $locationId);
            // })
            ->select(
                'ppp.*',
                'banks.id as bank_id',
                'banks.name_en as bank_name_en',
                'banks.name_bn as bank_name_bn',
                'mfs.id as mfs_id',
                'mfs.name_en as mfs_name_en',
                'mfs.name_bn as mfs_name_bn'
            )
            ->get();

        // Collect bank and MFS records
        foreach ($paymentProcessors as $processor) {
            if ($processor->bank_id != null) {
                $banks[] = [
                    'id' => $processor->bank_id,
                    'name_en' => $processor->bank_name_en,
                    'name_bn' => $processor->bank_name_bn
                ];
            } else if ($processor->mfs_id != null) {
                $mfs[] = [
                    'id' => $processor->mfs_id,
                    'name_en' => $processor->mfs_name_en,
                    'name_bn' => $processor->mfs_name_bn
                ];
            }
        }

        $uniqueBanks = $banks->unique('id')->values()->all();
        $uniqueMfs = $mfs->unique('id')->values()->all();

        return [
            'banks' => $uniqueBanks,
            'mfs' => $uniqueMfs
        ];
    }
}
