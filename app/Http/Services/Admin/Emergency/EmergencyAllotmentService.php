<?php

namespace App\Http\Services\Admin\Emergency;

use App\Models\EmergencyAllotment;
use App\Models\FinancialYear;
use Illuminate\Http\Request;


class EmergencyAllotmentService
{

    public function getListData(Request $request)
    {
        $searchText = $request->searchText;
        $perPage = $request->perPage??10;
        $allotment_name = $request->allotment_name;
        $started_period = $request->started_period;
        $closing_period = $request->closing_period;
        $filterArrayName = [];
        $filterArrayAmount = [];
        $filterArrayEx = [];
        $filterArrayNew = [];
        $filterArrayStart = [];
        $filterArrayClosing = [];
        $filterArrayCycle = [];
        if ($searchText) {
            $filterArrayName[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAmount[] = ['amount_per_person', 'LIKE', '%' . $searchText . '%'];
            $filterArrayEx[] = ['no_of_existing_benificiariy', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNew[] = ['no_of_new_benificiariy', 'LIKE', '%' . $searchText . '%'];
            $filterArrayStart[] = ['starting_period', 'LIKE', '%' . $searchText . '%'];
            $filterArrayClosing[] = ['closing_period', 'LIKE', '%' . $searchText . '%'];
            $filterArrayCycle[] = ['payment_cycle', 'LIKE', '%' . $searchText . '%'];
        }
        $query = EmergencyAllotment::query();
        $query->when($searchText, function ($q) use ($searchText, $filterArrayName, $filterArrayAmount, $filterArrayClosing, $filterArrayCycle, $filterArrayEx, $filterArrayStart, $filterArrayNew) {
            $q->where($filterArrayName)
                ->orWhere('name_bn', 'LIKE', '%' . $searchText . '%')
                ->orWhere($filterArrayAmount)
                ->orWhere($filterArrayEx)
                ->orWhere($filterArrayNew)
                ->orWhere($filterArrayStart)
                ->orWhere($filterArrayClosing)
                ->orWhereHas('programs', function($q) use ($searchText){
                    $q->where('name_en', 'LIKE', "%$searchText%")
                    ->orWhere('name_bn', 'LIKE', "%$searchText%");
                })
                ->orWhere($filterArrayCycle);
        });
        if ($allotment_name) {
            $query->where('name_en', $allotment_name);
        }
        if ($started_period && $closing_period) {
            $query->whereBetween('starting_period', [$started_period, $closing_period]);
        }
        $query = $this->applyLocationFilter($query, $request);
        return $query->with('programs', 'division', 'district', 'upazila', 'cityCorporation', 'districtPourosova', 'location')
//            ->orderBy('name_en', 'asc')
            ->latest()
            ->paginate($perPage, ['*'], 'page');
    }

    private function applyLocationFilter($query, $request): mixed
    {
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $assignedLocationId = $user->assign_location?->id;
        $subLocationType = $user->assign_location?->location_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation
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

        if ($division_id && $division_id > 0)
            $query = $query->where('emergency_allotments.division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('emergency_allotments.district_id', $district_id);
        if ($city_corp_id && $city_corp_id > 0)
            $query = $query->where('emergency_allotments.city_corp_id', $city_corp_id);
        if ($district_pourashava_id && $district_pourashava_id > 0)
            $query = $query->where('emergency_allotments.district_pourashava_id', $district_pourashava_id);
        if ($upazila_id && $upazila_id > 0)
            $query = $query->where('emergency_allotments.upazila_id', $upazila_id);
        if ($pourashava_id && $pourashava_id > 0)
            $query = $query->where('emergency_allotments.pourashava_id', $pourashava_id);
        if ($thana_id && $thana_id > 0)
            $query = $query->where('emergency_allotments.thana_id', $thana_id);
        if ($union_id && $union_id > 0)
            $query = $query->where('emergency_allotments.union_id', $union_id);
        if ($ward_id && $ward_id > 0)
            $query = $query->where('emergency_allotments.ward_id', $ward_id);

        return $query;
    }

    public function store($request): EmergencyAllotment
    {
        $starting_period = $request->starting_period;
        $closing_period = $request->closing_period;
        $total_beneficiary = (int)$request->no_of_new_beneficiary + (int)$request->no_of_existing_beneficiary;
        $total_amount = $total_beneficiary * (int)$request->per_person_amount;

        try {
            $allotment = new EmergencyAllotment();
            $allotment->fill([
                'name_en' => $request->name_en,
                'name_bn' => $request->name_bn,
                'payment_cycle' => $request->payment_cycle,
                'amount_per_person' => $request->per_person_amount,
                'division_id' => $request->division_id,
                'district_id' => $request->district_id,
                'location_type' => $request->location_type,
                'sub_location_type' => $request->sub_location_type,
                'no_of_new_benificiariy' => $request->no_of_new_beneficiary,
                'no_of_existing_benificiariy' => $request->no_of_existing_beneficiary,
                'total_beneficiaries' => $total_beneficiary,
                'total_amount' => $total_amount,
                'starting_period' => $starting_period,
                'closing_period' => $closing_period,
                'created_by_id' => Auth()->user()->id,
                'status' => 1,
                'financial_year_id' => $this->getFinancialYearId($starting_period, $closing_period),
            ]);

            $locationFields = [
                'ward_id_city' => 'ward_id_city',
                'ward_id_dist' => 'ward_id_dist',
                'ward_id_union' => 'ward_id_union',
                'ward_id_pouro' => 'ward_id_pouro'
            ];

            foreach ($locationFields as $field => $value) {
                if ($request->has($field) && $request->$field != null) {
                    $allotment->location_id = $request->$field;
                    break;
                }
            }

            switch ($request->location_type) {
                case 1: // Dist pouro
                    $allotment->district_pourashava_id = $request->district_pouro_id;
                    $allotment->ward_id = $request->ward_id_dist;
                    break;
                case 2: // Upazila
                    $allotment->upazila_id = $request->thana_id;
                    if ($request->sub_location_type == 2) { // Union
                        $allotment->union_id = $request->union_id;
                        $allotment->ward_id = $request->ward_id_union;
                    } else { // Pouro
                        $allotment->pourashava_id = $request->pouro_id;
                        $allotment->ward_id = $request->ward_id_pouro;
                    }
                    break;
                case 3: // City corporation
                    $allotment->city_corp_id = $request->city_id;
                    $allotment->thana_id = $request->city_thana_id;
                    $allotment->ward_id = $request->ward_id_city;
                    break;
            }
            $allotment->save();
            if (!empty($request->program_id)) {
                $allotment->programs()->sync($request->program_id);
            }
            return $allotment;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    private function getFinancialYearId($startDate, $endDate)
    {
        $startYear = date('Y', strtotime($startDate));
        $endYear = date('y', strtotime($endDate));
        $startMonth = date('m', strtotime($startDate));
        $endMonth = date('m', strtotime($endDate));
        if ($startYear === $endYear) {
            if ($startMonth >= 1 && $endMonth <= 6) {
                $startYear = (int)$startYear - 1;
            } elseif ($startMonth >= 6 && $endMonth <= 12) {
                $endYear = (int)$startYear + 1;
            } else {
                return null;
            }
        }
        $financialYearString = $startYear . '-' . $endYear;
        $financialYear = FinancialYear::where('financial_year', $financialYearString)
            ->firstOrFail();
        return $financialYear->id;
    }

    public function edit($id)
    {
        return EmergencyAllotment::where('id', $id)->with(['programs', 'division', 'district', 'upazila', 'cityCorporation', 'districtPourosova', 'location'])->first();
    }

    public function update($request, $id)
    {
        $starting_period = $request->starting_period;
        $closing_period = $request->closing_period;
        $total_beneficiary = (int)$request->no_of_new_beneficiary + (int)$request->no_of_existing_beneficiary;
        $total_amount = $total_beneficiary * (int)$request->per_person_amount;
        try {

            $allotment = EmergencyAllotment::findOrFail($id);
            $allotment->name_en = $request->name_en;
            $allotment->name_bn = $request->name_bn;
            $allotment->payment_cycle = $request->payment_cycle;
            $allotment->amount_per_person = $request->per_person_amount;
            $allotment->division_id = $request->division_id;
            $allotment->district_id = $request->district_id;
            $allotment->location_type = $request->location_type;
            $allotment->sub_location_type = $request->sub_location_type;
            $allotment->no_of_new_benificiariy = $request->no_of_new_beneficiary;
            $allotment->no_of_existing_benificiariy = $request->no_of_existing_beneficiary;
            $allotment->total_beneficiaries = $total_beneficiary;
            $allotment->total_amount = $total_amount;
            $allotment->starting_period = $request->starting_period;
            $allotment->closing_period = $request->closing_period;
            $allotment->updated_by_id = Auth()->user()->id;
            $allotment->financial_year_id = $this->getFinancialYearId($starting_period, $closing_period);

            $locationFields = [
                'ward_id_city' => 'ward_id_city',
                'ward_id_dist' => 'ward_id_dist',
                'ward_id_union' => 'ward_id_union',
                'ward_id_pouro' => 'ward_id_pouro'
            ];

            foreach ($locationFields as $field => $value) {
                if ($request->has($field) && $request->$field != null) {
                    $allotment->location_id = $request->$field;
                    break;
                }
            }

            //Dist pouro
            switch ($request->location_type) {
                case 1: // Dist pouro
                    $allotment->district_pourashava_id = $request->district_pouro_id;
                    $allotment->ward_id = $request->ward_id_dist;
                    break;
                case 2: // Upazila
                    $allotment->upazila_id = $request->thana_id;
                    if ($request->sub_location_type == 2) { // Union
                        $allotment->union_id = $request->union_id;
                        $allotment->ward_id = $request->ward_id_union;
                    } else { // Pouro
                        $allotment->pourashava_id = $request->pouro_id;
                        $allotment->ward_id = $request->ward_id_pouro;
                    }
                    break;
                case 3: // City corporation
                    $allotment->city_corp_id = $request->city_id;
                    $allotment->thana_id = $request->city_thana_id;
                    $allotment->ward_id = $request->ward_id_city;
                    break;
            }
            $allotment->update();
            if (!empty($request->program_id)) {
                $allotment->programs()->sync($request->program_id);
            } else {
                $allotment->programs()->detach($allotment->program_id);
            }

            return $allotment;
        } catch (\Throwable $th) {
            throw $th;
        }
    }

    public function getFinancialId($startDate, $endDate)
    {
        $startYear = date('Y', strtotime($startDate));
        $endYear = date('Y', strtotime($endDate));
        $startMonth = date('m', strtotime($startDate));
        $endMonth = date('m', strtotime($endDate));
        if ($startYear === $endYear) {
            if ($startMonth >= 1 && $endMonth <= 6) {
                $startYear = (int)$startYear - 1;
            } elseif ($startMonth >= 6 && $endMonth <= 12) {
                $endYear = (int)$startYear + 1;
            } else {
                return null;
            }
        }
        $financialYearString = $startYear . '-' . $endYear;
        $financialYear = FinancialYear::where('financial_year', $financialYearString)->firstOrFail();
        return $financialYear->id;
    }

    public function destroy($id)
    {
        try {
            $allotment = EmergencyAllotment::with('programs')->find($id);
            $programs = $allotment->programs;
            if ($programs) {
                $allotment->programs()->detach();
            }
            $allotment->delete();
            return $allotment;
        } catch (\Throwable $th) {
            throw $th;
        }
    }
}
