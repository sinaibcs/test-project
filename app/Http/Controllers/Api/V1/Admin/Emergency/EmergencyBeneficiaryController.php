<?php

namespace App\Http\Controllers\Api\V1\Admin\Emergency;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Emergency\EmergencyBeneficiaryRequest;
use App\Http\Requests\Admin\Emergency\UpdateEmergencyBeneficiaryRequest;
use App\Http\Resources\Admin\Emergency\BankAndMfsResource;
use App\Http\Resources\Admin\Emergency\EmergencyBeneficiaryResource;
use App\Http\Services\Admin\Emergency\EmergencyBeneficiaryService;
use App\Http\Services\Notification\SMSservice;
use App\Http\Traits\MessageTrait;
use App\Jobs\SendEmail;
use App\Models\AllowanceProgram;
use App\Models\EmergencyAllotment;
use App\Models\EmergencyBeneficiary;
use Illuminate\Http\Request;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use Mpdf\MpdfException;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;

class EmergencyBeneficiaryController extends Controller
{
    use MessageTrait;

    private EmergencyBeneficiaryService $emergencyBeneficiaryService;

    public function __construct(EmergencyBeneficiaryService $emergencyBeneficiaryService, public SMSservice $SMSservice)
    {
        $this->emergencyBeneficiaryService = $emergencyBeneficiaryService;
        $this->SMSservice = $SMSservice;
    }

    /**
     * @throws \Throwable
     */
    public function store(Request $request)
    {
        $beneficiary = $this->emergencyBeneficiaryService->store($request);
        if ($beneficiary->status == 1) {
            $programName = EmergencyAllotment::where('id', $beneficiary->allotment_id)->first('name_en');
            $program = $programName->name_en;
            $message = "Dear $beneficiary->name_en," . "\nWe are thrilled to inform you that you have been selected as a recipient for the $program.\n\nYour Emergency Beneficiary ID is $beneficiary->beneficiary_id.\n\nSincerely," . "\nDepartment of Social Services";
            $this->SMSservice->sendSms($beneficiary->mobile, $message);
            if ($beneficiary->email) {
                $this->dispatch(new SendEmail($beneficiary->email, $beneficiary->name_en, $program));
            }
        }

        Helper::activityLogInsert($beneficiary, '', 'Emergency Beneficiary', 'Emergency Beneficiary Created !');
        return EmergencyBeneficiaryResource::make($beneficiary)->additional([
            'success' => true,
            'message' => $this->insertSuccessMessage,
        ]);
    }

    public function storeMultipleData(Request $request)
    {
        try {
            $beneficiary = $this->emergencyBeneficiaryService->storeMultipleData($request);

            if ($beneficiary) {
                Helper::activityLogInsert($beneficiary, '', 'Emergency Beneficiary', 'Emergency Beneficiary Created !');
                return EmergencyBeneficiaryResource::make($beneficiary)->additional([
                    'success' => true,
                    'message' => $this->insertSuccessMessage,
                ]);
            } else {
                return response()->json([
                    'data' => null,
                    'success' => false,
                    'message' => "Maximum beneficiary limit exceeded",
                ], 200);
            }
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function edit($id): \Illuminate\Http\JsonResponse|EmergencyBeneficiaryResource
    {
        try {
            $beneficiary = $this->emergencyBeneficiaryService->edit($id);
            return EmergencyBeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function details($id): \Illuminate\Http\JsonResponse|EmergencyBeneficiaryResource
    {
        try {
            $beneficiary = $this->emergencyBeneficiaryService->details($id);
            return EmergencyBeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function beneficiariesInfo(Request $request, $id): \Illuminate\Http\JsonResponse
    {
        $beneficiaryInfo = $this->emergencyBeneficiaryService->beneficiariesInfo($id);
        return handleResponse($beneficiaryInfo, null);
    }

    public function getSelectedBeneficiaries(Request $request)
    {
        try {
            $beneficiaryList = $this->emergencyBeneficiaryService->getSelectedBeneficiaries($request);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getExistingBeneficiariesInfo(Request $request)
    {
        try {
            $beneficiaryList = $this->emergencyBeneficiaryService->getExistingBeneficiaries($request);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getNewBeneficiariesInfo(Request $request)
    {
        try {
            $beneficiaryList = $this->emergencyBeneficiaryService->getNewBeneficiaries($request);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function update(UpdateEmergencyBeneficiaryRequest $request, $id)
    {
        try {
            $beforeUpdate = EmergencyBeneficiary::find($id);
            $beneficiary = $this->emergencyBeneficiaryService->update($request, $id);
            Helper::activityLogUpdate($beneficiary, $beforeUpdate, 'Emergency Beneficiary', 'Emergency Beneficiary Updated !');

            return EmergencyBeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function destroy($id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->emergencyBeneficiaryService->destroy($id);
            Helper::activityLogDelete($data, '', 'Emergency Beneficiary', 'Emergency Beneficiary Deleted !');
            return handleResponse($data, $this->deleteSuccessMessage);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @return ResponseAlias
     * @throws MpdfException
     */
    public function getBeneficiaryListPdf(Request $request): ResponseAlias
    {
        $beneficiaries = $this->emergencyBeneficiaryService->list($request, true);
        $user = auth()->user()->load('assign_location.parent.parent.parent.parent');
        $generated_by = $user->full_name;
        $assign_location = '';
        if ($user->assign_location) {
            $assign_location .= ', ' . (app()->isLocale('bn') ? $user->assign_location?->name_bn : $user->assign_location?->name_en);
            if ($user->assign_location?->parent) {
                $assign_location .= ', ' . (app()->isLocale('bn') ? $user->assign_location?->parent?->name_bn : $user->assign_location?->parent?->name_en);
                if ($user->assign_location?->parent?->parent) {
                    $assign_location .= ', ' . (app()->isLocale('bn') ? $user->assign_location?->parent?->parent?->name_bn : $user->assign_location?->parent?->parent?->name_en);
                    //                    if ($user->assign_location?->parent?->parent?->parent) {
                    //                        $assign_location .= ', ' . $user->assign_location?->parent?->parent?->parent?->name_bn;
                    //                    }
                }
            }
        }
        $data = ['beneficiaries' => $beneficiaries, 'generated_by' => $generated_by, 'assign_location' => $assign_location];
        $pdf = LaravelMpdf::loadView(
            'reports.emergency.beneficiary_list',
            $data,
            [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("emergency_beneficiary_list.page_title"),
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 25,
                'margin_header' => 10,
                'margin_footer' => 5,
            ]
        );

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]
        );

        //        $fileName = 'উপকারভোগীর_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
        //        $pdfPath = public_path("/pdf/$fileName");
        //        $pdf->save($pdfPath);
        //        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

    public function list(Request $request)
    {
        try {
            $beneficiaryList = $this->emergencyBeneficiaryService->list($request);
            return EmergencyBeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getCoverageAreaWiseBankAndMfs(Request $request)
    {
        try {
            $bankAndMfsList = $this->emergencyBeneficiaryService->getCoverageAreaWiseBankAndMfs($request);
            return BankAndMfsResource::collection($bankAndMfsList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
}
