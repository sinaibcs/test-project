<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emergency\EmergencyPayrollRequest;
use App\Http\Requests\Admin\Emergency\SubmitEmergencyPayrollRequest;
use App\Http\Resources\Admin\Emergency\ActiveBeneficiaryResource;
use App\Http\Resources\Admin\Emergency\EmergencyAllotmentResource;
use App\Http\Resources\Admin\Emergency\EmergencyBeneficiaryResource;
use App\Http\Resources\Admin\Emergency\EmergencyPayrollResource;
use App\Http\Services\Admin\Emergency\EmergencyPayrollService;
use App\Http\Traits\MessageTrait;
use App\Models\EmergencyBeneficiaryPayrollPaymentStatusLog;
use App\Models\EmergencyPayroll;
use App\Models\EmergencyPayrollDetails;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmergencyPayrollController extends Controller
{
    use MessageTrait;

    /**
     * @var EmergencyPayrollService
     */
    private EmergencyPayrollService $emergencyPayrollService;

    /**
     * @param EmergencyPayrollService $emergencyPayrollService
     */
    public function __construct(EmergencyPayrollService $emergencyPayrollService)
    {
        $this->emergencyPayrollService = $emergencyPayrollService;
    }

    /**
     * @param $program_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProgramInfo($program_id): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->emergencyPayrollService->getProgramInfo($program_id);
            return response()->json([
                'data' => $program,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], 200);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param int $program_id
     * @param int $financial_year_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getActiveInstallments(Request $request, $allotmentId): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $activeInstallmentList = $this->emergencyPayrollService->getActiveInstallments($request, $allotmentId);
            //            return response()->json($beneficiaryList);
            return EmergencyPayrollResource::collection($activeInstallmentList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function beneficiaryDelete($payroll_details_id): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->emergencyPayrollService->beneficiaryDelete($payroll_details_id);
            return response()->json([
                'data' => $program,
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], Response::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function getAllotmentAreaStatistics(Request $request, int $allotment_id)
    {
        try {
            $data = $this->emergencyPayrollService->getAllotmentAreaStatistics($request, $allotment_id);
            return response()->json([
                'data' => $data,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], Response::HTTP_OK);
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
            $allotmentAreaList = $this->emergencyPayrollService->getAllotmentAreaList($request);
            return EmergencyAllotmentResource::collection($allotmentAreaList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
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
            $beneficiaryList = $this->emergencyPayrollService->getActiveBeneficiaries($request, $allotment_id);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getSelectedBeneficiaries(Request $request, $allotment_id): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->emergencyPayrollService->getSelectedBeneficiaries($request, $allotment_id);
            return response()->json($program, 200);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function setBeneficiaries(EmergencyPayrollRequest $request)
    {
        $payroll = $this->emergencyPayrollService->processPayroll($request);
        if (isset($payroll) && isset($payroll['status']) == 210) {
            return response()->json([
                'data' => $payroll,
                'success' => false
            ]);
        } elseif (isset($payroll) && isset($payroll['status']) == 211) {
            return response()->json([
                'data' => $payroll,
                'success' => false,
            ]);
        } elseif (isset($payroll) && isset($payroll['status']) == 212) {
            return response()->json([
                'data' => $payroll,
                'success' => false,
            ]);
        } else {
            return response()->json([
                'data' => $payroll,
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ], Response::HTTP_OK);
        }
    }
    public function previewBeneficiaries(Request $request)
    {
        try {
            $beneficiaryList = $this->emergencyPayrollService->previewBeneficiaries($request);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function submitPayroll(SubmitEmergencyPayrollRequest $request)
    {
        try {
            $payroll = $this->emergencyPayrollService->submitPayroll($request);
            if (isset($payroll) && isset($payroll['status']) == 211) {
                return response()->json([
                    'data' => $payroll,
                    'success' => false,
                ]);
            } else {
                return response()->json([
                    'data' => $payroll,
                    'success' => true,
                    'message' => $this->insertSuccessMessage,
                ], Response::HTTP_OK);
            }
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), ['validation'], 500);
        }
    }
}
