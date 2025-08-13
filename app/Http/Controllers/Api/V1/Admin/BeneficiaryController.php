<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Resources\Admin\Beneficiary\BeneficiaryAccountChangeResource;
use App\Http\Resources\Mobile\Payroll\PaymentTrackingMobileResource;
use App\Models\Variable;
use Log;
use Excel;
use Mpdf\MpdfException;
use App\Models\Application;
use App\Models\Beneficiary;
use App\Imports\ExcelImport;
use Illuminate\Http\Request;
use App\Models\AdditionalFields;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use App\Exports\BeneficiariesExport;
use App\Http\Controllers\Controller;
use App\Models\BeneficiaryVerifyLog;
use App\Jobs\UpdateBeneficiaryAccountJob;
use App\Models\BeneficiaryChangeTracking;
use App\Models\PayrollPaymentCycleDetail;
use Illuminate\Support\Facades\Validator;
use App\Jobs\UpdateBeneficiaryLocationJob;
use App\Jobs\ProcessBeneficiariesAccountsExcel;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Requests\Admin\Beneficiary\VerifyAllRequest;
use App\Http\Requests\Admin\Beneficiary\ApproveAllRequest;
use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryResource;
use App\Http\Requests\Admin\Beneficiary\BeneficiaryExitRequest;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Http\Requests\Admin\Beneficiary\BeneficiaryLocationShiftingRequest;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryLocationShiftingResource;
use App\Http\Requests\Admin\Beneficiary\DeleteBeneficiaryRequest;
use App\Http\Requests\Admin\Beneficiary\SearchBeneficiaryRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateAccountInfoRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateBeneficiaryRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateContactInfoRequest;
use App\Http\Requests\Admin\Beneficiary\UpdateNomineeInfoRequest;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryExitResource;
use App\Http\Requests\Admin\Beneficiary\ReplaceBeneficiaryRequest;
use App\Http\Requests\Admin\Beneficiary\UpdatePersonalInfoRequest;
use App\Http\Requests\Admin\Beneficiary\BeneficiaryShiftingRequest;
use App\Http\Requests\Admin\Beneficiary\InactiveBeneficiaryRequest;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryIdCardResource;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryReplaceResource;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryDropDownResource;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryShiftingResource;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryPaymentHistoryResource;
use App\Http\Resources\Admin\Beneficiary\BeneficiaryGrivanceHistoryResource;

/**
 *
 */
