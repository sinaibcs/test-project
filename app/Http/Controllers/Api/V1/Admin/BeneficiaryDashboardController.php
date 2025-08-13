<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Constants\BeneficiaryStatus;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use App\Http\Traits\MessageTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class BeneficiaryDashboardController extends Controller
{
    use MessageTrait;

    /**
     * @var BeneficiaryService
     */
    private BeneficiaryService $beneficiaryService;

    /**
     * @param BeneficiaryService $beneficiaryService
     */
    public function __construct(BeneficiaryService $beneficiaryService)
    {
        $this->beneficiaryService = $beneficiaryService;
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getTotalBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        $beneficiaries = $this->beneficiaryService->getStatusWiseTotalBeneficiaries();
        $totalReplacedBeneficiaries = $this->beneficiaryService->getTotalReplacedBeneficiaries();

        $totalBeneficiaries = 0;
        $totalActiveBeneficiaries = 0;
        $totalInactiveBeneficiaries = 0;
        $totalWaitingBeneficiaries = 0;
        if ($beneficiaries) {
            foreach ($beneficiaries as $beneficiary) {
                if ($beneficiary->status == BeneficiaryStatus::ACTIVE) {
                    $totalActiveBeneficiaries = $beneficiary->beneficiary_count;
                    $totalBeneficiaries += $totalActiveBeneficiaries;
                } elseif ($beneficiary->status == BeneficiaryStatus::INACTIVE) {
                    $totalInactiveBeneficiaries = $beneficiary->beneficiary_count;
                    $totalBeneficiaries += $totalInactiveBeneficiaries;
                } elseif ($beneficiary->status == BeneficiaryStatus::WAITING) {
                    $totalWaitingBeneficiaries = $beneficiary->beneficiary_count;
                    $totalBeneficiaries += $totalWaitingBeneficiaries;
                }

            }
        }


        $beneficiaries = [
            "totalBeneficiaries" => $totalBeneficiaries,
            "totalActiveBeneficiaries" => $totalActiveBeneficiaries,
            "totalInactiveBeneficiaries" => $totalInactiveBeneficiaries,
            "totalWaitingBeneficiaries" => $totalWaitingBeneficiaries,
            "totalReplacedBeneficiaries" => $totalReplacedBeneficiaries
        ];
        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getLocationWiseBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        $beneficiaries = $this->beneficiaryService->getLocationWiseBeneficiaries($request);
        $total = $beneficiaries->sum(function ($item) {
            return $item->value;
        });
        $beneficiaries = $beneficiaries->map(function ($item) use ($total) {
            $item->percentage = $total > 0 ? round(($item->value / $total) * 100, 2) : 0;
            return $item;
        });

        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGenderWiseBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        $beneficiaries = $this->beneficiaryService->getGenderWiseBeneficiaries($request);
        $total = $beneficiaries->sum(function ($item) {
            return $item->value;
        });
        $beneficiaries = $beneficiaries->map(function ($item) use ($total) {
            $item->percentage = round(($item->value / $total) * 100, 2);
            return $item;
        });
        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearWiseWaitingBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        $results = $this->beneficiaryService->getYearWiseBeneficiaries($request);
        $beneficiaries = [];
        foreach ($results as $result) {
            $beneficiaries[$result->year]['year'] = $result->year;
            if ($result->status == 1)
                $beneficiaries[$result->year]['value'] = $result->value;
            elseif ($result->status == 3)
                $beneficiaries[$result->year]['waiting'] = $result->value;
        }
        $beneficiaries = array_values($beneficiaries);
        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgramWiseBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        $beneficiaries = $this->beneficiaryService->getProgramWiseBeneficiaries($request);
        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAgeWiseBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        $beneficiaries = $this->beneficiaryService->getAgeWiseBeneficiaries($request)->first();
        $beneficiaries = collect($beneficiaries);
        $beneficiaries = $beneficiaries->map(function ($item, $key) {
            return ["age" => $key, "beneficiaries" => $item ?? 0];
        });
        $beneficiaries = array_values($beneficiaries->toArray());

        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getYearWiseProgramShifting(Request $request): \Illuminate\Http\JsonResponse
    {
        $beneficiaries = $this->beneficiaryService->getYearWiseProgramShifting($request);
        return response()->json([
            'data' => $beneficiaries,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }
}
