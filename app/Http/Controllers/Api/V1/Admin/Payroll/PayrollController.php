<?php

namespace App\Http\Controllers\Api\V1\Admin\Payroll;

use App\Models\User;
use App\Helpers\Helper;
use App\Jobs\SendEmail;
use App\Models\Payroll;
use App\Models\Beneficiary;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use function PHPUnit\Framework\isNull;
use App\Models\PayrollInstallmentSchedule;
use App\Exceptions\AuthBasicErrorException;
use App\Exports\PayrollBeneficiariesExport;
use App\Http\Services\Notification\SMSservice;
use App\Http\Services\Admin\Payroll\PayrollService;
use App\Http\Resources\Admin\Payroll\PayrollResource;
use Illuminate\Http\Exceptions\HttpResponseException;
use App\Http\Requests\Admin\Payroll\SavePayrollRequest;
use App\Http\Resources\Admin\Payroll\AllotmentResource;
use App\Http\Requests\Admin\Payroll\RejectPayrollRequest;
use App\Http\Requests\Admin\Payroll\SubmitPayrollRequest;
use App\Http\Requests\Admin\Payroll\VerifyPayrollRequest;
use App\Http\Resources\Admin\Payroll\BeneficiaryResource;
use App\Http\Requests\Admin\Payroll\ApprovePayrollRequest;

use App\Http\Resources\Admin\Payroll\ActiveBeneficiaryResource;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Http\Resources\Admin\Payroll\PayrollInstallmentScheduleResource;

class PayrollController extends Controller
{
    use MessageTrait;

    private PayrollService $payrollService;

    public function __construct(PayrollService $payrollService, public SMSservice $SMSservice)
    {
        $this->payrollService = $payrollService;
        $this->SMSservice = $SMSservice;
    }

