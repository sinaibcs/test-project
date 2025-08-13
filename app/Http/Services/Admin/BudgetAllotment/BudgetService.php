<?php

namespace App\Http\Services\Admin\BudgetAllotment;


use App\Helpers\Helper;
use App\Http\Requests\Admin\Budget\ApproveBudgetRequest;
use App\Http\Requests\Admin\Budget\StoreBudgetRequest;
use App\Http\Requests\Admin\Budget\UpdateBudgetRequest;
use App\Http\Resources\Admin\Location\LocationResource;
use App\Jobs\CreateAllotment;
use App\Jobs\ProcessBudget;
use App\Models\AllowanceProgram;
use App\Models\AllowanceProgramAge;
use App\Models\AllowanceProgramAmount;
use App\Models\Beneficiary;
use App\Models\Budget;
use App\Models\BudgetDetail;
use App\Models\FinancialYear;
use App\Models\Location;
use App\Models\Lookup;
use App\Models\OfficeHasWard;
use Carbon\Carbon;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Log;
use Mockery\Exception;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use Throwable;
use function Laravel\Prompts\error;


class BudgetService
{
    /**
     * @return FinancialYear|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function currentFinancialYear()
    {
        return FinancialYear::query()->where('status', 1)->first();
    }

    /**
     * @return FinancialYear|\Illuminate\Database\Eloquent\Builder|\Illuminate\Database\Eloquent\Model|object|null
     */
    public function budgetFinancialYear()
    {
        return FinancialYear::query()->whereIn('status', [1, 2])->orderBy('start_date', 'desc')->get();
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
     * @param StoreBudgetRequest $request
     * @return \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
     */
    public function save(StoreBudgetRequest $request): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $budget_id = mt_rand(100000, 999999);
        $created_by_id = auth()->user()->id;
        $validated = $request->safe()->merge(['budget_id' => $budget_id, 'created_by_id' => $created_by_id])->only(['budget_id', 'program_id', 'financial_year_id', 'calculation_type', 'prev_financial_year_ids', 'calculation_value', 'remarks']);
        $budget = Budget::create($validated);
        ProcessBudget::dispatch($this->get($budget->id));
        Helper::activityLogInsert($budget, '', 'Budget', 'Budget Created!');
        return $budget;
    }
    
    public function saveForFileUpload($data): \Illuminate\Database\Eloquent\Model|\Illuminate\Database\Eloquent\Collection|bool|\Illuminate\Database\Eloquent\Builder|array|null
    {
        $budget_id = mt_rand(100000, 999999);
        $created_by_id = auth()->user()->id;
        $budget = Budget::create(array_merge(['budget_id' => $budget_id, 'created_by_id' => $created_by_id, 'calculation_value' => 0, 'calculation_type' => 0, 'process_flag' => 1 ], $data));
        Helper::activityLogInsert($budget, '', 'Budget', 'Budget Created!');
        return $budget;
    }