class BeneficiaryController extends Controller
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
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserLocation(): \Illuminate\Http\JsonResponse
    {
        $uerLocation = $this->beneficiaryService->getUserLocation();
        return response()->json([
            'data' => $uerLocation,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * Display a listing of the resource.
     */
    public function list(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->list($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function listDropDown(Request $request)
    {
        try {
            $beneficiaryList = $this->beneficiaryService->list($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryDropDownResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function show($id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->detail($id);
            $application = null;
            if($beneficiary->application_id){
                $application = $beneficiary->application;
                if ($application) {
                    $application->load([
                        'allowAddiFields' => function ($q) use ($application) {
                            $q->with(['allowAddiFieldValues' => function ($q) use ($application) {
                                $q->where('application_id', $application->id);
                            }]);
                        }
                    ]);
                }
            }

            if ($beneficiary) {
                $beneficiary->load([
                    'allowAddiFields',
                    'allowAddiFields.additional_field_value',
                ]);

                $pmtData = $this->beneficiaryService->getPmtData($beneficiary->beneficiary_id);

                return BeneficiaryResource::make($beneficiary)->additional([
                    'success' => true,
                    'message' => $this->fetchSuccessMessage,
                    'application' => $application,
                    'pmt_data'    => $pmtData
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $this->notFoundMessage,
                ], ResponseAlias::HTTP_OK);
            }

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function get($id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->get($id);
            if ($beneficiary) {
                return BeneficiaryResource::make($beneficiary)->additional([
                    'success' => true,
                    'message' => $this->fetchSuccessMessage,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $this->notFoundMessage,
                ], ResponseAlias::HTTP_OK);
            }

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryIdCardResource
     */
    public function idCard($id): \Illuminate\Http\JsonResponse|BeneficiaryIdCardResource
    {
        try {
            $beneficiary = $this->beneficiaryService->idCard($id);
            if ($beneficiary) {
                return BeneficiaryIdCardResource::make($beneficiary)->additional([
                    'success' => true,
                    'message' => $this->fetchSuccessMessage,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $this->notFoundMessage,
                ], ResponseAlias::HTTP_OK);
            }

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function getByBeneficiaryId($beneficiary_id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->getByBeneficiaryId($beneficiary_id);
            if ($beneficiary) {
                return BeneficiaryResource::make($beneficiary)->additional([
                    'success' => true,
                    'message' => $this->fetchSuccessMessage,
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => $this->notFoundMessage,
                ], ResponseAlias::HTTP_OK);
            }

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getPaymentSummary($beneficiary_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->beneficiaryService->getPaymentSummary($beneficiary_id);
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
     * @param $beneficiary_id
     */
    public function getPaymentHistory($beneficiary_id)
    {
        try {
            $data = $this->beneficiaryService->getPaymentHistory($beneficiary_id);
            return (new PaymentTrackingMobileResource($data))->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getGrievanceSummary($beneficiary_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->beneficiaryService->getGrievanceSummary($beneficiary_id);
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
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getGrievanceHistory($beneficiary_id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $data = $this->beneficiaryService->getGrievanceHistory($beneficiary_id);
            return BeneficiaryGrivanceHistoryResource::collection($data)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChangeTrackingSummary($beneficiary_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->beneficiaryService->getChangeTrackingSummary($beneficiary_id);
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
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getChangeTrackingHistory($beneficiary_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->beneficiaryService->getChangeTrackingHistory($beneficiary_id);
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
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getNomineeChangeHistory($beneficiary_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->beneficiaryService->getNomineeChangeHistory($beneficiary_id);
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
     * @param $beneficiary_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function getAccountChangeHistory($beneficiary_id): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->beneficiaryService->getAccountChangeHistory($beneficiary_id);
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
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function edit($id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->detail($id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);

        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param UpdateBeneficiaryRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function update(UpdateBeneficiaryRequest $request, $id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->update($request, $id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }


    /**
     * @param UpdatePersonalInfoRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function updatePersonalInfo(UpdatePersonalInfoRequest $request, $id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->updatePersonalInfo($request, $id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param UpdateContactInfoRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function updateContactInfo(UpdateContactInfoRequest $request, $id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->updateContactInfo($request, $id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param UpdateNomineeInfoRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function updateNomineeInfo(UpdateNomineeInfoRequest $request, $id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->updateNomineeInfo($request, $id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param UpdateAccountInfoRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function updateAccountInfo(UpdateAccountInfoRequest $request, $id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $accountUniqueCheck = Beneficiary::where('id', '!=', $id)
                ->whereNotNull('account_number')
                ->where('account_number', $request->account_number)
                ->select('beneficiary_id')
                ->get();

//            fromRaw('beneficiaries USE INDEX (beneficiaries_account_number_index)')

            if(isset($accountUniqueCheck) && $accountUniqueCheck->isNotEmpty()){
                $beneficiaryIds = $accountUniqueCheck->pluck('beneficiary_id')->implode(', ');

                return response()->json([
                    'success' => false,
                    'message' => "Account Match with Mis ID: ". $beneficiaryIds,
                ], 200);
            }

            $beneficiary = $this->beneficiaryService->updateAccountInfo($request, $id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }


    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function verify(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->verify($request, $id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function rollbackVerification(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->rollbackVerification($request->ids);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param VerifyAllRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function verifyAll(VerifyAllRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->verifyAll($request);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function verifyAccountChange(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {

        try {
             $this->beneficiaryService->verifyAccountChange($request, $id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function approveAccountChange(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {

        try {
            $this->beneficiaryService->approveAccountChange($request, $id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function toWaiting(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $result = $this->beneficiaryService->toWaiting($request, $id);
            return response()->json([
                'success' => $result['success'],
                'message' => $result['message'],
                'data' => $result['data'] ?? null,
                'error' => $result['error'] ?? null,
            ],  $result['success'] ? ResponseAlias::HTTP_OK : ResponseAlias::HTTP_BAD_REQUEST);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function approve(Request $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->approve($request, $id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param ApproveAllRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function approveAll(ApproveAllRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->approveAll($request);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return response()->json([
                'success' => false,
                'message' => $th->getMessage(),
            ], ResponseAlias::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * @param InactiveBeneficiaryRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function inactive(InactiveBeneficiaryRequest $request, $id): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->inactive($request, $id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param DeleteBeneficiaryRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete(DeleteBeneficiaryRequest $request, $id): \Illuminate\Http\JsonResponse
    {
        $beneficiary = Beneficiary::findOrFail($id);
        if(PayrollPaymentCycleDetail::where('beneficiary_id', $beneficiary->beneficiary_id)->where('financial_year_id',getCurrentFinancialYear()->id)->count() > 0){
            return $this->sendError("Unable to delete Due to Payment Made", [], 422);
        }
        try {
            $this->beneficiaryService->delete($request, $id);
            return response()->json([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function deletedList(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->deletedList($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return BeneficiaryResource|\Illuminate\Http\JsonResponse
     */
    public function restore($id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $this->beneficiaryService->restore($id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function restoreInactive($id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $this->beneficiaryService->restoreInactive($id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function restoreExit($id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $this->beneficiaryService->restoreExit($id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function restoreReplace($beneficiary_id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $this->beneficiaryService->restoreReplace($beneficiary_id);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getListForReplace(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->getListForReplace($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param ReplaceBeneficiaryRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BeneficiaryResource
     */
    public function replaceSave(ReplaceBeneficiaryRequest $request, $id): \Illuminate\Http\JsonResponse|BeneficiaryResource
    {
        try {
            $beneficiary = $this->beneficiaryService->replaceSave($request, $id);
            return BeneficiaryResource::make($beneficiary)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function replaceList(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->replaceList($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryReplaceResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function accountChangeList(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->accountChangeList($request);
            return BeneficiaryAccountChangeResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param BeneficiaryExitRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function exitSave(BeneficiaryExitRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->exitSave($request);
            return response()->json([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function exitList(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->exitList($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryExitResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param BeneficiaryShiftingRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function shiftingSave(BeneficiaryShiftingRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->shiftingSave($request);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function shiftingList(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->shiftingList($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryShiftingResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param BeneficiaryLocationShiftingRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function locationShiftingSave(BeneficiaryLocationShiftingRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $this->beneficiaryService->locationShiftingSave($request);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function locationShiftingList(SearchBeneficiaryRequest $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $beneficiaryList = $this->beneficiaryService->locationShiftingList($request);
//            return response()->json($beneficiaryList);
            return BeneficiaryLocationShiftingResource::collection($beneficiaryList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    private function getAdditionalData($ben){
        if(request()->has('additionalFieldIds')){
            $values = $ben->application?->applicationAllowanceValues()->whereIn('allow_addi_fields_id', request()->additionalFieldIds)->with('additionalFieldValue')->get()??[];
            $data = [];
            foreach($values as $value){
                if($value->additionalFieldValue){
                    $data[$value->allow_addi_fields_id] = $value->additionalFieldValue;
                }else{
                    $data[$value->allow_addi_fields_id] = $value->value;
                }
            }
            return $data;
        }

        return [];
    }

    private function getAdditionalFields(){
        if(request()->has('additionalFieldIds')){
            return $this->additionals = AdditionalFields::whereIn('id', request()->additionalFieldIds)->get();
        }
        return [];
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return ResponseAlias
     * @throws MpdfException
     */
    public function getBeneficiaryListPdf(SearchBeneficiaryRequest $request): ResponseAlias
    {
        $beneficiaries = $this->beneficiaryService->list($request, true);
        $beneficiaries = $beneficiaries->map(function($ben){
            $ben->additionalData = $this->getAdditionalData($ben);
            return $ben;
        });
        Log::info(json_encode($beneficiaries));
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

//        $html = view('reports.beneficiary.beneficiary_list', compact('beneficiaries', 'generated_by', 'assign_location'))->render();
//        $pdf = LaravelMpdf::chunkLoadHTML("<tr><td>chunk_html</td></tr>",$html,
//            [
//                'mode' => 'utf-8',
//                'format' => 'A4-L',
//                'title' => __("beneficiary_list.page_title"),
//                'orientation' => 'L',
//                'default_font_size' => 10,
//                'margin_left' => 10,
//                'margin_right' => 10,
//                'margin_top' => 10,
//                'margin_bottom' => 25,
//                'margin_header' => 10,
//                'margin_footer' => 5,
//            ]);


        $data = ['beneficiaries' => $beneficiaries, 'generated_by' => $generated_by, 'assign_location' => $assign_location, 'additionalFields' => $this->getAdditionalFields()];

        $pdf = LaravelMpdf::chunkLoadView("<html-separator/>", 'reports.beneficiary.beneficiary_list', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("beneficiary_list.page_title"),
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 25,
                'margin_header' => 10,
                'margin_footer' => 5,
            ]);

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

//        $fileName = 'উপকারভোগীর_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
//        $pdfPath = public_path("/pdf/$fileName");
//        $pdf->save($pdfPath);
//        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return ResponseAlias
     * @throws MpdfException
     */
    public function getBeneficiaryExitListPdf(SearchBeneficiaryRequest $request): ResponseAlias
    {
        $beneficiaries = $this->beneficiaryService->exitList($request, true);
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
        $pdf = LaravelMpdf::loadView('reports.beneficiary.beneficiary_exit_list', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("beneficiary_exit.page_title"),
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
            ]);

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

//        $fileName = 'উপকারভোগীর_প্রস্থান_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
//        $pdfPath = public_path("/pdf/$fileName");
//        $pdf->save($pdfPath);
//        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return ResponseAlias
     * @throws MpdfException
     */
    public function getBeneficiaryReplaceListPdf(SearchBeneficiaryRequest $request): ResponseAlias
    {
        $beneficiaries = $this->beneficiaryService->replaceList($request, true);
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

//        \Log::info("Beneficiary replace data pdf: ", $data);

        $pdf = LaravelMpdf::loadView('reports.beneficiary.beneficiary_replace_list', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("beneficiary_replace.page_title"),
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
            ]);

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

//        $fileName = 'উপকারভোগী_পরিবর্তন_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
//        $pdfPath = public_path("/pdf/$fileName");
//        $pdf->save($pdfPath);
//        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

    /**
     * @param SearchBeneficiaryRequest $request
     * @return ResponseAlias
     * @throws MpdfException
     */
    public function getBeneficiaryShiftingListPdf(SearchBeneficiaryRequest $request): ResponseAlias
    {
        $beneficiaries = $this->beneficiaryService->shiftingList($request, true);
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
        $pdf = LaravelMpdf::loadView('reports.beneficiary.beneficiary_shifting_list', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("beneficiary_shifting.page_title"),
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
            ]);

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

//        $fileName = 'উপকারভোগী_স্থানান্তর_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
//        $pdfPath = public_path("/pdf/$fileName");
//        $pdf->save($pdfPath);
//        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

    public function getBeneficiaryListExcel(SearchBeneficiaryRequest $request){
        $beneficiariesQuery = $this->beneficiaryService->exportList($request);

        // return $beneficiariesQuery->get();

        // $export = new BeneficiariesExport($beneficiariesQuery);

        $beneficiariesQuery = $this->beneficiaryService->list($request, false, true);

        $export = new BeneficiariesExport($beneficiariesQuery);

        return Excel::download($export, 'beneficiaries.xlsx');

    }

    public function getAllAdditionalFields(Request $request){
        return ['data'=> AdditionalFields::whereHas('allowanceprogram', function($q)use($request){
            if(is_array($request->program_ids))
                $q->whereIn('allowance_programs.id', $request->program_ids);
            else
                $q->where('allowance_programs.id', $request->program_ids);
        })->get()];
    }

    public function uploadUpdateBeneficiariesStatusExcel(Request $request)
    {
        // 1. Validate request
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:204800',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        try {
            \DB::beginTransaction();

            // 2. Read Excel data
            $sheetsData = Excel::toArray(new ExcelImport, $file);
            $beneficiaryIdCol = 0;
            $statusCol = 1;
            $programIdCol = 2;
            $liveVerifyCol = 3;
            $typeIdCol = 4;

            foreach ($sheetsData as $sheet) {
                // 3. Skip empty sheets
                if (empty($sheet)) continue;

                // 4. Validate header row
                $header = $sheet[0] ?? [];
//                \Log::info('Excel Header:', $header);

                if (
                    !isset($header[$beneficiaryIdCol], $header[$statusCol]) ||
                    trim($header[$beneficiaryIdCol]) !== 'MIS_ID' ||
                    trim($header[$statusCol]) !== 'STATUS' ||
                    trim($header[$programIdCol] !== 'PROGRAM_ID') ||
                    trim($header[$liveVerifyCol] !== 'LIVE_VERIFY') ||
                    trim($header[$typeIdCol] !== 'TYPE_ID')
                ) {
                    throw new \Exception("Invalid EXCEL format! Please ensure the headers are correct.");
                }

                $currentFinancialYear = $this->beneficiaryService->currentFinancialYear();
                $currentFinancialYearId = $currentFinancialYear?->id;
                $verified_at = now();

                // 5. Process rows
                collect($sheet)->skip(1)->each(function ($row, $index) use ($beneficiaryIdCol, $statusCol, $programIdCol, $liveVerifyCol, $currentFinancialYearId, $verified_at, $typeIdCol) {
                    // Validate data presence
                    $rowNumber = $index + 2; // Excel row index
                    if (empty($row[$beneficiaryIdCol])) {
                        throw new \Exception("Missing data at row $rowNumber (MIS_ID).");
                    }

                    $misId      = trim($row[$beneficiaryIdCol]);
                    $status     = (int) trim($row[$statusCol]);
                    $programId  = (int) trim($row[$programIdCol]);
                    $liveVerify = trim($row[$liveVerifyCol]);
                    $typeId     = trim($row[$typeIdCol]);

                    $beneficiary = Beneficiary::where('beneficiary_id', $misId)->first();

                    if (!$beneficiary) return;

                    if(!empty($status)){
                        $beneficiary->status = $status;
                    }

                    if(!empty($programId)){
                        $beneficiary->program_id = $programId;
                    }

                    if(!empty($typeId)){
                        if($typeId == "delete"){
                            $beneficiary->type_id = null;
                        } else {
                            $beneficiary->type_id = $typeId;
                        }
                    }

//                    \Log::info("live verify 1 : ", ['data' => $liveVerify]);

                    if ($liveVerify == 1 || $liveVerify == 0 )
                    {
//                        \Log::info("live verify 2 : ", ['data' => $liveVerify]);

                        if (($beneficiary->status == 1 && empty($status)) || $status == 1 ){


                            if ($liveVerify == 1){
                                $logData[] = [
                                    'beneficiary_id' => $beneficiary->id,
                                    'financial_year_id' => $currentFinancialYearId,
                                    'verified_at' => $verified_at,
                                    'verified_by_id' => 10,
                                ];
                                BeneficiaryVerifyLog::upsert($logData, uniqueBy: ['beneficiary_id', 'financial_year_id']);
                            }
                        }

                        if ($liveVerify == 0) {
                            $beneficiary->is_verified = $liveVerify;
                            $beneficiary->last_ver_fin_year_id = $currentFinancialYearId;
                            $beneficiary->last_verified_at = $verified_at;

                            BeneficiaryVerifyLog::where([
                                'beneficiary_id' => $beneficiary->id,
                                'financial_year_id' => $currentFinancialYearId
                            ])->delete();
                        }
                    }

                    $beneficiary->save();

                });
            }

            \DB::commit();

            return response()->json([
                'message' => 'File processed and beneficiaries updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadUpdateBeneficiariesAccountsExcel(Request $request)
    {
        // 1. Validate request
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:204800',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        try {

            // 2. Read Excel data
            $sheetsData = Excel::toArray(new ExcelImport, $file);
            $beneficiaryIdCol = 0;
            $accountNumberCol = 1;
            $accountType = 2;
            $accountOwnerCol = 3;
            $mfsIdCol = 4;
            $bankIdCol = 5;
            $bankBranchIdCol = 6;

            foreach ($sheetsData as $sheet) {
                // 3. Skip empty sheets
                if (empty($sheet)) continue;

                // 4. Validate header row
                $header = $sheet[0] ?? [];
//                \Log::info('Excel Header:', $header);

                if (
                    !isset($header[$beneficiaryIdCol], $header[$accountNumberCol]) ||
                    trim($header[$beneficiaryIdCol]) !== 'MIS_ID' ||
                    trim($header[$accountNumberCol]) !== 'ACCOUNT_NUMBER' ||
                    trim($header[$accountType]) !== 'ACCOUNT_TYPE' ||
                    trim($header[$accountOwnerCol]) !== 'ACCOUNT_OWNERSHIP' ||
                    trim($header[$mfsIdCol]) !== 'MFS_ID' ||
                    trim($header[$bankIdCol]) !== 'BANK_ID' ||
                    trim($header[$bankBranchIdCol]) !== 'BANK_BRANCH_ID'
                ) {
                    throw new \Exception("Invalid EXCEL format! Please ensure the headers are correct.");
                }

                // 5. Process rows
                collect($sheet)->skip(1)->each(function ($row, $index) use ($beneficiaryIdCol, $accountNumberCol, $accountType, $accountOwnerCol, $mfsIdCol, $bankIdCol, $bankBranchIdCol) {
                    // Validate data presence
                    if (empty($row[0]) || empty($row[1])) return;

                    UpdateBeneficiaryAccountJob::dispatch([
                        'beneficiary_id'   => (int) trim($row[$beneficiaryIdCol]),
                        'account_number'   => trim($row[$accountNumberCol]),
                        'account_type'     => (int) trim($row[$accountType]),
                        'account_owner'    => (int) trim($row[$accountOwnerCol]),
                        'mfs_id'           => (int) trim($row[$mfsIdCol]),
                        'bank_id'          => (int) trim($row[$bankIdCol]),
                        'bank_branch_id'   => (int) trim($row[$bankBranchIdCol])
                    ]);

                });
            }

            return response()->json([
                'message' => 'File processed and beneficiaries updated successfully.',
            ], 200);

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadUpdateBeneficiariesLocationExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:204800',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        try {
            $sheetsData = Excel::toArray(new ExcelImport, $file);

            $beneficiaryIdCol = 0;
            $permanentDivisionIdCol = 1;
            $permanentDistrictIdCol = 2;
            $permanentLocationTypeIdCol = 3;
            $permanentCityCorpIdCol = 4;
            $permanentDistrictPouroshavaIdCol = 5;
            $permanentUpazilaIdCol = 6;
            $permanentPouroshavaIdCol = 7;
            $permanentThanaIdCol = 8;
            $permanentUnionIdCol = 9;
            $permanentWardIdCol = 10;
            $permanentPostCodeIdCol = 11;
            $permanentAddressIdCol = 12;

            foreach ($sheetsData as $sheet) {
                if (empty($sheet)) continue;

                $header = $sheet[0] ?? [];

                if (
                    !isset($header[$beneficiaryIdCol], $header[$permanentLocationTypeIdCol]) ||
                    trim($header[$beneficiaryIdCol]) !== 'MIS_ID' ||
                    trim($header[$permanentDivisionIdCol]) !== 'permanent_division_id' ||
                    trim($header[$permanentDistrictIdCol]) !== 'permanent_district_id' ||
                    trim($header[$permanentLocationTypeIdCol]) !== 'permanent_location_type_id' ||
                    trim($header[$permanentCityCorpIdCol]) !== 'permanent_city_corp_id' ||
                    trim($header[$permanentDistrictPouroshavaIdCol]) !== 'permanent_district_pourashava_id' ||
                    trim($header[$permanentUpazilaIdCol]) !== 'permanent_upazila_id' ||
                    trim($header[$permanentPouroshavaIdCol]) !== 'permanent_pourashava_id' ||
                    trim($header[$permanentThanaIdCol]) !== 'permanent_thana_id' ||
                    trim($header[$permanentUnionIdCol]) !== 'permanent_union_id' ||
                    trim($header[$permanentWardIdCol]) !== 'permanent_ward_id' ||
                    trim($header[$permanentPostCodeIdCol]) !== 'permanent_post_code' ||
                    trim($header[$permanentAddressIdCol]) !== 'permanent_address'
                ) {
                    throw new \Exception("Invalid EXCEL format! Please ensure the headers are correct.");
                }

                collect($sheet)->skip(1)->each(function ($row, $index) use (
                    $beneficiaryIdCol,
                    $permanentDivisionIdCol,
                    $permanentDistrictIdCol,
                    $permanentLocationTypeIdCol,
                    $permanentCityCorpIdCol,
                    $permanentDistrictPouroshavaIdCol,
                    $permanentUpazilaIdCol,
                    $permanentPouroshavaIdCol,
                    $permanentThanaIdCol,
                    $permanentUnionIdCol,
                    $permanentWardIdCol,
                    $permanentPostCodeIdCol,
                    $permanentAddressIdCol
                ) {

                    if (empty($row[$beneficiaryIdCol]) || empty($row[$permanentDivisionIdCol]) || empty($row[$permanentDistrictIdCol]) || empty($row[$permanentLocationTypeIdCol]) || empty($row[$permanentWardIdCol])) return;

                    $permanent_location_type_id = (int) trim($row[$permanentLocationTypeIdCol]);

                    if(empty($permanent_location_type_id) && $permanent_location_type_id != 1 && $permanent_location_type_id != 2 && $permanent_location_type_id != 3) return;

                    UpdateBeneficiaryLocationJob::dispatch([
                        'beneficiary_id'                    => (int) trim($row[$beneficiaryIdCol]),
                        'permanent_division_id'             => (int) trim($row[$permanentDivisionIdCol]),
                        'permanent_district_id'             => (int) trim($row[$permanentDistrictIdCol]),

                        'permanent_location_type_id'        => $permanent_location_type_id,
                        'permanent_city_corp_id'            => (int) trim($row[$permanentCityCorpIdCol]),
                        'permanent_district_pourashava_id'  => (int) trim($row[$permanentDistrictPouroshavaIdCol]),
                        'permanent_upazila_id'              => (int) trim($row[$permanentUpazilaIdCol]),

                        'permanent_pourashava_id'           => (int) trim($row[$permanentPouroshavaIdCol]),
                        'permanent_thana_id'                => (int) trim($row[$permanentThanaIdCol]),
                        'permanent_union_id'                => (int) trim($row[$permanentUnionIdCol]),

                        'permanent_ward_id'                 => (int) trim($row[$permanentWardIdCol]),
                        'permanent_post_code'               => (int) trim($row[$permanentPostCodeIdCol]),
                        'permanent_address'                 => trim($row[$permanentAddressIdCol])
                    ]);


                });
            }

        } catch (\Exception $e) {

            return response()->json([
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function uploadUpdateBeneficiariesAdditionalFieldExcel(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|mimes:xlsx,xls|max:204800',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $file = $request->file('file');

        try {
            \DB::beginTransaction();

            $sheetsData = Excel::toArray(new ExcelImport, $file);

            $beneficiaryIdCol = 0;
            $presentEducationInstituteNameCol = 1;
            $addressOfEducationalInstituteCol = 2;
            $rollIdNumberCol = 3;
            $sectionDepartmentCol = 4;
            $classCol = 5;

            foreach ($sheetsData as $sheet) {
                // 3. Skip empty sheets
                if (empty($sheet)) continue;

                // 4. Validate header row
                $header = $sheet[0] ?? [];
//                \Log::info('Excel Header:', $header);

                if (
                    !isset($header[$beneficiaryIdCol] ) ||
                    trim($header[$beneficiaryIdCol]) !== 'MIS_ID' ||
                    trim($header[$presentEducationInstituteNameCol]) !== 'PRESENT_EDUCATION_INSTITUTE_NAME' ||
                    trim($header[$addressOfEducationalInstituteCol]) !== 'ADDRESS_OF_EDUCATIONAL_INSTITUTE' ||
                    trim($header[$rollIdNumberCol] !== 'ROLL_ID_NUMBER') ||
                    trim($header[$sectionDepartmentCol] !== 'SECTION_DEPARTMENT') ||
                    trim($header[$classCol] !== 'CLASS')
                ) {
                    throw new \Exception("InvalidCol EXCEL format! Please ensure the headers are correct.");
                }

                collect($sheet)->skip(1)->each(function ($row, $index) use ($beneficiaryIdCol, $presentEducationInstituteNameCol, $addressOfEducationalInstituteCol, $rollIdNumberCol, $sectionDepartmentCol, $classCol) {
                    // Validate data presence
                    $rowNumber = $index + 2; // Excel row index
                    if (empty($row[$beneficiaryIdCol])) {
                        throw new \Exception("Missing data at row $rowNumber (MIS_ID).");
                    }

                    $misId                          = trim($row[$beneficiaryIdCol]);
                    $beneficiary = Beneficiary::select('id')->where('beneficiary_id', $misId)->first();
                    if (!$beneficiary) return;

                    $presentEducationInstituteName  = trim($row[$presentEducationInstituteNameCol]);
                    $addressOfEducationalInstitute  = trim($row[$addressOfEducationalInstituteCol]);
                    $rollIdNumber                   = trim($row[$rollIdNumberCol]);
                    $sectionDepartment              = trim($row[$sectionDepartmentCol]);
                    $class                          = $row[$classCol] ?? null;

                    if (!empty($presentEducationInstituteName)) {
                        if (strtolower($presentEducationInstituteName) === 'delete') {
                            \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 100)
                                ->delete();
                        } else {
                            $existing = \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 100)
                                ->first();

                            if ($existing) {
                                \DB::table('beneficiaries_allowance_values')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'value' => $presentEducationInstituteName,
                                        'updated_at' => now(),
                                    ]);
                            } else {
                                \DB::table('beneficiaries_allowance_values')
                                    ->insert([
                                        'beneficiary_id' => $beneficiary->id,
                                        'allow_addi_fields_id' => 100,
                                        'value' => $presentEducationInstituteName,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    if (!empty($addressOfEducationalInstitute)) {
                        if (strtolower($addressOfEducationalInstitute) === 'delete') {
                            \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 101)
                                ->delete();
                        } else {
                            $existing = \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 101)
                                ->first();

                            if ($existing) {
                                \DB::table('beneficiaries_allowance_values')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'value' => $addressOfEducationalInstitute,
                                        'updated_at' => now(),
                                    ]);
                            } else {
                                \DB::table('beneficiaries_allowance_values')
                                    ->insert([
                                        'beneficiary_id' => $beneficiary->id,
                                        'allow_addi_fields_id' => 101,
                                        'value' => $addressOfEducationalInstitute,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    if (!empty($rollIdNumber)) {
                        if (strtolower($rollIdNumber) === 'delete') {
                            \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 102)
                                ->delete();
                        } else {
                            $existing = \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 102)
                                ->first();

                            if ($existing) {
                                \DB::table('beneficiaries_allowance_values')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'value' => $rollIdNumber,
                                        'updated_at' => now(),
                                    ]);
                            } else {
                                \DB::table('beneficiaries_allowance_values')
                                    ->insert([
                                        'beneficiary_id' => $beneficiary->id,
                                        'allow_addi_fields_id' => 102,
                                        'value' => $rollIdNumber,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    if (!empty($sectionDepartmentCol)) {
                        if (strtolower($sectionDepartmentCol) === 'delete') {
                            \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 103)
                                ->delete();
                        } else {
                            $existing = \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 103)
                                ->first();

                            if ($existing) {
                                \DB::table('beneficiaries_allowance_values')
                                    ->where('id', $existing->id)
                                    ->update([
                                        'value' => $sectionDepartmentCol,
                                        'updated_at' => now(),
                                    ]);
                            } else {
                                \DB::table('beneficiaries_allowance_values')
                                    ->insert([
                                        'beneficiary_id' => $beneficiary->id,
                                        'allow_addi_fields_id' => 103,
                                        'value' => $sectionDepartmentCol,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                            }
                        }
                    }

                    if (!empty($class)) {
                        $trimmedValue = trim($class);

                        if (strtolower($trimmedValue) === 'delete') {
                            \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 104)
                                ->delete();

                            \DB::table('beneficiaries')
                                ->where('id', $beneficiary->id)
                                ->update(['type_id' => null]);
                        } else {
                            $classColInt = (int) $trimmedValue;

                            $existingRows = \DB::table('beneficiaries_allowance_values')
                                ->where('beneficiary_id', $beneficiary->id)
                                ->where('allow_addi_fields_id', 104)
                                ->get();

                            if ($existingRows->count() > 0) {
                                $keepRow = $existingRows->first();

                                \DB::table('beneficiaries_allowance_values')
                                    ->where('id', $keepRow->id)
                                    ->update([
                                        'allow_addi_field_values_id' => $classColInt,
                                        'updated_at' => now(),
                                    ]);

                                $duplicateIds = $existingRows->pluck('id')->filter(fn($id) => $id !== $keepRow->id)->all();

                                if (!empty($duplicateIds)) {
                                    \DB::table('beneficiaries_allowance_values')
                                        ->whereIn('id', $duplicateIds)
                                        ->delete();
                                }
                            } else {
                                \DB::table('beneficiaries_allowance_values')
                                    ->insert([
                                        'beneficiary_id' => $beneficiary->id,
                                        'allow_addi_fields_id' => 104,
                                        'allow_addi_field_values_id' => $classColInt,
                                        'created_at' => now(),
                                        'updated_at' => now(),
                                    ]);
                            }

                            $typeId = null;

                            if ($classColInt >= 390 && $classColInt <= 394) {
                                $typeId = 25;
                            } elseif ($classColInt >= 395 && $classColInt <= 399) {
                                $typeId = 26;
                            } elseif ($classColInt >= 400 && $classColInt <= 401) {
                                $typeId = 27;
                            } elseif ($classColInt >= 402 && $classColInt <= 407) {
                                $typeId = 42;
                            }

                            if (!is_null($typeId)) {
                                \DB::table('beneficiaries')
                                    ->where('id', $beneficiary->id)
                                    ->update(['type_id' => $typeId]);
                            }
                        }
                    }



                });
            } // end loop

            \DB::commit();

            return response()->json([
                'message' => 'File processed and beneficiaries updated successfully.',
            ], 200);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'message' => 'Error processing file: ' . $e->getMessage(),
            ], 500);
        }
    }

}
