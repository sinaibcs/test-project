<?php

namespace App\Http\Services\Admin\BudgetAllotment;

use App\Models\Allotment;
use App\Models\AllowanceProgram;
use App\Models\BudgetDetail;
use App\Models\FinancialYear;
use Illuminate\Http\Request;


class DashboardService
{
    /**
     * @return FinancialYear
     */
    public function financialYearRange()
    {
        return FinancialYear::query()
            ->join('budgets', 'budgets.financial_year_id', '=', 'financial_years.id')
            ->selectRaw("concat(min(year(financial_years.start_date)), '-', max(year(financial_years.end_date))) as financial_year_range")
            ->where('budgets.is_approved', true)
            ->first();
    }
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
    public function getCurrentFinancialYear()
    {
        return FinancialYear::query()->where('status', 1)->first();
    }

    /**
     * @return int|mixed
     */
    public function totalBudgetAmount()
    {
        return BudgetDetail::query()
            ->join('budgets', 'budgets.id', '=', 'budget_details.budget_id')
            ->where('budgets.is_approved', 1)
            ->sum('budget_details.total_amount');
    }

    /**
     * @return int|mixed
     */
    public function totalBeneficiaries()
    {
        return BudgetDetail::query()
            ->join('budgets', 'budgets.id', '=', 'budget_details.budget_id')
            ->where('budgets.is_approved', true)
            ->sum('budget_details.total_beneficiaries');
    }

    /**
     * @param $current_financial_year_id
     * @return int|mixed
     */
    public function currentBudgetAmount($current_financial_year_id)
    {
        return BudgetDetail::query()
            ->join('budgets', 'budget_details.budget_id', '=', 'budgets.id')
            ->where('budgets.financial_year_id', $current_financial_year_id)
            ->where('budgets.is_approved', true)
            ->sum('budget_details.total_amount');
    }

    /**
     * @param $current_financial_year_id
     * @return int|mixed
     */
    public function currentTotalBeneficiaries($current_financial_year_id)
    {
        return BudgetDetail::query()
            ->join('budgets', 'budget_details.budget_id', '=', 'budgets.id')
            ->where('budgets.financial_year_id', $current_financial_year_id)
            ->sum('budget_details.total_beneficiaries');
    }

    /**
     * @return int|mixed
     */
    public function totalAllotmentAmount()
    {
        return Allotment::query()->sum('total_amount');
    }

    /**
     * @param $current_financial_year_id
     * @return int|mixed
     */
    public function currentAllotmentAmount($current_financial_year_id)
    {
        return Allotment::query()
            ->where('financial_year_id', $current_financial_year_id)
            ->sum('total_amount');
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function totalBudget(Request $request)
    {
        $financial_year_ids = $request->has('financial_year_ids') ? $request->get('financial_year_ids') : [];
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
        $location_ids = $request->has('location_ids') ? $request->get('location_ids') : [];
        $query = BudgetDetail::query()
            ->join('budgets', 'budget_details.budget_id', '=', 'budgets.id')
            ->join('financial_years', 'financial_years.id', '=', 'budgets.financial_year_id');
        $query = $query->where('budgets.is_approved', true);
        if (count($financial_year_ids) > 0) {
            $query = $query->whereIn('budgets.financial_year_id', $financial_year_ids);
        }
        if (count($program_ids) > 0) {
            $query = $query->whereIn('budgets.program_id', $program_ids);
        }
        if (count($location_ids) > 0) {
            $query = $query->whereIn('budget_details.division_id', $location_ids);
        }
        return $query->selectRaw('financial_years.financial_year, sum(budget_details.total_amount) as total_amount')
            ->groupBy('financial_years.financial_year')
            ->orderBy('financial_years.financial_year', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function totalAllotment(Request $request)
    {
        $financial_year_ids = $request->has('financial_year_ids') ? $request->get('financial_year_ids') : [];
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
        $location_ids = $request->has('location_ids') ? $request->get('location_ids') : [];
        $query = Allotment::query()
            ->join('financial_years', 'financial_years.id', '=', 'allotments.financial_year_id');
        if (count($financial_year_ids) > 0) {
            $query = $query->whereIn('allotments.financial_year_id', $financial_year_ids);
        }
        if (count($program_ids) > 0) {
            $query = $query->whereIn('allotments.program_id', $program_ids);
        }
        if (count($location_ids) > 0) {
            $query = $query->whereIn('allotments.division_id', $location_ids);
        }
        return $query->selectRaw('financial_years.financial_year, sum(allotments.total_amount) as total_amount')
            ->groupBy('financial_years.financial_year')
            ->orderBy('financial_years.financial_year', 'desc')
            ->limit(10)
            ->get();
    }

    /**
     * @param Request $request
     * @return AllowanceProgram[]|\Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|\Illuminate\Database\Query\Builder[]|\Illuminate\Support\Collection
     */
    public function yearlyBeneficiaries(Request $request)
    {
        $financial_year_ids = $request->has('financial_year_ids') ? $request->get('financial_year_ids') : [];
        $program_ids = $request->has('program_ids') ? $request->get('program_ids') : [];
        $location_ids = $request->has('location_ids') ? $request->get('location_ids') : [];
        $query = AllowanceProgram::query()
            ->leftJoin('budgets', 'budgets.program_id', '=', 'allowance_programs.id')
            ->leftJoin('budget_details', 'budget_details.budget_id', '=', 'budgets.id');
//        $query = $query->where('budgets.is_approved', 1);
        if (count($financial_year_ids) > 0) {
            $query = $query->whereIn('budgets.financial_year_id', $financial_year_ids);
        }
        if (count($program_ids) > 0) {
            $query = $query->whereIn('budgets.program_id', $program_ids);
        }
        if (count($location_ids) > 0) {
            $query = $query->whereIn('budget_details.division_id', $location_ids);
        }
        return $query->selectRaw('allowance_programs.name_en, allowance_programs.name_bn, ifnull(sum(budget_details.total_beneficiaries),0) as total_beneficiaries')
            ->groupBy('allowance_programs.name_en', 'allowance_programs.name_bn')
            ->orderBy('allowance_programs.name_en', 'asc')
            ->get();
    }

    /**
     * @param Request $request
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function programWiseAllotmentList(Request $request)
    {
        if ($request->has('financial_year_id')) {
            $financial_year_id = $request->get('financial_year_id');
        } else {
            $currentFinancialYear = $this->getCurrentFinancialYear();
            $financial_year_id = $currentFinancialYear?->id;
        }
        $query = Allotment::query()
            ->join('allowance_programs', 'allowance_programs.id', '=', 'allotments.program_id');
        if ($financial_year_id) {
            $query = $query->where('allotments.financial_year_id', $financial_year_id);
        }

        return $query->selectRaw('allowance_programs.name_en as program_name_en, allowance_programs.name_bn as program_name_bn, sum(allotments.total_beneficiaries) as total_beneficiaries, sum(allotments.additional_beneficiaries) as additional_beneficiaries, sum(allotments.total_amount) as total_amount')
            ->groupBy(['allowance_programs.name_en', 'allowance_programs.name_bn'])
            ->orderBy('allowance_programs.name_en')
            ->get();
    }
}
