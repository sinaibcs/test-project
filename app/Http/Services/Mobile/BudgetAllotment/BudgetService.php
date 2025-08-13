<?php

namespace App\Http\Services\Mobile\BudgetAllotment;


use App\Helpers\Helper;
use App\Http\Requests\Admin\Budget\ApproveBudgetRequest;
use App\Http\Requests\Admin\Budget\StoreBudgetRequest;
use App\Http\Requests\Admin\Budget\UpdateBudgetRequest;
use App\Http\Resources\Admin\Location\LocationResource;
use App\Jobs\CreateAllotment;
use App\Jobs\ProcessBudget;
use App\Models\AllowanceProgram;
use App\Models\Beneficiary;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\FinancialYear;
use App\Models\Location;
use App\Models\Lookup;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;


class BudgetService
{
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
     * @param StoreBudgetRequest $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function save(StoreBudgetRequest $request): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $budget_id = mt_rand(100000, 999999);
        $validated = $request->safe()->merge(['budget_id' => $budget_id])->only(['budget_id', 'program_id', 'financial_year_id', 'calculation_type', 'no_of_previous_year', 'calculation_value', 'remarks']);
        $budget = Budget::create($validated);
        ProcessBudget::dispatch($this->get($budget->id));
        Helper::activityLogInsert($budget, '', 'Budget', 'Budget Created!');
        return $budget;
    }

    /**
     * @param Budget $budget
     * @return bool|Throwable|\Exception
     * @throws Throwable
     */
    public function processBudget(Budget $budget): bool|\Throwable|\Exception
    {
        DB::beginTransaction();
        try {
            $financialYear = FinancialYear::findOrFail($budget->financial_year_id);
            $calculationType = Lookup::findOrFail($budget->calculation_type);
            $no_of_previous_year = $budget->no_of_previous_year ?: 1;
            $previousFinancialYearIds = FinancialYear::whereDate('end_date', '<', $financialYear->end_date)->orderBy('end_date')->limit($no_of_previous_year)->pluck('id')->toArray();

            $allotmentAreas = DB::select("
            SELECT
                l.location_type,
                l.id AS locatoin_id,
                l.id AS ward_id,
                NULL AS union_id,
                NULL AS pourashava_id,
                NULL AS thana_id,
                p1.id AS district_pourashava_id,
                NULL AS upazila_id,
                NULL AS city_corp_id,
                p2.id AS district_id,
                p3.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            WHERE
                l.location_type = 1
                AND l.`type` = 'ward'
                AND l.deleted_at IS NULL
            UNION
            SELECT
                p1.location_type,
                l.id AS locatoin_id,
                NULL AS ward_id,
                l.id AS union_id,
                NULL AS pourashava_id,
                NULL AS thana_id,
                NULL AS district_pourashava_id,
                p1.id AS upazila_id,
                NULL AS city_corp_id,
                p2.id AS district_id,
                p3.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            WHERE
                p1.location_type = 2
                AND l.`type` = 'union'
                AND l.deleted_at IS NULL
            UNION
            SELECT
                p1.location_type,
                l.id AS locatoin_id,
                NULL AS ward_id,
                NULL AS union_id,
                l.id AS pourashava_id,
                NULL AS thana_id,
                NULL AS district_pourashava_id,
                p1.id AS upazila_id,
                NULL AS city_corp_id,
                p2.id AS district_id,
                p3.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            WHERE
                p1.location_type = 2
                AND l.`type` = 'pouro'
                AND l.deleted_at IS NULL
            UNION
            SELECT
                l.location_type,
                l.id AS locatoin_id,
                l.id AS ward_id,
                NULL AS union_id,
                NULL AS pourashava_id,
                p1.id AS thana_id,
                NULL AS district_pourashava_id,
                p1.id AS upazila_id,
                p2.id AS city_corp_id,
                p3.id AS district_id,
                p4.id AS division_id
            FROM
                locations l
            JOIN locations p1 ON
                l.parent_id = p1.id
            JOIN locations p2 ON
                p1.parent_id = p2.id
            JOIN locations p3 ON
                p2.parent_id = p3.id
            JOIN locations p4 ON
                p3.parent_id = p4.id
            WHERE
                l.location_type = 3
                AND l.`type` = 'ward'
                AND l.deleted_at IS NULL;
            ");

            foreach ($allotmentAreas as $allotmentArea) {
                $budgetDetail = [
                    'budget_id' => $budget->id,
                    'total_beneficiaries' => 100,
                    'per_beneficiary_amount' => 100,
                    'total_amount' => 10000,
                    'division_id' => $allotmentArea->division_id,
                    'district_id' => $allotmentArea->district_id,
                    'location_type' => $allotmentArea->location_type,
                    'city_corp_id' => $allotmentArea->city_corp_id,
                    'upazila_id' => $allotmentArea->upazila_id,
                    'district_pourashava_id' => $allotmentArea->district_pourashava_id,
                    'thana_id' => $allotmentArea->thana_id,
                    'pourashava_id' => $allotmentArea->pourashava_id,
                    'union_id' => $allotmentArea->union_id,
                    'ward_id' => $allotmentArea->ward_id,
                    'location_id' => $allotmentArea->locatoin_id,
                    'created_at' => now()
                ];
                BudgetDetail::create($budgetDetail);
            }

            $budget->process_flag = 1;
            $budget->save();
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            return $throwable;
        }
        return true;
    }

    /**
     * @param Budget $budget
     * @param array $previousFinancialYearIds
     * @param $calculationType
     * @param array $location
     * @return int[]
     */
    public function calculateBudget(Budget $budget, array $previousFinancialYearIds, $calculationType, array $location = array()): array
    {
        // initialize
        $data = [
            'previous_total_beneficiary' => 0,
            'previous_total_amount' => 0,
            'current_total_beneficiary' => 0,
            'current_total_amount' => 0,
        ];

        $program_id = $budget->program_id;
        $financial_year_id = $budget->financial_year_id;
        $calculation_type = $budget->calculation_type;
        $no_of_previous_year = $budget->no_of_previous_year;
        $calculation_value = $budget->calculation_value;

        $query = DB::table('budget')
            ->join('budget_detail', 'budget_detail.budget_id', '=', 'budget.id')
            ->where('budget.program_id', $program_id)
            ->whereIn('budget.financial_year_id', $previousFinancialYearIds);
        $beneficiaryQuery = DB::table('beneficiaries')
            ->where('financial_year_id', $financial_year_id)
            ->where('program_id', $program_id);
        if (empty($location)) {
            if (isset($location['division_id']) && $location['division_id'] != null) {
                $query = $query->where('budget_detail.division_id', $location['division_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('division_id', $location['division_id']);
            }
            if (isset($location['district_id']) && $location['district_id'] != null) {
                $query = $query->where('budget_detail.district_id', $location['district_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('district_id', $location['district_id']);
            }
            if (isset($location['city_corp_id']) && $location['city_corp_id'] != null) {
                $query = $query->where('budget_detail.city_corp_id', $location['city_corp_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('city_corp_id', $location['city_corp_id']);
            }
            if (isset($location['district_pourashava_id']) && $location['district_pourashava_id'] != null) {
                $query = $query->where('budget_detail.district_pourashava_id', $location['district_pourashava_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('district_pourashava_id', $location['district_pourashava_id']);
            }
            if (isset($location['upazila_id']) && $location['upazila_id'] != null) {
                $query = $query->where('budget_detail.upazila_id', $location['upazila_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('upazila_id', $location['upazila_id']);
            }
            if (isset($location['pourashava_id']) && $location['pourashava_id'] != null) {
                $query = $query->where('budget_detail.pourashava_id', $location['pourashava_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('pourashava_id', $location['pourashava_id']);
            }
            if (isset($location['thana_id']) && $location['thana_id'] != null) {
                $query = $query->where('budget_detail.thana_id', $location['thana_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('thana_id', $location['thana_id']);
            }
            if (isset($location['union_id']) && $location['union_id'] != null) {
                $query = $query->where('budget_detail.union_id', $location['union_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('union_id', $location['union_id']);
            }
            if (isset($location['ward_id']) && $location['ward_id'] != null) {
                $query = $query->where('budget_detail.ward_id', $location['ward_id']);
                $beneficiaryQuery = $beneficiaryQuery->where('ward_id', $location['ward_id']);
            }
        }
        $previousBudgetResult = $query->selectRaw('avg(budget_detail.total_beneficiary) as total_beneficiary, avg(budget_detail.total_amount)')->first();
        $previous_total_beneficiary = $previousBudgetResult->total_beneficiary;
        $previous_total_amount = $previousBudgetResult->total_amount;
        if ($previous_total_beneficiary == 0 || $previous_total_amount == 0) {
            $currentBeneficiaryResult = $beneficiaryQuery->selectRaw('count(id) as total_beneficiary, max(monthly_allowance)')->first();
            $current_total_beneficiary = $currentBeneficiaryResult->total_beneficiary;
            $per_beneficiary_amount = $currentBeneficiaryResult->per_beneficiary_amount;
            $current_total_amount = $current_total_beneficiary * $per_beneficiary_amount;
        } else {

        }

        return $data;
    }

    /**
     * @param Request $request
     * @param $getAllRecords
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function list(Request $request, $getAllRecords = false)
    {
        $program_id = $request->query('program_id');
        $financial_year_id = $request->query('financial_year_id');
        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = Budget::query();
        if ($program_id)
            $query = $query->where('program_id', $program_id);

        if ($financial_year_id)
            $query = $query->where('financial_year_id', $financial_year_id);

        if ($getAllRecords)
            return $query->with('program',
                'calculationType',
                'financialYear')
                ->orderBy("$sortByColumn", "$orderByDirection")
                ->get();
        else
            return $query->with('program',
                'calculationType',
                'financialYear')
                ->orderBy("$sortByColumn", "$orderByDirection")
                ->paginate($perPage);

    }

    /**
     * @param $id
     * @return \Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Eloquent\Model|null
     */
    public function get($id)
    {
        return Budget::with('program', 'calculationType', 'financialYear')->find($id);
    }

    /**
     * @param UpdateBudgetRequest $request
     * @param $id
     * @return mixed
     */
    public function update(UpdateBudgetRequest $request, $id)
    {
        $budget = Budget::findOrFail($id);
        $beforeUpdate = $budget->replicate();
        $validated = $request->safe()->only(['calculation_type', 'no_of_previous_year', 'calculation_value', 'remarks']);
        $budget->fill($validated);
        $budget->save();
        Helper::activityLogUpdate($budget, $beforeUpdate, "Budget", "Budget Updated!");
        return $budget;
    }

    /**
     * @param ApproveBudgetRequest $request
     * @param $id
     * @return mixed
     */
    public function approve(ApproveBudgetRequest $request, $id)
    {
        $budget = Budget::findOrFail($id);
        $validated = $request->validated();
        $budget->fill($validated);
        $budget->is_approved = true;
        $budget->approval_status = 'Approved';
        $budget->approved_at = now();
        if ($request->hasFile('approved_document'))
            $budget->approved_document = $request->file('approved_document')->store('public');

        $budget->save();
        CreateAllotment::dispatch($id);
        return $budget;
    }

    /**
     * @param $id
     * @return true
     * @throws Throwable
     */
    public function delete($id)
    {
        DB::beginTransaction();
        try {
            $budget = Budget::findOrFail($id);
            $budget->budgetDetail()->delete();
            $budget->delete();
            DB::commit();
            return true;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param $budget_id
     * @return void
     * @throws Throwable
     */
    public function createAllotment($budget_id)
    {
        DB::beginTransaction();
        $budget = Budget::findOrFail($budget_id);
        if ($budget->allotment_create_flag == 1 || $budget->is_approved == 0 || $budget->process_flag == 0) {
            throw new Exception('Either allotment created or budget not yet approved', ResponseAlias::HTTP_BAD_REQUEST);
        }
        try {
            DB::insert("
            INSERT
                INTO
                allotments (
                budget_id,
                program_id,
                financial_year_id,
                location_type,
                location_id,
                ward_id,
                union_id,
                pourashava_id,
                thana_id,
                district_pourashava_id,
                upazila_id,
                city_corp_id,
                district_id,
                division_id,
                regular_beneficiaries,
                additional_beneficiaries,
                total_beneficiaries,
                per_beneficiary_amount,
                total_amount
                )
            SELECT
                d.budget_id,
                b.program_id,
                b.financial_year_id,
                d.location_type,
                d.location_id,
                d.ward_id,
                d.union_id,
                d.pourashava_id,
                d.thana_id,
                d.district_pourashava_id,
                d.upazila_id,
                d.city_corp_id,
                d.district_id,
                d.division_id,
                d.total_beneficiaries as regular_beneficiaries,
                0 as additional_beneficiaries,
                d.total_beneficiaries,
                d.per_beneficiary_amount,
                d.total_amount
            FROM
                budgets b
            INNER JOIN budget_details d ON
                b.id = d.budget_id
            WHERE
                d.budget_id = $budget_id;
            ");
            $budget->allotment_create_flag = 1;
            $budget->save();
            DB::commit();
        } catch (Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }

    /**
     * @param Request $request
     * @param $program_id
     * @param $financial_year_id
     * @return array
     */
    public function getProjection(Request $request, $program_id, $financial_year_id): array
    {
        $location_id = $request->query('location_id');
        $location = $location_id ? Location::findOrFail($location_id) : null;
        $program = AllowanceProgram::findOrFail($program_id);
        $query = Beneficiary::query()->where('program_id', $program_id)->where('financial_year_id', $financial_year_id);
//        if ($location_id)
//            $query = $query->where('program_id', $program_id);
        $coming_year_total_ben = $query->count();
        $coming_year_total_amount = 100;

        $previous_financial_year = FinancialYear::whereRaw(
            "start_date < (
                SELECT
                    fy2.start_date
                from
                    financial_years fy2
                WHERE
                    fy2.id = $financial_year_id)")
            ->orderBy('start_date', 'desc')
            ->first();
        $query2 = Beneficiary::query()->where('program_id', $program_id)->where('financial_year_id', $previous_financial_year?->id);
//        if ($location_id)
//            $query = $query->where('program_id', $program_id);
        $previous_year_total_ben = $query2->count();
        $previous_year_total_amount = 90;
        return [
            'location' => $location,
            'program' => $program,
            'previous_year_total_ben' => $previous_year_total_ben,
            'previous_year_total_amount' => $previous_year_total_amount,
            'coming_year_total_ben' => $coming_year_total_ben,
            'coming_year_total_amount' => $coming_year_total_amount
        ];
    }

    /**
     * @param Request $request
     * @param bool $getAllRecords
     * @return mixed
     */
    public function detailList($budget_id, Request $request, bool $getAllRecords = false)
    {

        $perPage = $request->query('perPage', 10);

        $query = BudgetDetail::query()->where('budget_id', $budget_id);

        $query = $this->applyLocationFilter($query, $request);

        if ($getAllRecords)
            return $query->with('budget', 'upazila', 'cityCorporation', 'districtPourosova', 'location')
                ->orderBy('location_type')
                ->orderBy('upazila_id')
                ->orderBy('city_corp_id')
                ->orderBy('district_pourashava_id')
                ->get();
        else
            return $query->with('budget', 'upazila', 'cityCorporation', 'districtPourosova', 'location')
                ->orderBy('location_type')
                ->orderBy('upazila_id')
                ->orderBy('city_corp_id')
                ->orderBy('district_pourashava_id')
                ->paginate($perPage);

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
     * @param $budget_id
     * @param Request $request
     * @return null
     * @throws Throwable
     */
    public function detailUpdate($budget_id, Request $request)
    {
        DB::beginTransaction();
        try {
            $budget = Budget::find($budget_id);
            if (!$budget) {
                DB::rollBack();
                throw new \Exception('No budget was found!');
            } elseif (!$request->has('budget_details')) {
                DB::rollBack();
                throw new \Exception('No budget location was found!');
            }
            foreach ($request->input('budget_details') as $budget_detail) {
                $budgetDetailInstance = BudgetDetail::findOrFail($budget_detail['id']);
                $budgetDetailInstanceBeforeUpdate = $budgetDetailInstance->replicate();
                $location = Location::find($budgetDetailInstance->location_id);
                $budgetDetailInstance->total_beneficiaries = $budget_detail['total_beneficiaries'];
                $budgetDetailInstance->per_beneficiary_amount = $budget_detail['per_beneficiary_amount'];
                $budgetDetailInstance->total_amount = $budget_detail['total_amount'];
                $budgetDetailInstance->updated_at = now();
                $budgetDetailInstance->save();

                Helper::activityLogUpdate($budgetDetailInstance, $budgetDetailInstanceBeforeUpdate, "Budget", "Budget updated for location: " . $location?->name_en);
            }

            DB::commit();
            return null;
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
