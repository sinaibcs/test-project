<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emergency\EmergencyPayrollRequest;
use App\Http\Requests\Admin\Payroll\RejectPayrollRequest;
use App\Http\Resources\Admin\Emergency\ActiveBeneficiaryResource;
use App\Http\Resources\Admin\Emergency\EmergencyBeneficiaryResource;
use App\Http\Resources\Admin\Emergency\EmergencyPayrollApprovalResource;
use App\Http\Resources\Admin\Emergency\EmergencyPayrollResource;
use App\Http\Services\Admin\Emergency\EmergencyPayrollApprovalService;
use App\Http\Traits\MessageTrait;
use App\Models\EmergencyBeneficiary;
use App\Models\EmergencyPayroll;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class EmergencyPayrollApprovalController extends Controller
{
    use MessageTrait;

    /**
     * @var EmergencyPayrollApprovalService
     */
    private EmergencyPayrollApprovalService $emergencyPayrollApprovalService;

    public function __construct(EmergencyPayrollApprovalService $emergencyPayrollApprovalService)
    {
        $this->emergencyPayrollApprovalService = $emergencyPayrollApprovalService;
    }


    public function getPayrollList(Request $request)
    {
        try {
            $approvalList = $this->emergencyPayrollApprovalService->getPayrollList($request);
            return EmergencyPayrollApprovalResource::class::collection($approvalList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function getPayrollRejectedBeneficiaries(Request $request, $payroll_id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->emergencyPayrollApprovalService->getPayrollRejectedBeneficiaries($request, $payroll_id);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getPayrollApproveBeneficiaries(Request $request, $payroll_id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->emergencyPayrollApprovalService->getPayrollApproveBeneficiaries($request, $payroll_id);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getActivePayrollBeneficiaries(Request $request, $payroll_id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->emergencyPayrollApprovalService->getActivePayrollBeneficiaries($request, $payroll_id);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function approvePayroll(Request $request, $id)
    {
        try {
            $data = $this->emergencyPayrollApprovalService->approvePayroll($request, $id);
            if ($data['statusCode'] == 200) {
                Helper::activityLogInsert($data['data'], '', 'Emergency Payroll Approval', 'Emergency Payroll Approved');
            }
            return $data;
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function rejectBeneficiary($id): EmergencyBeneficiaryResource|\Illuminate\Http\JsonResponse
    {
        try {
            $beneficiary = $this->emergencyPayrollApprovalService->rejectBeneficiary($id);
            $beforeUpdate = EmergencyBeneficiary::find($id);
            Helper::activityLogInsert($beneficiary, $beforeUpdate, 'Emergency Beneficiary Rejected', 'Emergency Beneficiary Rejected Successfully');
            return EmergencyBeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => "Emergency Beneficiary Rejected Successfully",
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function rejectPayroll(RejectPayrollRequest $request)
    {

        try {
            $beforeReject = EmergencyPayroll::find($request->id);
            $reject = $this->emergencyPayrollApprovalService->rejectPayroll($request);

            if (isset($reject['error'])) {
                return response()
                    ->json(
                        [
                            'success' => false,
                            'message' => $reject['details'],
                        ],
                        Response::HTTP_OK
                    );
            } else {
                Helper::activityLogInsert($reject, $beforeReject, 'Payroll Rejected', 'Payroll Rejected');
                return EmergencyPayrollResource::make($reject)->additional([
                    'success' => true,
                    'message' => "Payroll Rejected Successfully",
                ]);
            }
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
}