    /**
     * @param Budget $budget
     * @return bool|Throwable|\Exception
     * @throws Throwable
     */
    public function processBudget1(Budget $budget)
    {
        DB::beginTransaction();
        try {
            info('Processing budget: ' . $budget->budget_id);
            $program_id = $budget->program_id;
            $program = AllowanceProgram::query()->findOrFail($program_id);
            $program_has_class = (bool)$program?->is_disable_class;
            $classes = $program_has_class ? AllowanceProgramAmount::query()->where('allowance_program_id', $program_id)->get() : null;
            if ($program_has_class)
                $allotmentAreas = DB::select("select
                                            location_type_id as location_type,
                                            locatoin_id,
                                            null as ward_id,
                                            null as union_id,
                                            null as pourashava_id,
                                            null as thana_id,
                                            district_pourashava_id,
                                            upazila_id,
                                            city_corp_id,
                                            district_id,
                                            division_id
                                        from
                                            allotment_areas_1st_level_view");
            else
                $allotmentAreas = DB::select("select
                                            location_type,
                                            locatoin_id,
                                            ward_id,
                                            union_id,
                                            pourashava_id,
                                            thana_id,
                                            district_pourashava_id,
                                            upazila_id,
                                            city_corp_id,
                                            district_id,
                                            division_id
                                        from
                                            allotment_areas_view");

            $financial_year_id = $budget->financial_year_id;
            $calculation_type = Lookup::find($budget->calculation_type)?->keyword;//$budget->calculationType()->keyword;
            $calculation_value = $budget->calculation_value;
            $current_financial_year_id = $this->currentFinancialYear()?->id;
            $previous_financial_year_ids = explode(',', $budget->prev_financial_year_ids);
            $budgetDetail = [];
            foreach ($allotmentAreas as $allotmentArea) {
                if ($program_has_class) {
                    foreach ($classes as $class) {
                        $location = [
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
                        ];
                        $budget_value = $this->calculateBudget($program_id, $class->type_id, $current_financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $location);
                        $budgetDetail[] = [
                            'budget_id' => $budget->id,
                            'total_beneficiaries' => $budget_value['current_total_beneficiary'],
                            'total_amount' => $budget_value['current_total_amount'],
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
                            'type_id' => $class->type_id,
                            'created_at' => now()
                        ];
                    }
                } else {
                    $location = [
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
                    ];
                    $budget_value = $this->calculateBudget($program_id, null, $current_financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $location);
                    $budgetDetail[] = [
                        'budget_id' => $budget->id,
                        'total_beneficiaries' => $budget_value['current_total_beneficiary'],
                        'total_amount' => $budget_value['current_total_amount'],
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
                }
//                BudgetDetail::create($budgetDetail);
            }
//            dump($budgetDetail);
            $budget->process_flag = 1;
            $budget->save();
            if (count($budgetDetail) > 0)
                $budget->budgetDetail()->createMany($budgetDetail);
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            // set status fail
            error('Processing budget failed!!');
            error($throwable->getMessage());
            $budget->approval_status = 'Failed';
            $budget->process_flag = -1;
            $budget->save();
            return $throwable;
        }
        return true;
    }

    public function processBudget(Budget $budget)
    {
        ini_set('max_execution_time', '0');

        DB::beginTransaction();
        try {
            info('Processing budget: ' . $budget->budget_id);
            
            $program = AllowanceProgram::findOrFail($budget->program_id);
            // info('program: ' . json_encode($program));
            $program_has_class = (bool) $program->is_disable_class;
            $program_is_office_wise = (bool) $program->is_office_wise_budget;
            $classes = $program_has_class ? AllowanceProgramAmount::where('allowance_program_id', $program->id)->get() : null;

            // Select correct allotment areas view
            if($this->distPauroIsOfficeWise($program)){
                $allotmentView = 'combined_allotment_office_area_view';
            }else{
                $allotmentView = $program_is_office_wise ? 'allotment_areas_1st_level_view' : 'allotment_areas_view';
            }
            $allotmentAreas = DB::select("SELECT * FROM {$allotmentView}");

            $financial_year_id = $budget->financial_year_id;
            $calculation_type = Lookup::find($budget->calculation_type)?->keyword;
            $calculation_value = $budget->calculation_value;
            $current_financial_year_id = $this->currentFinancialYear()?->id;
            $previous_financial_year_ids = explode(',', $budget->prev_financial_year_ids);

            $budgetDetail = [];

            foreach ($allotmentAreas as $area) {
                // \Log::debug(json_encode($area));

                if (isset($area->office_id) && $area->office_id != null) {
                    if($program_has_class){
                        foreach ($classes as $class) {
                            $budget_value = $this->calculateBudget($program->id, $class->type_id, $current_financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, null, $area->office_id, true);
                            // \Log::debug(json_encode($budget_value));
                            $budgetDetail[] = $this->prepareBudgetDetail($budget, $budget_value, $area, $class->type_id, $area->office_id);
                        }
                    }else{
                        $budget_value = $this->calculateBudget($program->id, null, $current_financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, null, $area->office_id, true);
                        $budgetDetail[] = $this->prepareBudgetDetail($budget, $budget_value, $area, null, $area->office_id);
                    }
                } else {
                    $location = [
                        'division_id' => $area->division_id,
                        'district_id' => $area->district_id,
                        'location_type' => $area->location_type,
                        'city_corp_id' => $area->city_corp_id,
                        'upazila_id' => $area->upazila_id,
                        'district_pourashava_id' => $area->district_pourashava_id,
                        'thana_id' => $area->thana_id,
                        'pourashava_id' => $area->pourashava_id,
                        'union_id' => $area->union_id,
                        'ward_id' => $area->ward_id,
                    ];

                    $budget_value = $this->calculateBudget($program->id, null, $current_financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $location, null, false);
                    $budgetDetail[] = $this->prepareBudgetDetail($budget, $budget_value, $area);
                }
            }
            // Log::debug(json_encode($budgetDetail));

            $budget->process_flag = 1;
            $budget->save();

            if (!empty($budgetDetail)) {
                $budget->budgetDetail()->insert($budgetDetail); // Bulk insert
            }

            DB::commit();
            return true;
        } catch (Throwable $throwable) {
            DB::rollBack();
            error('Processing budget failed!! ' . $throwable->getMessage());

            $budget->update([
                'approval_status' => 'Failed',
                'process_flag' => -1
            ]);

            throw $throwable;
        }
    }

    /**
     * Prepare budget detail array for insertion.
     */
    private function prepareBudgetDetail(Budget $budget, array $budget_value, $area, $type_id = null, $office_id = null)
    {
        return [
            'budget_id' => $budget->id,
            'total_beneficiaries' => $budget_value['current_total_beneficiary'],
            'total_amount' => $budget_value['current_total_amount'],
            'division_id' => $area->division_id,
            'district_id' => $area->district_id,
            'location_type' => $area->location_type,
            'city_corp_id' => $area->city_corp_id,
            'upazila_id' => $area->upazila_id,
            'district_pourashava_id' => $area->district_pourashava_id,
            'thana_id' => $area->thana_id??null,
            'pourashava_id' => $area->pourashava_id??null,
            'union_id' => $area->union_id??null,
            'ward_id' => $area->ward_id??null,
            'location_id' => $area->locatoin_id,
            'type_id' => $type_id,
            'office_id' => $office_id,
            'created_at' => now(),
        ];
    }


    /**
     * @param Budget $budget
     * @return \Exception|Throwable|true
     * @throws Throwable
     */
    public function processBudget2(Budget $budget)
    {
        DB::beginTransaction();
        try {
            info('Processing budget: ' . $budget->budget_id);
            $budget->process_flag = 1;
            $budget->save();
            DB::commit();
        } catch (\Throwable $throwable) {
            DB::rollBack();
            // set status fail
            error('Processing budget failed!!');
            $budget->approval_status = 'Failed';
            $budget->process_flag = -1;
            $budget->save();
            return $throwable;
        }
        return true;
    }

    /**
     * @param int $program_id
     * @param int $financial_year_id
     * @param string $calculation_type
     * @param float $calculation_value
     * @param array $previous_financial_year_ids
     * @param array $location
     * @return int[]
     */
    // public function calculateBudget(int $program_id, int|null $class_id, int $financial_year_id, string $calculation_type, float $calculation_value, array $previous_financial_year_ids, array $location = array()): array
    // {
    //     // initialize
    //     $data = [
    //         'previous_total_beneficiary' => 0,
    //         'previous_total_amount' => 0,
    //         'current_total_beneficiary' => 0,
    //         'current_total_amount' => 0,
    //     ];
    //     $program = AllowanceProgram::query()->findOrFail($program_id);
    //     $query = DB::table('allotments')
    //         ->where('program_id', $program_id)
    //         ->whereIn('financial_year_id', $previous_financial_year_ids)
    //         ->when($class_id != null, function ($query) use ($class_id) {
    //             $query->where('type_id', $class_id);
    //         });
    //     $beneficiaryQuery = DB::table('beneficiaries')
    //         ->where('status', 1) // only active beneficiaries
    //         ->where('program_id', $program_id)
    //         ->when($class_id != null, function ($query) use ($class_id) {
    //             $query->where('type_id', $class_id);
    //         });
    //     $allotmentAreaQuery = $class_id == null ? DB::table('allotment_areas_view') : DB::table('allotment_areas_1st_level_view');
    //     if (count($location) > 0) {
    //         if (isset($location['division_id']) && $location['division_id'] != null) {
    //             $query = $query->where('division_id', $location['division_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_division_id', $location['division_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('division_id', $location['division_id']);
    //         }
    //         if (isset($location['district_id']) && $location['district_id'] != null) {
    //             $query = $query->where('district_id', $location['district_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_district_id', $location['district_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('district_id', $location['district_id']);
    //         }
    //         if (isset($location['city_corp_id']) && $location['city_corp_id'] != null) {
    //             $query = $query->where('city_corp_id', $location['city_corp_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_city_corp_id', $location['city_corp_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('city_corp_id', $location['city_corp_id']);
    //         }
    //         if (isset($location['district_pourashava_id']) && $location['district_pourashava_id'] != null) {
    //             $query = $query->where('district_pourashava_id', $location['district_pourashava_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_district_pourashava_id', $location['district_pourashava_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('district_pourashava_id', $location['district_pourashava_id']);
    //         }
    //         if (isset($location['upazila_id']) && $location['upazila_id'] != null) {
    //             $query = $query->where('upazila_id', $location['upazila_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_upazila_id', $location['upazila_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('upazila_id', $location['upazila_id']);
    //         }
    //         if (isset($location['pourashava_id']) && $location['pourashava_id'] != null) {
    //             $query = $query->where('pourashava_id', $location['pourashava_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_pourashava_id', $location['pourashava_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('pourashava_id', $location['pourashava_id']);
    //         }
    //         if (isset($location['thana_id']) && $location['thana_id'] != null) {
    //             $query = $query->where('thana_id', $location['thana_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_thana_id', $location['thana_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('thana_id', $location['thana_id']);
    //         }
    //         if (isset($location['union_id']) && $location['union_id'] != null) {
    //             $query = $query->where('union_id', $location['union_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_union_id', $location['union_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('union_id', $location['union_id']);
    //         }
    //         if (isset($location['ward_id']) && $location['ward_id'] != null) {
    //             $query = $query->where('ward_id', $location['ward_id']);
    //             $beneficiaryQuery = $beneficiaryQuery->where('permanent_ward_id', $location['ward_id']);
    //             $allotmentAreaQuery = $allotmentAreaQuery->where('ward_id', $location['ward_id']);
    //         }
    //     }
    //     $allotmentAreaCount = $allotmentAreaQuery->count();
    //     $previousBudgetResult = $query->selectRaw('sum(total_beneficiaries) as total_beneficiaries, sum(total_amount) as total_amount, count(distinct financial_year_id) as number_of_previous_years')->first();
    //     $number_of_previous_years = $previousBudgetResult?->number_of_previous_years ?: 0;
    //     $previous_total_beneficiary = $previousBudgetResult?->total_beneficiaries && $number_of_previous_years > 0 ? ceil($previousBudgetResult?->total_beneficiaries / $number_of_previous_years) : 0;
    //     $previous_total_amount = $previousBudgetResult?->total_amount && $number_of_previous_years > 0 ? ceil($previousBudgetResult?->total_amount / $number_of_previous_years) : 0;
    //     $per_beneficiary_amount = $previous_total_beneficiary > 0 ? ceil($previous_total_amount / $previous_total_beneficiary) : 0;
    //     if ($per_beneficiary_amount <= 0) {
    //         if ($program->is_age_limit)
    //             $per_beneficiary_amount = AllowanceProgramAge::query()->where('allowance_program_id', $program_id)->max('amount');
    //         else
    //             $per_beneficiary_amount = AllowanceProgramAmount::query()->where('allowance_program_id', $program_id)->max('amount');
    //     }
    //     switch ($calculation_type) {
    //         case "PERCENTAGE_OF_AMOUNT":
    //             $extra_amount = $previous_total_amount * ($calculation_value / 100);
    //             $current_total_amount = $extra_amount + $previous_total_amount;
    //             $extra_beneficiaries = $per_beneficiary_amount > 0 ? floor($extra_amount / $per_beneficiary_amount) : 0;
    //             $current_total_beneficiary = $previous_total_beneficiary + $extra_beneficiaries;
    //             break;
    //         case "FIXED_AMOUNT":
    //             $extra_amount = $allotmentAreaCount * $calculation_value;
    //             $current_total_amount = $previous_total_amount + $extra_amount;
    //             $extra_beneficiaries = $per_beneficiary_amount > 0 ? floor($extra_amount / $per_beneficiary_amount) : 0;
    //             $current_total_beneficiary = $previous_total_beneficiary + $extra_beneficiaries;
    //             break;
    //         case "BY_POPULATION_PERCENTAGE":
    //         case "BY_POVERTY_SCORE_PERCENTAGE":
    //         case "BY_APPLICATION_PERCENTAGE":
    //         case "PERCENTAGE_OF_BENEFICIARY":
    //             $extra_beneficiaries = $previous_total_beneficiary * ($calculation_value / 100);
    //             $current_total_beneficiary = $previous_total_beneficiary + $extra_beneficiaries;
    //             $extra_amount = $extra_beneficiaries * $per_beneficiary_amount;
    //             $current_total_amount = $previous_total_amount + $extra_amount;
    //             break;
    //         case "BY_POPULATION":
    //         case "BY_POVERTY_SCORE":
    //         case "BY_APPLICATION":
    //         case "FIXED_BENEFICIARY":
    //             $extra_beneficiaries = $allotmentAreaCount * $calculation_value;
    //             if ($previous_total_beneficiary <= 0) {
    //                 $current_total_beneficiary = $extra_beneficiaries;
    //                 $current_total_amount = $current_total_beneficiary * $per_beneficiary_amount;
    //             } else {
    //                 $current_total_beneficiary = $previous_total_beneficiary + $extra_beneficiaries;
    //                 $extra_amount = $extra_beneficiaries * $per_beneficiary_amount;
    //                 $current_total_amount = $previous_total_amount + $extra_amount;
    //             }
    //             break;
    //         default:
    //             $current_total_beneficiary = $previous_total_beneficiary;
    //             $current_total_amount = $previous_total_amount;
    //             break;
    //     }
    //     $data["previous_total_beneficiary"] = $previous_total_beneficiary;
    //     $data["previous_total_amount"] = $previous_total_amount;
    //     $data["current_total_beneficiary"] = $current_total_beneficiary;
    //     $data["current_total_amount"] = $current_total_amount;

    //     return $data;
    // }
    public function calculateBudget(
        int $program_id,
        ?int $class_id,
        int $financial_year_id,
        string $calculation_type,
        float $calculation_value,
        array $previous_financial_year_ids,
        array|null $location = [],
        int|null $office_id,
        bool $is_office_wise = false
    ): array {
        // Initialize result data
        $data = [
            'previous_total_beneficiary' => 0,
            'previous_total_amount' => 0,
            'current_total_beneficiary' => 0,
            'current_total_amount' => 0,
        ];
    
        $program = AllowanceProgram::findOrFail($program_id);
        
        // Base queries
        $query = DB::table('allotments')->where('program_id', $program_id)->whereIn('financial_year_id', $previous_financial_year_ids);
        $beneficiaryQuery = DB::table('beneficiaries')->where('status', 1)->where('program_id', $program_id);
        $allotmentAreaQuery = !$is_office_wise ? DB::table('allotment_areas_view') : DB::table('allotment_areas_1st_level_view');
        
        // Apply class filter if needed
        if ($class_id) {
            $query->where('type_id', $class_id);
            $beneficiaryQuery->where('type_id', $class_id);
        }
        
        // Apply location filters dynamically
        $locationFields = [
            'division_id', 'district_id', 'city_corp_id', 'district_pourashava_id', 
            'upazila_id', 'pourashava_id', 'thana_id', 'union_id', 'ward_id'
        ];

        // \Log::info(json_encode($location));
        
        if($location){
            foreach ($locationFields as $field) {
                if (!empty($location[$field])) {
                    $query->where($field, $location[$field]);
                    $beneficiaryQuery->where("permanent_{$field}", $location[$field]);
                    $allotmentAreaQuery->where($field, $location[$field]);
                }
            }
        }
        if($office_id){
            $wardIds = OfficeHasWard::where('office_id', $office_id)->pluck('ward_id');
            // $query->whereIn('ward_id', $wardIds)->orWhere('office_id', $office_id);
            $query->where('office_id', $office_id);
            $beneficiaryQuery->whereIn("permanent_ward_id", $wardIds);
            $allotmentAreaQuery->where('office_id', $office_id);
        }
        
        // Fetch data
        $allotmentAreaCount = $allotmentAreaQuery->count();
        $previousBudget = $query->selectRaw(
            'SUM(total_beneficiaries) as total_beneficiaries, SUM(total_amount) as total_amount, COUNT(DISTINCT financial_year_id) as years'
        )->first();
    
        $number_of_previous_years = max($previousBudget->years, 1);
        $previous_total_beneficiary = ceil(($previousBudget->total_beneficiaries ?? 0) / $number_of_previous_years);
        $previous_total_amount = ceil(($previousBudget->total_amount ?? 0) / $number_of_previous_years);
        $per_beneficiary_amount = $previous_total_beneficiary > 0 ? ceil($previous_total_amount / $previous_total_beneficiary) : 0;
    
        // If no valid per_beneficiary_amount, fetch from program settings
        if ($per_beneficiary_amount <= 0) {
            $per_beneficiary_amount = $program->is_age_limit
                ? AllowanceProgramAge::where('allowance_program_id', $program_id)->max('amount')
                : AllowanceProgramAmount::where('allowance_program_id', $program_id)->max('amount');
            $multiplyBy = 1;
            if($program->payment_cycle == 'Quarterly'){
                $multiplyBy = 4;
            }elseif($program->payment_cycle == 'Monthly'){
                $multiplyBy = 12;
            }elseif($program->payment_cycle == 'Half Yearly'){
                $multiplyBy = 2;
            }elseif($program->payment_cycle == 'Yearly'){
                $multiplyBy = 1;
            }
            $per_beneficiary_amount *= $multiplyBy;
        }
        
        // Calculate new values
        switch ($calculation_type) {
            case "PERCENTAGE_OF_AMOUNT":
                $extra_amount = $previous_total_amount * ($calculation_value / 100);
                break;
            case "FIXED_AMOUNT":
                $extra_amount = $allotmentAreaCount * $calculation_value;
                break;
            case "PERCENTAGE_OF_BENEFICIARY":
            case "BY_POPULATION_PERCENTAGE":
            case "BY_POVERTY_SCORE_PERCENTAGE":
            case "BY_APPLICATION_PERCENTAGE":
                $extra_beneficiaries = ceil($previous_total_beneficiary * ($calculation_value / 100));
                $extra_amount = $extra_beneficiaries * $per_beneficiary_amount;
                break;
            case "FIXED_BENEFICIARY":
            case "BY_POPULATION":
            case "BY_POVERTY_SCORE":
            case "BY_APPLICATION":
                $extra_beneficiaries = $allotmentAreaCount * $calculation_value;
                $extra_amount = $extra_beneficiaries * $per_beneficiary_amount;
                break;
            default:
                $extra_amount = 0;
                $extra_beneficiaries = 0;
        }
    
        // Compute final results
        $data["previous_total_beneficiary"] = $previous_total_beneficiary;
        $data["previous_total_amount"] = $previous_total_amount;
        $data["current_total_beneficiary"] = $previous_total_beneficiary + ($extra_beneficiaries ?? 0);
        $data["current_total_amount"] = $previous_total_amount + $extra_amount;
    
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
        $sub_program_id = $request->query('sub_program_id');
        $financial_year_id = $request->query('financial_year_id');
        $perPage = $request->query('perPage', 10);
        $sortByColumn = $request->query('sortBy', 'created_at');
        $orderByDirection = $request->query('orderBy', 'asc');

        $query = Budget::query();
        if ($sub_program_id){
            $query = $query->where('program_id', $sub_program_id);
        }elseif ($program_id){
            $subs = AllowanceProgram::where('parent_id', $program_id)->pluck('id');
            if(count($subs)){
                $query = $query->whereIn('program_id', $subs);
            }else{
                $query = $query->where('program_id', $program_id);
            }
        }

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
     * @param $budget_id
     * @param $location_id
     * @return \Illuminate\Support\Collection
     */
    // public function getDetailBudget($budget_id, $location_id = null)
    // {
    //     $budget = Budget::query()->findOrFail($budget_id);
    //     $program_id = $budget->program_id;
    //     $program = AllowanceProgram::query()->findOrFail($program_id);
    //     $program_has_class = $program?->is_disable_class;
    //     $classes = $program_has_class ? AllowanceProgramAmount::query()->where('allowance_program_id', $program_id)->get() : null;

    //     $query = BudgetDetail::query()
    //         ->leftJoin('lookups as classes', 'classes.id', '=', 'budget_details.type_id');


    //     $location = $location_id != null ? Location::find($location_id) : null;


    //     if ($location == null) {
    //         $query = $query->join('locations', 'locations.id', '=', 'budget_details.division_id');
    //     } elseif ($location?->type == 'division') {
    //         $query = $query->join('locations', 'locations.id', '=', 'budget_details.district_id');
    //     } elseif ($location?->type == 'district') {
    //         $query = $query->join('locations', function ($join) {
    //             return $join->on('locations.id', '=', 'budget_details.upazila_id')
    //                 ->orOn('locations.id', '=', 'budget_details.city_corp_id')
    //                 ->orOn('locations.id', '=', 'budget_details.district_pourashava_id');
    //         });
    //     } elseif ($location?->type == 'city') {
    //         if ($location?->location_type == 1) {
    //             $query = $query->join('locations', 'locations.id', '=', 'budget_details.ward_id');
    //         } elseif ($location?->location_type == 3)
    //             $query = $query->join('locations', 'locations.id', '=', 'budget_details.thana_id');
    //     } elseif ($location?->type == 'thana') {
    //         if ($location?->location_type == 2) {
    //             $query = $query->join('locations', function ($join) {
    //                 return $join->on('locations.id', '=', 'budget_details.union_id')
    //                     ->orOn('locations.id', '=', 'budget_details.pourashava_id');
    //             });
    //         } elseif ($location?->parent?->location_type == 3) {
    //             $query = $query->join('locations', 'locations.id', '=', 'budget_details.ward_id');
    //         }
    //     } elseif ($location?->type == 'union' || $location?->type == 'pouro') {
    //         if ($location?->type == 'union')
    //             $query = $query->join('locations', 'locations.id', '=', 'budget_details.union_id');
    //         elseif ($location?->type == 'pouro')
    //             $query = $query->join('locations', 'locations.id', '=', 'budget_details.pourashava_id');
    //     } elseif ($location?->type == 'ward') {
    //         $query = $query->join('locations', 'locations.id', '=', 'budget_details.ward_id');
    //     }
    //     $query = $query->leftJoin('locations as parent', 'parent.id', '=', 'locations.parent_id');

    //     $query = $query->where('budget_details.budget_id', $budget_id);
    //     if ($location?->id)
    //         $query = $query->where('locations.parent_id', $location?->id);
    //     $query = $query->selectRaw('locations.id, parent.parent_id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en as class_name_en, classes.value_bn as class_name_bn, sum(budget_details.total_beneficiaries) as total_beneficiaries, sum(budget_details.total_amount) as total_amount');
    //     $query = $query->groupByRaw('locations.id, parent.parent_id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en, classes.value_bn')
    //         ->orderByRaw('locations.name_en, classes.value_en');
    //     $data = $query->get();
    //     return $data->map(function ($item) use ($program_has_class) {
    //         $location = Location::query()->find($item->id);
    //         if ($program_has_class) {
    //             if ($location?->type == 'city')
    //                 $is_allotment_area = $program_has_class;
    //             elseif ($location?->type == 'thana' && $location?->location_type == 2)
    //                 $is_allotment_area = $program_has_class;
    //             else
    //                 $is_allotment_area = false;
    //         } else {
    //             if ($location?->type == 'ward')
    //                 $is_allotment_area = $location?->location_type == 1 || $location?->location_type == 3;
    //             elseif ($location?->type == 'union' || $location?->type == 'pouro')
    //                 $is_allotment_area = $location?->parent?->location_type == 2;
    //             else
    //                 $is_allotment_area = false;
    //         }

    //         $item->is_allotment_area = $is_allotment_area;
    //         return $item;
    //     });
    // }

    public function getDetailBudget($budget_id, $location_id = null)
    {
        $budget = Budget::findOrFail($budget_id);
        $program = AllowanceProgram::findOrFail($budget->program_id);
        $program_has_class = $program->is_disable_class ?? false;
        $program_has_office_wise_budget = (bool) $program?->is_office_wise_budget;
        
        $query = BudgetDetail::query()
            ->leftJoin('lookups as classes', 'classes.id', '=', 'budget_details.type_id')
            ->where('budget_details.budget_id', $budget_id);;
        
        $location = $location_id ? Location::find($location_id) : null;

        if($location){
            $is_allotment_area = $this->determineAllotmentArea($location, $program_has_office_wise_budget || $this->determineOfficeWise($location, $program));
            if($is_allotment_area){
                $query->where('budget_details.location_id', $location->id);
                $query->leftJoin('offices', 'offices.id', '=', 'budget_details.office_id');
                $query->leftJoin('locations as l1', 'l1.id', '=', 'budget_details.location_id');

                $query->selectRaw('
                    offices.id, l1.parent_id, offices.name_en, offices.name_bn, classes.value_en as class_name_en,
                    classes.value_bn as class_name_bn, sum(budget_details.total_beneficiaries) as total_beneficiaries,
                    sum(budget_details.total_amount) as total_amount
                ')
                ->groupByRaw('offices.id, offices.name_en, offices.name_bn, classes.value_en, classes.value_bn')
                ->orderByRaw('offices.name_en, classes.value_en');
                // $query->join('locations', 'locations.id', '=', 'budget_details.location_id');
                // $query->selectRaw('
                //     locations.id, offices.name_en, offices.name_bn,
                //     locations.type, locations.location_type, classes.value_en as class_name_en,
                //     classes.value_bn as class_name_bn, sum(budget_details.total_beneficiaries) as total_beneficiaries,
                //     sum(budget_details.total_amount) as total_amount
                // ')
                // ->groupByRaw('locations.id, offices.name_en, offices.name_bn, locations.type, locations.location_type, classes.value_en, classes.value_bn')
                // ->orderByRaw('offices.name_en, classes.value_en');

                Log::info($query->toRawSql());

                return $query->get()->map(function ($item) use ($is_allotment_area) {
                    
                    $item->is_allotment_area = $is_allotment_area;
                    $item->has_allotment_office = false;
                    return $item;
                });
            }
        }
        
        // Determine the appropriate join based on location type
        if (!$location) {
            $query->join('locations', 'locations.id', '=', 'budget_details.division_id');
        } else {
            $joins = [
                'division' => 'budget_details.district_id',
                'district' => function ($join) {
                    $join->on('locations.id', '=', 'budget_details.upazila_id')
                        ->orOn('locations.id', '=', 'budget_details.city_corp_id')
                        ->orOn('locations.id', '=', 'budget_details.district_pourashava_id');
                },
                'city' => $location->location_type == 1 ? 'budget_details.ward_id' : 'budget_details.thana_id',
                'thana' => $location->location_type == 2 ? function ($join) {
                    $join->on('locations.id', '=', 'budget_details.union_id')
                        ->orOn('locations.id', '=', 'budget_details.pourashava_id');
                } : 'budget_details.ward_id',
                'union' => 'budget_details.union_id',
                'pouro' => 'budget_details.pourashava_id',
                'ward' => 'budget_details.ward_id',
            ];
            
            if (isset($joins[$location->type])) {
                $query->join('locations', is_callable($joins[$location->type]) ? $joins[$location->type] : 'locations.id', '=', $joins[$location->type]);
            }
        }

        $query->leftJoin('locations as parent', 'parent.id', '=', 'locations.parent_id')
            ->where('budget_details.budget_id', $budget_id);
        
        if ($location?->id) {
            $query->where('locations.parent_id', $location->id);
        }
        
        $query->selectRaw('
            locations.id, parent.parent_id, locations.name_en, locations.name_bn,
            locations.type, locations.location_type, classes.value_en as class_name_en,
            classes.value_bn as class_name_bn, sum(budget_details.total_beneficiaries) as total_beneficiaries,
            sum(budget_details.total_amount) as total_amount
        ')
        ->groupByRaw('locations.id, parent.parent_id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en, classes.value_bn')
        ->orderByRaw('locations.name_en, classes.value_en');

        // return $query->toRawSql();
        
        return $query->get()->map(function ($item) use ($program, $program_has_office_wise_budget) {
            $location = Location::find($item->id);
            $pho = $program_has_office_wise_budget || $this->determineOfficeWise($location, $program);
            
            $item->is_allotment_area = $this->determineAllotmentArea($location, $program_has_office_wise_budget);
            $item->has_allotment_office = $pho && $item->is_allotment_area;
            return $item;
        });
    }

    public function getDetailBudget1($budget_id, $location_id = null)
    {
        $budget = Budget::findOrFail($budget_id);
        $program = AllowanceProgram::findOrFail($budget->program_id);
        $program_has_class = $program->is_disable_class;
        $classes = $program_has_class ? AllowanceProgramAmount::where('allowance_program_id', $program->id)->get() : null;

        $query = BudgetDetail::query()
            ->leftJoin('lookups as classes', 'classes.id', '=', 'budget_details.type_id');

        $location = $location_id ? Location::find($location_id) : null;

        if (!$location) {
            $query->join('locations', 'locations.id', '=', 'budget_details.division_id');
        } else {
            $locationType = $location->type;
            $locationTypeMap = [
                'division' => 'district_id',
                'district' => function ($join) {
                    $join->on('locations.id', '=', 'budget_details.upazila_id')
                        ->orOn('locations.id', '=', 'budget_details.city_corp_id')
                        ->orOn('locations.id', '=', 'budget_details.district_pourashava_id');
                },
                'city' => $location->location_type == 1 ? 'ward_id' : ($location->location_type == 3 ? 'thana_id' : null),
                'thana' => $location->location_type == 2 ? function ($join) {
                    $join->on('locations.id', '=', 'budget_details.union_id')
                        ->orOn('locations.id', '=', 'budget_details.pourashava_id');
                } : ($location->parent->location_type == 3 ? 'ward_id' : null),
                'union' => 'union_id',
                'pouro' => 'pourashava_id',
                'ward' => 'ward_id'
            ];
            
            $joinCondition = $locationTypeMap[$locationType] ?? null;
            if ($joinCondition) {
                $query->join('locations', is_callable($joinCondition) ? $joinCondition : fn($join) => $join->on('locations.id', '=', "budget_details.$joinCondition"));
            }
        }
        
        $query->leftJoin('locations as parent', 'parent.id', '=', 'locations.parent_id')
            ->where('budget_details.budget_id', $budget_id);
        
        if ($location?->id) {
            $query->where('locations.parent_id', $location->id);
        }
        
        $query->selectRaw('locations.id, parent.parent_id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en as class_name_en, classes.value_bn as class_name_bn, sum(budget_details.total_beneficiaries) as total_beneficiaries, sum(budget_details.total_amount) as total_amount')
            ->groupByRaw('locations.id, parent.parent_id, locations.name_en, locations.name_bn, locations.type, locations.location_type, classes.value_en, classes.value_bn')
            ->orderByRaw('locations.name_en, classes.value_en');
        
        return $query->get()->map(function ($item) use ($program_has_class) {
            $location = Location::find($item->id);
            $is_allotment_area = match (true) {
                $program_has_class && in_array($location->type, ['city', 'thana']) && $location->location_type == 2 => true,
                !$program_has_class && ($location->type == 'ward' && in_array($location->location_type, [1, 3])) => true,
                !$program_has_class && in_array($location->type, ['union', 'pouro']) && $location->parent?->location_type == 2 => true,
                default => false
            };
            $item->is_allotment_area = $is_allotment_area;
            return $item;
        });
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
        $updated_by_id = auth()->user()->id;
        $validated = $request->safe()->merge(['updated_by_id' => $updated_by_id])->only(['calculation_type', 'no_of_previous_year', 'calculation_value', 'remarks']);
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
        $budget->approved_by_id = auth()->user()->id;
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
            $budgetDeleted = $budget->replicate();
            $budget->budgetDetail()->forceDelete();
            $budget->forceDelete();
            Helper::activityLogDelete($budgetDeleted, '', 'Budget', 'Budget Deleted!!');
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
            info('Approving budget: ' . $budget->budget_id);
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
                total_amount,
                type_id,
                office_id
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
                d.total_amount,
                d.type_id,
                d.office_id
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
        } catch (Throwable $throwable) {
            DB::rollBack();
            // set status fail
            error($throwable->getMessage());
            error('Budget approve failed!!');
            $budget->is_approved = false;
            $budget->approval_status = 'Draft';
            $budget->approved_by_id = null;
            $budget->approved_at = null;
            $budget->save();
            throw $throwable;
        }
    }

    /**
     * @param Request $request
     * @param $program_id
     * @param $financial_year_id
     * @return array
     */
    public function getProjection1(Request $request)
    {
        $program_id = $request->query('program_id');
        $financial_year_id = $this->currentFinancialYear()?->id;// $request->query('financial_year_id');
        $calculation_type = $request->has('calculation_type') ? Lookup::query()->find($request->query('calculation_type'))?->keyword : "";
        $calculation_value = $request->query('calculation_value');
        $previous_financial_year_ids = $request->has('prev_financial_year_ids') ? $request->get('prev_financial_year_ids') : [];
//        dump($calculation_type);
        $locations = Location::query()->where(function ($query) use ($request) {
            if ($request->has('location_id')) {
                $location_id = $request->query('location_id');
                $query->where('parent_id', $location_id);
            } else {
                $query->whereNull('parent_id');
            }
            return $query;
        })->with('parent')->get();
        $program = AllowanceProgram::query()->findOrFail($program_id);
        $program_has_class = (bool)$program?->is_disable_class;
        $classes = $program_has_class ? AllowanceProgramAmount::query()->where('allowance_program_id', $program_id)->get() : null;

        $locations->map(function ($location) use ($program_id, $program_has_class, $classes, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids) {
            $is_allotment_area = false;
            $budgetLocation = [];
            if ($location?->type == 'ward') {
                $is_allotment_area = ($location?->location_type == 1 || $location?->location_type == 3) && !$program_has_class;
                $budgetLocation['ward_id'] = $location->id;
            } elseif ($location?->type == 'union' || $location?->type == 'pouro') {
                $is_allotment_area = $location?->parent?->location_type == 2 && !$program_has_class;
                if ($location?->type == 'union')
                    $budgetLocation['union_id'] = $location->id;
                elseif ($location?->type == 'pouro')
                    $budgetLocation['pourashava_id'] = $location->id;
            } elseif ($location?->type == 'thana') {
                if ($location?->location_type == 2) {
                    $is_allotment_area = $program_has_class;
                    $budgetLocation['upazila_id'] = $location->id;
                } elseif ($location?->parent?->location_type == 3) {
                    $budgetLocation['thana_id'] = $location->id;
                }
            } elseif ($location?->type == 'city') {
                $is_allotment_area = $program_has_class;
                if ($location?->location_type == 1)
                    $budgetLocation['district_pourashava_id'] = $location->id;
                elseif ($location?->location_type == 3)
                    $budgetLocation['city_corp_id'] = $location->id;
            } elseif ($location?->type == 'district') {
                $budgetLocation['district_id'] = $location->id;
            } elseif ($location?->type == 'division')
                $budgetLocation['division_id'] = $location->id;

            if ($program_has_class) {
                $class_wise_budgets = collect();
                foreach ($classes as $k => $class) {
                    $budget_value = $this->calculateBudget($program_id, $class->type_id, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $budgetLocation);
                    $tmp = collect();
                    $tmp->put('class_id', $class->type->id);
                    $tmp->put('class_name_en', $class->type->value_en);
                    $tmp->put('class_name_bn', $class->type->value_bn);
                    $tmp->put('previous_total_beneficiary', $budget_value['previous_total_beneficiary']);
                    $tmp->put('previous_total_amount', $budget_value['previous_total_amount']);
                    $tmp->put('current_total_beneficiary', $budget_value['current_total_beneficiary']);
                    $tmp->put('current_total_amount', $budget_value['current_total_amount']);
                    $class_wise_budgets->put($k, $tmp);
                }
                $location->class_wise_budgets = $class_wise_budgets;
            } else {
                $budget_value = $this->calculateBudget($program_id, null, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $budgetLocation);
                $location->previous_total_beneficiary = $budget_value['previous_total_beneficiary'];
                $location->previous_total_amount = $budget_value['previous_total_amount'];
                $location->current_total_beneficiary = $budget_value['current_total_beneficiary'];
                $location->current_total_amount = $budget_value['current_total_amount'];
            }

            $location->program_has_class = $program_has_class;
            $location->is_allotment_area = $is_allotment_area;
        });

        return $locations;
    }

    public function distPauroIsOfficeWise(AllowanceProgram $allowanceProgram): bool{
        return in_array($allowanceProgram->id, [1,2,3]);
    }

    public function determineOfficeWise(?Location $location, AllowanceProgram $allowanceProgram): bool{
        return $location?->location_type == 1 && $this->distPauroIsOfficeWise($allowanceProgram);
    }
    public function getProjection(Request $request)
{
    $program_id = $request->query('program_id');
    $financial_year_id = $this->currentFinancialYear()?->id;
    $calculation_type = $request->has('calculation_type') ? Lookup::query()->find($request->query('calculation_type'))?->keyword : "";
    $calculation_value = $request->query('calculation_value');
    $previous_financial_year_ids = $request->has('prev_financial_year_ids') ? $request->get('prev_financial_year_ids') : [];
    
    $program = AllowanceProgram::query()->findOrFail($program_id);
    $program_has_class = (bool) $program?->is_disable_class;
    $program_has_office_wise_budget = (bool) $program?->is_office_wise_budget;
    $classes = $program_has_class ? AllowanceProgramAmount::query()->where('allowance_program_id', $program_id)->get() : collect();

    if($request->has('location_id')){
        $location = Location::find($request->location_id);
        $isOfficeWiseBudget = $program_has_office_wise_budget || $this->determineOfficeWise($location, $program);
        $is_allotment_area = $this->determineAllotmentArea($location, $isOfficeWiseBudget);
        
        if($is_allotment_area){
            $offices = $location->offices()->with('assignLocation')->get();
            $offices->map(function ($office) use ($program_id, $program_has_class, $classes, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $isOfficeWiseBudget, $is_allotment_area) {
                // $budgetLocation = $this->getBudgetLocation($office);
                // $is_allotment_area = $this->determineAllotmentArea($office, $program_has_office_wise_budget);
                
                if ($program_has_class) {
                    // Log::info($office->id);
                    $office->class_wise_budgets = $classes->map(function ($class) use ($program_id, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $office) {
                        $budget_value = $this->calculateBudget($program_id, $class->type_id, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, null, $office->id, true);
                        return [
                            'class_id' => $class->type->id,
                            'class_name_en' => $class->type->value_en,
                            'class_name_bn' => $class->type->value_bn,
                            'previous_total_beneficiary' => $budget_value['previous_total_beneficiary'],
                            'previous_total_amount' => $budget_value['previous_total_amount'],
                            'current_total_beneficiary' => $budget_value['current_total_beneficiary'],
                            'current_total_amount' => $budget_value['current_total_amount']
                        ];
                    });
                } else {
                    $budget_value = $this->calculateBudget($program_id, null, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, null, $office->id, $isOfficeWiseBudget);
                    $office->previous_total_beneficiary = $budget_value['previous_total_beneficiary'];
                    $office->previous_total_amount = $budget_value['previous_total_amount'];
                    $office->current_total_beneficiary = $budget_value['current_total_beneficiary'];
                    $office->current_total_amount = $budget_value['current_total_amount'];
                }
                
                $office->program_has_class = $program_has_class;
                $office->is_allotment_area = $is_allotment_area;
                $office->has_allotment_office = false;
            });
            return $offices;
        }
    }
    
    $locations = Location::query()->where(fn($query) => $request->has('location_id')
    ? $query->where('parent_id', $request->query('location_id'))
    : $query->whereNull('parent_id')
    )->with('parent')->get();
    
    $locations->map(function ($location) use ($program ,$program_id, $program_has_class, $classes, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $program_has_office_wise_budget) {
        $budgetLocation = $this->getBudgetLocation($location);
        $pho = $program_has_office_wise_budget || $this->determineOfficeWise($location, $program);
        $is_allotment_area = $this->determineAllotmentArea($location, $pho);
        
        if ($program_has_class) {
            $location->class_wise_budgets = $classes->map(function ($class) use ($program_id, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $budgetLocation, $pho) {
                $budget_value = $this->calculateBudget($program_id, $class->type_id, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $budgetLocation, null, $pho);
                return [
                    'class_id' => $class->type->id,
                    'class_name_en' => $class->type->value_en,
                    'class_name_bn' => $class->type->value_bn,
                    'previous_total_beneficiary' => $budget_value['previous_total_beneficiary'],
                    'previous_total_amount' => $budget_value['previous_total_amount'],
                    'current_total_beneficiary' => $budget_value['current_total_beneficiary'],
                    'current_total_amount' => $budget_value['current_total_amount']
                ];
            });
        } else {
            // Log::info('hello');
            $officeId = null;
            if($pho){
                $officeId = $location->offices()->first()?->id;
            }
            $budget_value = $this->calculateBudget($program_id, null, $financial_year_id, $calculation_type, $calculation_value, $previous_financial_year_ids, $officeId== null? $budgetLocation : null, $officeId, $pho);
            $location->previous_total_beneficiary = $budget_value['previous_total_beneficiary'];
            $location->previous_total_amount = $budget_value['previous_total_amount'];
            $location->current_total_beneficiary = $budget_value['current_total_beneficiary'];
            $location->current_total_amount = $budget_value['current_total_amount'];
        }
        
        $location->program_has_class = $program_has_class;
        $location->is_allotment_area = $is_allotment_area;
        $location->has_allotment_office = $pho && $is_allotment_area;
    });

    return $locations;
}

private function getBudgetLocation($location): array
{
    return match ($location->type) {
        'ward' => ['ward_id' => $location->id],
        'union' => ['union_id' => $location->id],
        'pouro' => ['pourashava_id' => $location->id],
        'thana' => ($location->location_type == 2) ? ['upazila_id' => $location->id] : ['thana_id' => $location->id],
        'city' => ($location->location_type == 1) ? ['district_pourashava_id' => $location->id] : ['city_corp_id' => $location->id],
        'district' => ['district_id' => $location->id],
        'division' => ['division_id' => $location->id],
        default => []
    };
}

private function determineAllotmentArea($location, $program_has_office_wise_budget): bool
{
    return match ($location->type) {
        'ward' => in_array($location->location_type, [1, 3]) && !$program_has_office_wise_budget,
        'union', 'pouro' => $location->parent?->location_type == 2 && !$program_has_office_wise_budget,
        'thana' => $location->location_type == 2 && $program_has_office_wise_budget,
        'city' => $program_has_office_wise_budget,
        default => false
    };
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
            return $query->with('budget', 'upazila', 'cityCorporation', 'districtPourosova', 'location', 'office', 'type')
                ->orderBy('location_type')
                ->orderBy('upazila_id')
                ->orderBy('city_corp_id')
                ->orderBy('district_pourashava_id')
                ->orderBy('office_id')
                ->orderBy('type_id')
                ->get();
        else
            return $query->with('budget', 'upazila', 'cityCorporation', 'districtPourosova', 'location', 'office', 'type')
                ->orderBy('location_type')
                ->orderBy('upazila_id')
                ->orderBy('city_corp_id')
                ->orderBy('district_pourashava_id')
                ->orderBy('office_id')
                ->orderBy('type_id')
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
//                $budgetDetailInstance->per_beneficiary_amount = $budget_detail['per_beneficiary_amount'];
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
