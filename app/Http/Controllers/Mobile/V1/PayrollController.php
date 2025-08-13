<?php

namespace App\Http\Controllers\Mobile\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Payroll\SavePayrollRequest;
use App\Http\Requests\Admin\Payroll\SubmitPayrollRequest;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryResource;
use App\Http\Resources\Admin\Payroll\ActiveBeneficiaryResource;
use App\Http\Resources\Admin\Payroll\AllotmentResource;
use App\Http\Resources\Admin\Payroll\PayrollInstallmentScheduleResource;
use App\Http\Resources\Admin\Payroll\PayrollResource;
use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use App\Http\Services\Admin\Payroll\PayrollService;
use App\Http\Traits\MessageTrait;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class PayrollController extends Controller
{
    use MessageTrait;

    /**
     * @var PayrollService
     */
    private PayrollService $payrollService;

    /**
     * @param PayrollService $payrollService
     */
    public function __construct(PayrollService $payrollService)
    {
        $this->payrollService = $payrollService;
    }

    /**
     * @param $program_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgramInfo($program_id): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->payrollService->getProgramInfo($program_id);
            return response()->json([
                'data' => $program,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param int $program_id
     * @param int $financial_year_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getActiveInstallments(int $program_id, int $financial_year_id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $activeInstallmentList = $this->payrollService->getActiveInstallments($program_id, $financial_year_id);
//            return response()->json($beneficiaryList);
            return PayrollInstallmentScheduleResource::collection($activeInstallmentList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAllotmentAreaList(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $allotmentAreaList = $this->payrollService->getAllotmentAreaList($request);
//            return response()->json($beneficiaryList);
            return AllotmentResource::collection($allotmentAreaList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getAllotmentAreaStatistics(int $allotment_id)
    {
        try {
            $data = $this->payrollService->getAllotmentAreaStatistics($allotment_id);
            return response()->json([
                'data' => $data,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @param $allotment_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getActiveBeneficiaries(Request $request, $allotment_id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->payrollService->getActiveBeneficiaries($request, $allotment_id);
//            return response()->json($beneficiaryList);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SavePayrollRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function setBeneficiaries(SavePayrollRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $payroll = $this->payrollService->setBeneficiaries($request);
            return response()->json([
                'data' => $payroll,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function previewBeneficiaries(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->payrollService->previewBeneficiaries($request);
//            return response()->json($beneficiaryList);
            return \App\Http\Resources\Admin\Payroll\BeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SubmitPayrollRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function submitPayroll(SubmitPayrollRequest $request): \Illuminate\Http\JsonResponse
    {
        try {
            $this->payrollService->submitPayroll($request);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getApprovalList(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $approvalList = $this->payrollService->approvalList($request);
//            return response()->json($approvalList);
            return PayrollResource::class::collection($approvalList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

}