    public function getUserWisePrograms(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = array();
            $userInfo = auth()->user();
            $user = User::find($userInfo->id); // $userId is the ID of the user you want to fetch
            if ($user->user_type == 1) {
                $data = AllowanceProgram::get();
            } else {
                $programs = $user->getPrograms();
                if (!empty($programs)) {
                    $data = AllowanceProgram::whereIn('id', $programs)->get();
                }
            }
            return response()->json([
                'data' => $data,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function rollback($allotment_id): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->payrollService->rollback($allotment_id);
            return response()->json([
                'data' => $program,
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function beneficiaryDelete($payroll_details_id): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->payrollService->beneficiaryDelete($payroll_details_id);
            return response()->json([
                'data' => $program,
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    
    public function beneficiaryDeleteAll(Request $request): \Illuminate\Http\JsonResponse
    {
        \DB::beginTransaction();
        try {
            foreach($request->ids as $id){
                $this->payrollService->beneficiaryDelete($id);
            }
            \DB::commit();
            return response()->json([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            \DB::rollBack();
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getSelectedBeneficiaries(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $program = $this->payrollService->getSelectedBeneficiaries($request);
            return response()->json($program, 200);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

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

    public function getActiveInstallments(int $program_id, $financial_year_id)
    {
        try {
            $activeInstallmentList = $this->payrollService->getActiveInstallments($program_id, $financial_year_id);
            return PayrollInstallmentScheduleResource::collection($activeInstallmentList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    
    public function getInstallments(int $program_id)
    {
        try {
            $installmentList = $this->payrollService->getInstallments($program_id);
            return PayrollInstallmentScheduleResource::collection($installmentList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getPayrollBeneficiaries(Request $request)
    {
        if($request->download == 'yes'){
            $beneficiaryList = $this->payrollService->getPayrollBeneficiaries($request, true);
            return Excel::download(new PayrollBeneficiariesExport($beneficiaryList),'approve-list.xlsx');
        }
        try {
            $beneficiaryList = $this->payrollService->getPayrollBeneficiaries($request);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getPayrollRejectedBeneficiaries(Request $request)
    {
        if($request->download == 'yes'){
            $beneficiaryList = $this->payrollService->getPayrollRejectedBeneficiaries($request, true);
            return Excel::download(new PayrollBeneficiariesExport($beneficiaryList),'approve-list.xlsx');
        }
        try {
            $beneficiaryList = $this->payrollService->getPayrollRejectedBeneficiaries($request);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getPayrollApproveBeneficiaries(Request $request)
    {   
        if($request->download == 'yes'){
            $beneficiaryList = $this->payrollService->getPayrollApproveBeneficiaries($request, true);
            return Excel::download(new PayrollBeneficiariesExport($beneficiaryList),'approve-list.xlsx');
        }
        try {
            $beneficiaryList = $this->payrollService->getPayrollApproveBeneficiaries($request);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getActivePayrollBeneficiaries(Request $request)
    {
        if($request->download == 'yes'){
            $beneficiaryList = $this->payrollService->getActivePayrollBeneficiaries($request, true);
            return Excel::download(new PayrollBeneficiariesExport($beneficiaryList),'approve-list.xlsx');
        }
        try {
            $beneficiaryList = $this->payrollService->getActivePayrollBeneficiaries($request);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getActiveBeneficiaries(Request $request, $allotment_id)
    {
        try {
            $beneficiaryList = $this->payrollService->getActiveBeneficiaries($request, $allotment_id);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function searchActiveBeneficiaries(Request $request, $allotment_id)
    {
        // dd($request->all());
        try {
            $beneficiaryList = $this->payrollService->searchActiveBeneficiaries($request, $allotment_id);
            return ActiveBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getAllotmentAreaList(Request $request)
    {
        try {
            $allotmentAreaList = $this->payrollService->getAllotmentAreaList($request);
            return AllotmentResource::collection($allotmentAreaList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function getAllotmentClassList(Request $request)
    {
        try {
            $allotmentAreaList = $this->payrollService->getAllotmentClassList($request);
            return AllotmentResource::collection($allotmentAreaList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getAllotmentAreaStatistics(Request $request, int $allotment_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->payrollService->getAllotmentAreaStatistics($request, $allotment_id);
            return response()->json([
                'data' => $data,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function setBeneficiaries(SavePayrollRequest $request): \Illuminate\Http\JsonResponse
    {
        $payroll = $this->payrollService->processPayroll($request);
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
            $user = auth()->user();
            if (!isNull($user) && !isNull($user->office_id)) {
                $address = auth()->user()->office;
            } else {
                $address = "";
            }
            $programName = AllowanceProgram::where('id', $payroll->program_id)->value('name_en');
            $financialYear = FinancialYear::where('id', $payroll->financial_year_id)->value('financial_year');
            $installment = PayrollInstallmentSchedule::where('id', $payroll->installment_schedule_id)->value('installment_name');
            // $message = "Payroll has been created successfully for the $programName program."
            //     . "\nFinancial year: $financialYear"
            //     . "\nInstallment: $installment."
            //     . "\nCreated by,"
            //     . "\n$user->full_name"
            //     . "\nDate and Time: $payroll->created_at"
            //     . "\nOffice Address: $address"
            //     . "\nDepartment of Social Services";
            // $this->SMSservice->sendSms($user->mobile, $message);
            if ($user->email) {
                $this->dispatch(new SendEmail($user->email, $user->full_name, $programName));
            }
            Helper::activityLogInsert($payroll, '', 'Payroll', 'Payroll has been Created !');
            return response()->json([
                'data' => $payroll,
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        }
    }
    public function previewBeneficiaries(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->payrollService->previewBeneficiaries($request);
            return BeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function submitPayroll(SubmitPayrollRequest $request): \Illuminate\Http\JsonResponse
    {
        $code = $request->otp;
        $cachedCode = \Cache::get("user_login_otp_" . $request->user()->id);
        if (!$cachedCode || $code != $cachedCode) {
            throw new AuthBasicErrorException(
                422,
                'invalid_otp',
                "Verification code invalid !",
            );
        }
        try {
            $payroll = $this->payrollService->submitPayroll($request);
            return response()->json([
                'data' => $payroll,
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (HttpResponseException $e) {
            $response = $e->getResponse();
            $responseData = json_decode($response->getContent(), true);
            return response()->json([
                'success' => false,
                'message' => $responseData['message'] ?? 'An error occurred',
            ], $response->getStatusCode());
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getApprovalList(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $approvalList = $this->payrollService->approvalList($request);
            return PayrollResource::class::collection($approvalList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function approvePayroll(ApprovePayrollRequest $request)
    {

        try {
            $data = $this->payrollService->approvePayroll($request);
            // if (isset($data['statusCode']) == 200) {
            //     foreach ($data['data'] as $data) {
            //         Helper::activityLogInsert($data, '', 'Payroll Approval', 'Payroll Approved');
            //     }
            // }
            return $data;
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    
    public function approveAllPayrollBeneficiaries(Request $request)
    {
        $request->validate([
            'office_id' => 'required',
            'financial_year_id' => 'required',
            'installment_id' => 'required',
            'program_id' => 'required'
        ]);

        try {
            $data = $this->payrollService->approveAllPayrollBeneficiaries($request);
            return $data;
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function verifyPayroll(VerifyPayrollRequest $request)
    {

        try {
            $beforeVerify = Payroll::find($request->id);
            $verify = $this->payrollService->verifyPayroll($request, $request->id);
            if ($verify) {
                Helper::activityLogInsert($verify, $beforeVerify, 'Payroll Rejected', 'Payroll Rejected');
                return response()->json([
                    'success' => true,
                    'message' => $this->updateSuccessMessage,
                ], ResponseAlias::HTTP_OK);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $this->fetchFailedMessage,
                ], ResponseAlias::HTTP_OK);
            }
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function rejectPayroll(RejectPayrollRequest $request)
    {
        try {
            $data = $this->payrollService->rejectPayroll($request);
            return $data;
            // return PayrollResource::make($data)->additional([
            //     'success' => true,
            //     'message' => "Payroll Rejected Successfully",
            // ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
}
