<?php

namespace App\Http\Services\Admin\BudgetAllotment;


use App\Helpers\Helper;
use function Psy\debug;
use App\Models\Location;
use App\Models\Allotment;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\AllowanceProgram;
use Illuminate\Support\Facades\DB;
use App\Models\AllowanceProgramAge;
use App\Constants\BeneficiaryStatus;
use App\Models\AllowanceProgramAmount;
use App\Http\Requests\Admin\Allotment\UpdateAllotmentRequest;

/**
 * Allotment Service
 */
class AllotmentService
{
    function __construct(private BudgetService $budgetService){

    }
    public function summary(Request $request): mixed
    {
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $perPage = $request->query('perPage', 50);

        $query = Allotment::query();
        $query = $query->selectRaw('program_id, financial_year_id, sum(total_beneficiaries) as total_beneficiaries, sum(total_amount) as total_amount');
        if ($program_id)
            $query = $query->where('program_id', $program_id);

        if ($financial_year_id)
            $query = $query->where('financial_year_id', $financial_year_id);
        $query = $query->with('program', 'financialYear');
        $query = $query->groupBy('program_id', 'financial_year_id');
        $query = $query->orderByRaw('financial_year_id desc, program_id asc');
        return $query->paginate($perPage);

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

    public function getList1($program_id, $financial_year_id, $location_id = null)
    {
        $program = AllowanceProgram::query()->findOrFail($program_id);
        $program_has_class = $program?->is_disable_class;
        $classes = $program_has_class ? AllowanceProgramAmount::query()->where('allowance_program_id', $program_id)->get() : null;

        $query = Allotment::query()
            ->leftJoin('lookups as classes', 'classes.id', '=', 'allotments.type_id');


        $location = $location_id != null ? Location::find($location_id) : null;
        \Log::debug(json_encode($location));
//        dd($location);
        // location
        // $location?->localtion_type;
        // 1=District Pouroshava, 2=Upazila, 3=City Corporation

        // $location?->type;
        // division->district
        // localtion_type=1; district-pouroshava->ward
        // localtion_type=2; thana->{union/pouro}->ward
        // localtion_type=3; thana->ward


        if ($location == null) {
            $query = $query->join('locations', 'locations.id', '=', 'allotments.division_id');
        } elseif ($location?->type == 'division') {
            $query = $query->join('locations', 'locations.id', '=', 'allotments.district_id');
        } elseif ($location?->type == 'district') {
            $query = $query->join('locations', function ($join) {
                return $join->on('locations.id', '=', 'allotments.upazila_id')
                    ->orOn('locations.id', '=', 'allotments.city_corp_id')
                    ->orOn('locations.id', '=', 'allotments.district_pourashava_id');
            });
        } elseif ($location?->type == 'city') {
            if ($location?->location_type == 1) {
                $query = $query->join('locations', 'locations.id', '=', 'allotments.ward_id');
            } elseif ($location?->location_type == 3)
                $query = $query->join('locations', 'locations.id', '=', 'allotments.thana_id');
        } elseif ($location?->type == 'thana') {
            if ($location?->location_type == 2) {
                $query = $query->join('locations', function ($join) {
                    return $join->on('locations.id', '=', 'allotments.union_id')
                        ->orOn('locations.id', '=', 'allotments.pourashava_id');
                });
            } elseif ($location?->parent?->location_type == 3) {
                $query = $query->join('locations', 'locations.id', '=', 'allotments.ward_id');
            }
        } elseif ($location?->type == 'union' || $location?->type == 'pouro') {
            if ($location?->type == 'union')
                $query = $query->join('locations', 'locations.id', '=', 'allotments.union_id');
            elseif ($location?->type == 'pouro')
                $query = $query->join('locations', 'locations.id', '=', 'allotments.pourashava_id');
        } elseif ($location?->type == 'ward') {
            $query = $query->join('locations', 'locations.id', '=', 'allotments.ward_id');
        }

        $query = $query->where('allotments.program_id', $program_id)
            ->where('allotments.financial_year_id', $financial_year_id);
        if ($location?->id)
            $query = $query->where('locations.parent_id', $location?->id);
        $query = $query->selectRaw('locations.id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en as class_name_en, classes.value_bn as class_name_bn, max(allotments.id) as allotment_id, sum(allotments.regular_beneficiaries) as regular_beneficiaries, sum(allotments.additional_beneficiaries) as additional_beneficiaries, sum(allotments.total_beneficiaries) as total_beneficiaries, sum(allotments.total_amount) as total_amount');
        $query = $query->groupByRaw('locations.id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en, classes.value_bn')
            ->orderByRaw('locations.name_en, classes.value_en');
        \Log::debug($query->toRawSql());
        $data = $query->get();
        $data = $data->map(function ($item) use ($program_has_class) {
            $location = Location::query()->find($item->id);

            if ($program_has_class) {
                if ($location?->type == 'city')
                    $is_allotment_area = $program_has_class;
                elseif ($location?->type == 'thana' && $location?->location_type == 2)
                    $is_allotment_area = $program_has_class;
                else
                    $is_allotment_area = false;
            } else {
                if ($location?->type == 'ward')
                    $is_allotment_area = $location?->location_type == 1 || $location?->location_type == 3;
                elseif ($location?->type == 'union' || $location?->type == 'pouro')
                    $is_allotment_area = $location?->parent?->location_type == 2;
                else
                    $is_allotment_area = false;
            }

            $item->is_allotment_area = $is_allotment_area;
            return $item;
        });

//        $program = AllowanceProgram::query()->find($program_id);
        $financial_year = FinancialYear::query()->find($financial_year_id);
        $resp = [
            'program' => $program,
            'financial_year' => $financial_year,
            'total_beneficiaries' => $data->sum('total_beneficiaries'),
            'total_amount' => $data->sum('total_amount'),
            'items' => $data->toArray(),
        ];

        return $resp;
    }

    public function getListByDistrict($program_id, $financial_year, $location_id){
        $program = AllowanceProgram::findOrFail($program_id);
        $program_has_class = $program->is_disable_class;
        $location = $location_id ? Location::find($location_id) : null;
        $program_has_office_wise_budget = ((bool) $program->is_office_wise_budget) || $this->budgetService->determineOfficeWise($location, $program);
        $query = Allotment::where('program_id', $program_id)->where('district_id', $location_id)->where('financial_year_id', $financial_year)->with('districtPourosova', 'cityCorporation', 'office', 'thana', 'union', 'pourashava', 'type', 'ward', 'upazila', 'location');
        if($program_has_office_wise_budget){
            $query
            // ->selectRaw('district_id, office_id , upazila_id, city_corp_id, district_pourashava_id, location_id, thana_id, union_id, pourashava_id, type_id, ward_id, max(allotments.id) as allotment_id,
            // sum(allotments.regular_beneficiaries) as regular_beneficiaries, sum(allotments.additional_beneficiaries) as additional_beneficiaries,
            // sum(allotments.total_beneficiaries) as total_beneficiaries, sum(allotments.total_amount) as total_amount')
            ->groupByRaw('allotments.office_id, allotments.type_id');
        }else{
            $query
            // ->selectRaw('district_id, upazila_id, city_corp_id, district_pourashava_id, location_id, thana_id, union_id, pourashava_id, type_id, ward_id, max(allotments.id) as allotment_id,
            // sum(allotments.regular_beneficiaries) as regular_beneficiaries, sum(allotments.additional_beneficiaries) as additional_beneficiaries,
            // sum(allotments.total_beneficiaries) as total_beneficiaries, sum(allotments.total_amount) as total_amount')
            ->groupByRaw('allotments.location_id, allotments.type_id')
            ;
        }
        return $query->get()->map(function($item){
            return [
                'upazila_dp_city' => $item->upazila?? $item->cityCorporation ?? $item->districtPourosova,
                'thana_union_pouroshova' => $item->thana ?? $item->union ?? $item->pourashava,
                'ward' => $item->ward,
                'office' => $item->office,
                'class' => $item->type,
                'regular_beneficiaries' => $item->regular_beneficiaries,
                'additional_beneficiaries' => $item->additional_beneficiaries,
                'total_beneficiaries' => $item->total_beneficiaries,
                'total_amount' => $item->total_amount,
                'allotment_id' => $item->id,
            ];
        });
    }

    public function getList($program_id, $financial_year_id, $location_id = null)
    {
        $program = AllowanceProgram::findOrFail($program_id);
        $program_has_class = $program->is_disable_class;
        $location = $location_id ? Location::find($location_id) : null;
        $program_has_office_wise_budget = ((bool) $program->is_office_wise_budget) || $this->budgetService->determineOfficeWise($location, $program);
        $classes = $program_has_class ? AllowanceProgramAmount::where('allowance_program_id', $program_id)->get() : null;

        if($location?->type == 'district' && true){
            $data = $this->getListByDistrict($program_id, $financial_year_id, $location_id);
            return [
                'program' => $program,
                'financial_year' => FinancialYear::find($financial_year_id),
                'total_beneficiaries' => $data->sum('total_beneficiaries'),
                'total_amount' => $data->sum('total_amount'),
                'is_district' => true,
                'items' => $data,
            ];
        }
        $query = Allotment::query()
            ->leftJoin('lookups as classes', 'classes.id', '=', 'allotments.type_id');

        \Log::debug(json_encode($location));

        if (!$location) {
            $query->join('locations', 'locations.id', '=', 'allotments.division_id');
        } else {
            switch ($location->type) {
                case 'division':
                    $query->join('locations', 'locations.id', '=', 'allotments.district_id');
                    break;
                case 'district':
                    $query->join('locations', function ($join) {
                        $join->on('locations.id', '=', 'allotments.upazila_id')
                            ->orOn('locations.id', '=', 'allotments.city_corp_id')
                            ->orOn('locations.id', '=', 'allotments.district_pourashava_id');
                    });
                    break;
                case 'city':
                    if($program_has_office_wise_budget){
                        $query->join('offices', 'offices.id', '=', 'allotments.office_id');
                    }else{
                        $query->join('locations', 'locations.id', '=', $location->location_type == 1 ? 'allotments.ward_id' : 'allotments.thana_id');
                    }
                    break;
                case 'thana':
                    if($program_has_office_wise_budget){
                        $query->join('offices', 'offices.id', '=', 'allotments.office_id');
                    }else{
                        if ($location->location_type == 2) {
                            $query->join('locations', function ($join) {
                                $join->on('locations.id', '=', 'allotments.union_id')
                                    ->orOn('locations.id', '=', 'allotments.pourashava_id');
                            });
                        } elseif ($location->parent->location_type == 3) {
                            $query->join('locations', 'locations.id', '=', 'allotments.ward_id');
                        }
                    }
                    break;
                case 'union':
                case 'pouro':
                    $query->join('locations', 'locations.id', '=', $location->type == 'union' ? 'allotments.union_id' : 'allotments.pourashava_id');
                    break;
                case 'ward':
                    $query->join('locations', 'locations.id', '=', 'allotments.ward_id');
                    break;
            }
        }

        $query->where('allotments.program_id', $program_id)
            ->where('allotments.financial_year_id', $financial_year_id);

        if($location != null && $program_has_office_wise_budget && in_array($location->type, ['thana','city'])){
            $query->where('offices.assign_location_id', $location->id);
            $query->selectRaw('offices.id as office_id, offices.name_en, offices.name_bn,
            classes.value_en as class_name_en, classes.value_bn as class_name_bn, max(allotments.id) as allotment_id,
            sum(allotments.regular_beneficiaries) as regular_beneficiaries, sum(allotments.additional_beneficiaries) as additional_beneficiaries,
            sum(allotments.total_beneficiaries) as total_beneficiaries, sum(allotments.total_amount) as total_amount')
            ->groupByRaw('office_id, offices.name_en, offices.name_bn, classes.value_en, classes.value_bn')
            ->orderByRaw('offices.name_en, classes.value_en');
        }else{
            if ($location?->id) {
                $query->where('locations.parent_id', $location->id);
            }
            $query->selectRaw('locations.id, locations.name_en, locations.name_bn, locations.type, locations.location_type,
                classes.value_en as class_name_en, classes.value_bn as class_name_bn, max(allotments.id) as allotment_id,
                sum(allotments.regular_beneficiaries) as regular_beneficiaries, sum(allotments.additional_beneficiaries) as additional_beneficiaries,
                sum(allotments.total_beneficiaries) as total_beneficiaries, sum(allotments.total_amount) as total_amount')
                ->groupByRaw('locations.id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en, classes.value_bn')
                ->orderByRaw('locations.name_en, classes.value_en');
        }

        \Log::debug($query->toRawSql());

        $data = $query->get()->map(function ($item) use ($program_has_class) {
            $location = Location::find($item->id);

            $is_allotment_area = match (true) {
                $item->office_id != null => true,
                !$program_has_class && (
                    ($location->type == 'ward' && in_array($location->location_type, [1, 3])) ||
                    (in_array($location->type, ['union', 'pouro']) && $location->parent->location_type == 2)
                ) => true,
                default => false,
            };

            $item->is_allotment_area = $is_allotment_area;
            return $item;
        });

        return [
            'program' => $program,
            'financial_year' => FinancialYear::find($financial_year_id),
            'total_beneficiaries' => $data->sum('total_beneficiaries'),
            'total_amount' => $data->sum('total_amount'),
            'items' => $data->toArray(),
        ];
    }


    public function navigate(Request $request)
    {
        $user = auth()->user()->load('assign_location');
        $assignedLocationId = $user->assign_location?->id;
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $location_id = $request->has('location_id') ? $request->query('location_id') : $assignedLocationId;
        $locations = Location::query()->where(function ($query) use ($location_id) {
            if ($location_id) {
                $query->where('parent_id', $location_id);
            } else {
                $query->whereNull('parent_id');
            }
            return $query;
        })->with('locationType', 'parent', 'office')->get();

        $locations->map(function ($location) use ($program_id, $financial_year_id) {
            // $location?->localtion_type;
            // 1=District Pouroshava, 2=Upazila, 3=City Corporation
            // $location?->type;
            // division->district
            // localtion_type=1; district-pouroshava->ward
            // localtion_type=2; thana->{union/pouro}->ward
            // localtion_type=3; thana->ward
            $is_allotment_area = false;
            $budgetLocation = [];
            if ($location?->type == 'ward') {
                $is_allotment_area = $location?->location_type == 1 || $location?->location_type == 3;
                $budgetLocation['ward_id'] = $location->id;
            } elseif ($location?->type == 'union' || $location?->type == 'pouro') {
                $is_allotment_area = $location?->parent?->location_type == 2;
                if ($location?->type == 'union')
                    $budgetLocation['union_id'] = $location->id;
                elseif ($location?->type == 'pouro')
                    $budgetLocation['pourashava_id'] = $location->id;
            } elseif ($location?->type == 'thana') {
                if ($location?->location_type == 2) {
                    $budgetLocation['upazila_id'] = $location->id;
                } elseif ($location?->parent?->location_type == 3) {
                    $budgetLocation['thana_id'] = $location->id;
                }
            } elseif ($location?->type == 'city') {
                if ($location?->location_type == 1)
                    $budgetLocation['district_pourashava_id'] = $location->id;
                elseif ($location?->location_type == 3)
                    $budgetLocation['city_corp_id'] = $location->id;
            } elseif ($location?->type == 'district') {
                $budgetLocation['district_id'] = $location->id;
            } elseif ($location?->type == 'division')
                $budgetLocation['division_id'] = $location->id;

            $budgetLocation['is_allotment_area'] = $is_allotment_area;

            $allotment = $this->getAllotmentDetail($program_id, $financial_year_id, $budgetLocation);
            $location->regular_beneficiaries = $allotment['regular_beneficiaries'];
            $location->additional_beneficiaries = $allotment['additional_beneficiaries'];
            $location->total_beneficiaries = $allotment['total_beneficiaries'];
            $location->total_amount = $allotment['total_amount'];
            $location->is_allotment_area = $is_allotment_area;
        });

        return $locations;
    }

    public function getAllotmentDetail(int $program_id, int $financial_year_id, array $location = array()): array
    {
        $query = Allotment::query()
            ->when($program_id, function ($query) use ($program_id) {
                return $query->where('program_id', $program_id);
            })
            ->when($financial_year_id, function ($query) use ($financial_year_id) {
                return $query->where('financial_year_id', $financial_year_id);
            });
        if (count($location) > 0) {
            if (isset($location['division_id']) && $location['division_id'] != null) {
                $query = $query->where('division_id', $location['division_id']);
            }
            if (isset($location['district_id']) && $location['district_id'] != null) {
                $query = $query->where('district_id', $location['district_id']);
            }
            if (isset($location['city_corp_id']) && $location['city_corp_id'] != null) {
                $query = $query->where('city_corp_id', $location['city_corp_id']);
            }
            if (isset($location['district_pourashava_id']) && $location['district_pourashava_id'] != null) {
                $query = $query->where('district_pourashava_id', $location['district_pourashava_id']);
            }
            if (isset($location['upazila_id']) && $location['upazila_id'] != null) {
                $query = $query->where('upazila_id', $location['upazila_id']);
            }
            if (isset($location['pourashava_id']) && $location['pourashava_id'] != null) {
                $query = $query->where('pourashava_id', $location['pourashava_id']);
            }
            if (isset($location['thana_id']) && $location['thana_id'] != null) {
                $query = $query->where('thana_id', $location['thana_id']);
            }
            if (isset($location['union_id']) && $location['union_id'] != null) {
                $query = $query->where('union_id', $location['union_id']);
            }
            if (isset($location['ward_id']) && $location['ward_id'] != null) {
                $query = $query->where('ward_id', $location['ward_id']);
            }
        }
        $result = $query->selectRaw('sum(regular_beneficiaries) as regular_beneficiaries, sum(additional_beneficiaries) as additional_beneficiaries, sum(total_beneficiaries) as total_beneficiaries, sum(total_amount) as total_amount')->first();

        $data["regular_beneficiaries"] = $result?->regular_beneficiaries ?: 0;
        $data["additional_beneficiaries"] = $result?->additional_beneficiaries ?: 0;
        $data["total_beneficiaries"] = $result?->total_beneficiaries ?: 0;
        $data["total_amount"] = $result?->total_amount ?: 0;

        return $data;
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
            $query = $query->where('division_id', $division_id);
        if ($district_id && $district_id > 0)
            $query = $query->where('district_id', $district_id);
        if ($location_type_id && $location_type_id > 0)
            $query = $query->where('location_type', $location_type_id);
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
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function get($id): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Builder|array|null
    {
        return Allotment::with('program', 'financialYear', 'location')->find($id);
    }

    public function getProgramAllowanceAmount($allotment)
    {
//         $data = DB::table('allowance_program_ages')->selectRaw('max(amount) as amount')->where('allowance_program_id', $allotment->program_id)->first();
// //        dump($data);
//         if (!$data) {
//             $data = DB::table('allowance_program_amounts')->selectRaw('max(amount) as amount')->where('allowance_program_id', $allotment->program_id)->first();
//             $amount = $data?->amount;
//         } else
//             $amount = $data?->amount;

//         return $amount;


        if (!empty($allotment->type_id??null)) {
            $per_beneficiary_amount = AllowanceProgramAmount::where('allowance_program_id', $allotment->program_id)
                ->where('type_id', $allotment->type_id)
                ->max('amount');
        }elseif ($allotment->program->is_age_limit) {
            $per_beneficiary_amount = AllowanceProgramAge::where('allowance_program_id', $allotment->program_id)->max('amount');
        } else {
            $per_beneficiary_amount = AllowanceProgramAmount::where('allowance_program_id', $allotment->program_id)->max('amount');
        }

        return $per_beneficiary_amount;
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
    
    public function updateMany($items): mixed
    {
        DB::beginTransaction();
        try {
            foreach($items as $item){
                $allotment = Allotment::findOrFail($item['allotment_id']);
                // $validated = $request->safe()->only(['additional_beneficiaries', 'total_beneficiaries', 'total_amount']);
                // $allotment->fill($validated);
                $allotment->total_amount = ($allotment->total_amount / $allotment->total_beneficiaries) * ($item['regular_beneficiaries'] + $item['additional_beneficiaries']);
                $allotment->regular_beneficiaries = $item['regular_beneficiaries'];
                $allotment->additional_beneficiaries = $item['additional_beneficiaries'];
                $allotment->total_beneficiaries = $item['regular_beneficiaries'] + $item['additional_beneficiaries'];
                $allotment->updated_at = now();
                $allotment->save();
                activity("Budget")
                ->causedBy(auth()->user())
                ->performedOn($allotment)
                ->log('Budget Updated!');
            }
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
        Helper::activityLogDelete($allotment, '', 'Allotment', 'Allotment Deleted!!');
        return $allotment->delete();
    }

    public static function remainingSpace($beneficiaryId){
        $financialYear = getCurrentFinancialYear();
        $beneficiary = Beneficiary::find($beneficiaryId);
        $allotment = Allotment::where('financial_year_id',$financialYear->id)
        ->where('program_id', $beneficiary->program_id);
        $allowance_program = AllowanceProgram::select('is_office_wise_budget')->find($beneficiary->program_id);

        if($beneficiary->permanent_location_type_id == 1 || $beneficiary->permanent_location_type_id == 3){
            $allotment->where('ward_id', $beneficiary->permanent_ward_id);
        }elseif($beneficiary->permanent_location_type_id == 2){
            if($beneficiary->type_id){
                $allotment->where('type_id', $beneficiary->type_id)
                ->where('upazila_id', $beneficiary->permanent_upazila_id);
            }else{
                if ($allowance_program->is_office_wise_budget == 1){
                    $allotment->where('upazila_id', $beneficiary->permanent_upazila_id);
                } else {
                    $allotment->where('union_id', $beneficiary->permanent_union_id);
                }
            }
        }
        $allotment = $allotment->first();
        if($allotment == null){
            throw new \Exception('Allotment does not exits for this area');
        }
        $ben = Beneficiary::where('program_id', $allotment->program_id);
        if($allotment->location_type == 1 || $allotment->location_type == 3){
            $ben->where('permanent_ward_id', $allotment->location_id);
        }elseif($allotment->location_type == 2){
            if($allotment->type_id){
                $ben->where('type_id', $allotment->type_id)
                ->where('permanent_upazila_id', $allotment->upazila_id);
            }else{
                if ($allowance_program->is_office_wise_budget == 1){
                    $ben->where('permanent_upazila_id', $allotment->location_id);
                } else {
                    $ben->where('permanent_union_id', $allotment->location_id);
                }
            }
        }
        $ben->where('status', BeneficiaryStatus::ACTIVE);
        return $allotment->total_beneficiaries - $ben->count();
    }

    public static function remainingSpaceForApplication($application){
        $financialYear = getCurrentFinancialYear();
        $allotment = Allotment::where('financial_year_id',$financialYear->id)
        ->where('program_id', $application->program_id);
        $allowance_program = AllowanceProgram::select('is_office_wise_budget')->find($application->program_id);

        if($application->permanent_location_type_id == 1 || $application->permanent_location_type_id == 3){
            $allotment->where('ward_id', $application->permanent_ward_id);
        }elseif($application->permanent_location_type_id == 2){
            if($application->type_id){
                $allotment->where('type_id', $application->type_id)
                ->where('upazila_id', $application->permanent_upazila_id);
            }else{
                if ($allowance_program->is_office_wise_budget == 1){
                    $allotment->where('upazila_id', $application->permanent_upazila_id);
                } else {
                    $allotment->where('union_id', $application->permanent_union_id);
                }
            }
        }
        $allotment = $allotment->first();
        if($allotment == null){
            throw new \Exception('Allotment does not exits for this area');
        }
        $ben = Beneficiary::where('program_id', $allotment->program_id);
        if($allotment->location_type == 1 || $allotment->location_type == 3){
            $ben->where('permanent_ward_id', $allotment->location_id);
        }elseif($allotment->location_type == 2){
            if($allotment->type_id){
                $ben->where('type_id', $allotment->type_id)
                ->where('permanent_upazila_id', $allotment->upazila_id);
            }else{
                if ($allowance_program->is_office_wise_budget == 1){
                    $ben->where('permanent_upazila_id', $allotment->location_id);
                } else {
                    $ben->where('permanent_union_id', $allotment->location_id);
                }
            }
        }
        $ben->where('status', BeneficiaryStatus::ACTIVE);
        return $allotment->total_beneficiaries - $ben->count();
    }

}
