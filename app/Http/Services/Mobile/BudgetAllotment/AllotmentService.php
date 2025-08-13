<?php

namespace App\Http\Services\Mobile\BudgetAllotment;


use App\Models\Allotment;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Constants\BeneficiaryStatus;
use App\Http\Requests\Admin\Allotment\UpdateAllotmentRequest;

/**
 * Allotment Service
 */
class AllotmentService
{
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
//        $location_type_id = $request->query('location_type_id');
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
            $query = $query->where('division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('district_id', $district_id);
        if ($city_corp_id && $city_corp_id > 0)
            $query = $query->where('city_corp_id', $city_corp_id);
        if ($district_pourashava_id && $district_pourashava_id > 0)
            $query = $query->where('district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && $upazila_id > 0)
            $query = $query->where('upazila_id', $upazila_id);
        if ($pourashava_id && $pourashava_id > 0)
            $query = $query->where('pourashava_id', $pourashava_id);
        if ($thana_id && $thana_id > 0)
            $query = $query->where('thana_id', $thana_id);
        if ($union_id && $union_id > 0)
            $query = $query->where('union_id', $union_id);
        if ($ward_id && $ward_id > 0)
            $query = $query->where('ward_id', $ward_id);

        return $query;
    }

    /**
     * @param Request $request
     * @param bool $getAllRecords
     * @return mixed
     */
    public function list(Request $request, bool $getAllRecords = false): mixed
    {
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $perPage = $request->query('perPage', 10);

        $query = Allotment::query();
        if ($program_id)
            $query = $query->where('program_id', $program_id);

        if ($financial_year_id)
            $query = $query->where('financial_year_id', $financial_year_id);

        $query = $this->applyLocationFilter($query, $request);

        $query = $query->with('program', 'financialYear', 'division', 'district', 'upazila', 'cityCorporation', 'districtPourosova', 'location');
        if ($getAllRecords)
            return $query->orderBy('location_id')->get();
        else
            return $query->paginate($perPage);

    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function get($id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return Allotment::with('program', 'financialYear', 'location')->find($id);
    }

    /**
     * @param UpdateAllotmentRequest $request
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function update(UpdateAllotmentRequest $request, $id): mixed
    {
        DB::beginTransaction();
        try {
            $allotment = Allotment::findOrFail($id);
            $validated = $request->safe()->only(['additional_beneficiaries', 'total_beneficiaries', 'total_amount']);
            $allotment->fill($validated);
            $allotment->updated_at = now();
            $allotment->save();
            DB::commit();
            return $allotment;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $id
     * @return mixed
     * @throws \Throwable
     */
    public function delete($id): mixed
    {
        $allotment = Allotment::findOrFail($id);
        return $allotment->delete();
    }

    public static function remainingSpace($beneficiaryId){
        $financialYear = getCurrentFinancialYear();
        $beneficiary = Beneficiary::find($beneficiaryId);
        $allotment = Allotment::where('financial_year_id',$financialYear->id)
        ->where('program_id', $beneficiary->program_id);

        if($beneficiary->permanent_location_type_id == 1 || $beneficiary->permanent_location_type_id == 3){
            $allotment->where('ward_id', $beneficiary->permanent_ward_id);
        }elseif($beneficiary->permanent_location_type_id == 2){
            if($beneficiary->type_id){
                $allotment->where('type_id', $beneficiary->type_id)
                ->where('upazila_id', $beneficiary->permanent_upazila_id);
            }else{
                $allotment->where('union_id', $beneficiary->permanent_union_id);
            }
        }
        $allotment = $allotment->first();
        if($allotment == null){
            throw new \Exception('No allotment found for this beneficiry');
        }
        $ben = Beneficiary::where('program_id', $allotment->program_id);
        if($allotment->location_type == 1 || $allotment->location_type == 3){
            $ben->where('permanent_ward_id', $allotment->location_id);
        }elseif($allotment->location_type == 2){
            if($allotment->type_id){
                $ben->where('type_id', $allotment->type_id)
                ->where('permanent_upazila_id', $allotment->upazila_id);
            }else{
                $ben->where('permanent_union_id', $allotment->location_id);
            }
        }
        $ben->where('status', BeneficiaryStatus::ACTIVE);
        return $allotment->total_beneficiaries - $ben->count();
    }
    
    public static function remainingSpaceForApplication($application){
        $financialYear = getCurrentFinancialYear();
        $allotment = Allotment::where('financial_year_id',$financialYear->id)
        ->where('program_id', $application->program_id);

        if($application->permanent_location_type_id == 1 || $application->permanent_location_type_id == 3){
            $allotment->where('ward_id', $application->permanent_ward_id);
        }elseif($application->permanent_location_type_id == 2){
            if($application->type_id){
                $allotment->where('type_id', $application->type_id)
                ->where('upazila_id', $application->permanent_upazila_id);
            }else{
                $allotment->where('union_id', $application->permanent_union_id);
            }
        }
        $allotment = $allotment->first();
        if($allotment == null){
            throw new \Exception('No allotment found for this beneficiry');
        }
        $ben = Beneficiary::where('program_id', $allotment->program_id);
        if($allotment->location_type == 1 || $allotment->location_type == 3){
            $ben->where('permanent_ward_id', $allotment->location_id);
        }elseif($allotment->location_type == 2){
            if($allotment->type_id){
                $ben->where('type_id', $allotment->type_id)
                ->where('permanent_upazila_id', $allotment->upazila_id);
            }else{
                $ben->where('permanent_union_id', $allotment->location_id);
            }
        }
        $ben->where('status', BeneficiaryStatus::ACTIVE);
        return $allotment->total_beneficiaries - $ben->count();
    }

}
