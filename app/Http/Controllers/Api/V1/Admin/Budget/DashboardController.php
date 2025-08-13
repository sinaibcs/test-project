<?php

namespace App\Http\Controllers\Api\V1\Admin\Budget;

use App\Http\Controllers\Controller;
use App\Http\Services\Admin\BudgetAllotment\DashboardService;
use App\Http\Traits\MessageTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class DashboardController extends Controller
{
    use MessageTrait;

    /**
     * @var DashboardService
     */
    private DashboardService $dashboardService;

    /**
     * @param DashboardService $budgetService
     */
    public function __construct(DashboardService $dashboardService)
    {
        $this->dashboardService = $dashboardService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getBudgetAndAllotmentSummary(Request $request): \Illuminate\Http\JsonResponse
    {
        $financialYearRange = $this->dashboardService->financialYearRange();
        $currentFinancialYear = $this->dashboardService->currentFinancialYear();
//        $totalBudgetAmount = $this->dashboardService->totalBudgetAmount();
        $totalBeneficiaries = $this->dashboardService->totalBeneficiaries();
        $totalAllotmentAmount = $this->dashboardService->totalAllotmentAmount();
//        $currentTotalBeneficiaries = $this->dashboardService->currentTotalBeneficiaries($currentFinancialYear?->id);
        $currentBudgetAmount = $this->dashboardService->currentBudgetAmount($currentFinancialYear?->id);
        $currentAllotmentAmount = $this->dashboardService->currentAllotmentAmount($currentFinancialYear?->id);
        $data = [
            'financialYearRange' => $financialYearRange?->financial_year_range ?: '',
            'currentFinancialYear' => $currentFinancialYear?->financial_year ?: '',
//            'totalBudgetAmount' => $totalBudgetAmount,
            'totalAllotmentAmount' => $totalAllotmentAmount,
            'totalBeneficiaries' => $totalBeneficiaries,
//            'currentTotalBeneficiaries' => $currentTotalBeneficiaries,
            'currentBudgetAmount' => $currentBudgetAmount,
            'currentAllotmentAmount' => $currentAllotmentAmount,
        ];
        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalBudget(Request $request)
    {
        $data = $this->dashboardService->totalBudget($request);
        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalAllotment(Request $request)
    {
        $data = $this->dashboardService->totalAllotment($request);
        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearlyBeneficiaries(Request $request)
    {
        $data = $this->dashboardService->yearlyBeneficiaries($request);
        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgramWiseAllotmentList(Request $request)
    {
        $data = $this->dashboardService->programWiseAllotmentList($request);
        return response()->json([
            'data' => $data,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }
}
