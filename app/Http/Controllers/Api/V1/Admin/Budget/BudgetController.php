<?php

namespace App\Http\Controllers\Api\V1\Admin\Budget;

use App\Exports\AreaAndOfficeWiseBudgetFormatExport;
use Crypt;
use App\Models\Budget;
use Log;
use Mockery\Exception;
use App\Helpers\Helper;
use App\Imports\ExcelImport;
use Illuminate\Http\Request;
use App\Models\FinancialYear;
use Illuminate\Validation\Rule;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use Illuminate\Support\Facades\DB;
use App\Models\AllowanceProgramAge;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use App\Models\AllowanceProgramAmount;
use Illuminate\Support\Facades\Validator;
use App\Exports\AreaWiseBudgetFormatExport;
use App\Exports\OfficeWiseBudgetFormatExport;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Resources\Admin\Budget\BudgetResource;
use App\Http\Requests\Admin\Budget\StoreBudgetRequest;
use App\Http\Requests\Admin\Budget\UpdateBudgetRequest;
use App\Http\Requests\Admin\Budget\ApproveBudgetRequest;
use App\Http\Resources\Admin\Budget\BudgetDetailResource;
use App\Http\Services\Admin\BudgetAllotment\BudgetService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;

/**
 *
 */
class BudgetController extends Controller
{
    use MessageTrait;

    /**
     * @var BudgetService
     */
    private BudgetService $budgetService;

    /**
     * @param BudgetService $budgetService
     */
    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    /**
     * @return FinancialResource|\Illuminate\Http\JsonResponse
     */
    public function getCurrentFinancialYear()
    {
        try {
            $financialYear = $this->budgetService->currentFinancialYear();
            if (!$financialYear)
                throw new Exception('No active financial year found!', ResponseAlias::HTTP_NOT_FOUND);
            return FinancialResource::make($financialYear)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection|\Illuminate\Http\JsonResponse
     */
    public function getBudgetFinancialYear()
    {
        try {
            $financialYear = $this->budgetService->budgetFinancialYear();
            return FinancialResource::collection($financialYear)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);

        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUserLocation(): \Illuminate\Http\JsonResponse
    {
        $uerLocation = $this->budgetService->getUserLocation();
        return response()->json([
            'data' => $uerLocation,
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ], ResponseAlias::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function list(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $budgetList = $this->budgetService->list($request);
//            return response()->json($beneficiaryList);
            return BudgetResource::collection($budgetList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param StoreBudgetRequest $request
     * @return BudgetResource|\Illuminate\Http\JsonResponse
     */
    public function add(StoreBudgetRequest $request): \Illuminate\Http\JsonResponse|BudgetResource
    {
        try {
            $budget = $this->budgetService->save($request);
            return BudgetResource::make($budget)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return BudgetResource|\Illuminate\Http\JsonResponse
     */
    public function show($id): \Illuminate\Http\JsonResponse|BudgetResource
    {
        try {
            $budget = $this->budgetService->get($id);
            if ($budget) {
                return BudgetResource::make($budget)->additional([
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
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $budget_id
     * @param $location_id
     * @return \Illuminate\Http\JsonResponse|BudgetResource
     */
    public function getDetailBudget($budget_id, $location_id = null): \Illuminate\Http\JsonResponse|BudgetResource
    {
        try {
            $budget = $this->budgetService->getDetailBudget($budget_id, $location_id);
            return response()->json([
                'data' => $budget,
                'success' => false,
                'message' => $this->notFoundMessage,
            ], ResponseAlias::HTTP_OK);

        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param UpdateBudgetRequest $request
     * @param $id
     * @return BudgetResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateBudgetRequest $request, $id): \Illuminate\Http\JsonResponse|BudgetResource
    {
        try {
            $budget = Budget::findOrFail($id);
            if (!$budget->process_flag) {
                throw new Exception('Budget not yet processed', ResponseAlias::HTTP_BAD_REQUEST);
            } elseif ($budget->is_approved) {
                throw new Exception('Budget Already Approved', ResponseAlias::HTTP_BAD_REQUEST);
            }
            $data = $this->budgetService->update($request, $id);
            return BudgetResource::make($data)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param ApproveBudgetRequest $request
     * @param $id
     * @return \Illuminate\Http\JsonResponse|BudgetResource
     */
    public function approve(ApproveBudgetRequest $request, $id): \Illuminate\Http\JsonResponse|BudgetResource
    {
        try {
            $beforeUpdate = Budget::findOrFail($id);
            if ($beforeUpdate->process_flag <= 0) {
                throw new Exception('Budget not yet processed', ResponseAlias::HTTP_BAD_REQUEST);
            } elseif ($beforeUpdate->is_approved) {
                throw new Exception('Budget Already Approved', ResponseAlias::HTTP_BAD_REQUEST);
            }
            $data = $this->budgetService->approve($request, $id);
//            $afterUpdate = Budget::findOrFail($id);
            Helper::activityLogUpdate($data, $beforeUpdate, "Budget", "Budget Approved!");
            return BudgetResource::make($data)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function delete($id): \Illuminate\Http\JsonResponse
    {
        try {
            $budget = Budget::findOrFail($id);
            if ($budget->is_approved == 1) {
                throw new Exception('Budget Already Approved', ResponseAlias::HTTP_BAD_REQUEST);
            }
            $this->budgetService->delete($id);
            Helper::activityLogDelete($budget, '', 'Budget', 'Budget Deleted!!');
            return response()->json([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getProjection(Request $request): \Illuminate\Http\JsonResponse
    {
        try {
            $data = $this->budgetService->getProjection($request);
            return response()->json([
                'data' => $data,
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function getFormatExcel(Request $request)
    {
        $program = AllowanceProgram::find($request->program_id);
        $financialYear = FinancialYear::find($request->financial_year_id);
        $year = $financialYear->financial_year;
        if($this->budgetService->distPauroIsOfficeWise($program)){
            return Excel::download(new AreaAndOfficeWiseBudgetFormatExport($program), "budget-{$program->name_en}-{$year}.xlsx");
        }
        if($program->is_office_wise_budget == 1){
            return Excel::download(new OfficeWiseBudgetFormatExport($program), "budget-{$program->name_en}-{$year}.xlsx");
        }else{
            return Excel::download(new AreaWiseBudgetFormatExport($program), "budget-{$program->name_en}-{$year}.xlsx");
        }
    }

    public function uploadExcel(Request $request)
    {
        // return ini_get('upload_max_filesize');
         // Validate the file
         $validator = Validator::make($request->all(), [
            'program_id' => [
                'required',
                'exists:allowance_programs,id',
                Rule::unique('budgets', 'program_id')
                    ->where('financial_year_id', $request->input('financial_year_id')),
            ],
            'financial_year_id' => 'required|integer|exists:financial_years,id',
            'file' => 'required|mimes:xlsx,xls|max:204800',
        ]);

        $program = AllowanceProgram::find($request->program_id);
        $dpiow = $this->budgetService->distPauroIsOfficeWise($program);
        $financialYear = FinancialYear::find($request->financial_year_id);


        // $multiplyBy = 1;
        // if($program->payment_cycle == 'Quarterly'){
        //     $multiplyBy = 3 * 4;
        // }elseif($program->payment_cycle == 'Monthly'){
        //     $multiplyBy = 1 * 12;
        // }elseif($program->payment_cycle == 'Half Yearly'){
        //     $multiplyBy = 6 * 2;
        // }elseif($program->payment_cycle == 'Yearly'){
        //     $multiplyBy = 1 * 1;
        // }
        // $per_beneficiary_amount *= 12;

        if ($validator->fails()) {
            return response()->json(['message' => $validator->errors()->first()], 422);
        }

        $file = $request->file('file');
        // return $file;
        DB::beginTransaction();
        try {
            $budget = $this->budgetService->saveForFileUpload($request->only(['program_id', 'financial_year_id']));
            // Extract all sheet data dynamically
            $sheetsData = Excel::toArray(new ExcelImport, $file);
            // return $sheetsData;
            $isOfficeWise = (bool) $program->is_office_wise_budget;
            if($dpiow){
                $valueIndex = 7;
            }elseif($isOfficeWise){
                $valueIndex = 7;
            }else{
                $valueIndex = 6;
            }
            $budgetDetails = [];
            foreach($sheetsData as $sheet){
                foreach($sheet as $key => $row){
                    if($key == 0){
                        if($row[$valueIndex] != 'মোট উপকারভোগী'){
                            throw new Exception("Invalid EXCEL format! please upload the correct format for this program.");
                        }
                    }else{
                        if($row[0] === null){
                            break;
                        }
                        $data = json_decode(Crypt::decrypt($row[0]));
                        if($isOfficeWise && $data->office_id == null){
                            throw new Exception("Invalid EXCEL file! please make sure you are uploading the correct file for this program.");
                        }
                        $rawBen = $row[$valueIndex];
                        if($rawBen === null){
                            throw new Exception("One or more rows have missing data. Please check the file before uploading");
                        }
                        $rawBen = Helper::banglaToEnglish($rawBen);
                        if(!is_numeric($rawBen)){
                            throw new Exception("One or more rows have invalid data. Please check the file before uploading");
                        }
                        $numberOfBen = floor($rawBen);
                        if (!empty($data->type_id??null)) {
                            $per_beneficiary_amount = AllowanceProgramAmount::where('allowance_program_id', $program->id)
                                ->where('type_id', $data->type_id)
                                ->max('amount');
                        }elseif ($program->is_age_limit) {
                            $per_beneficiary_amount = AllowanceProgramAge::where('allowance_program_id', $program->id)->max('amount');
                        } else {
                            $per_beneficiary_amount = AllowanceProgramAmount::where('allowance_program_id', $program->id)->max('amount');
                        }                        
                         $budgetDetails[] = [
                            // 'budget_id' => $budget->id,
                            'total_beneficiaries' => $numberOfBen,
                            'total_amount' => $per_beneficiary_amount * 12 * $numberOfBen,
                            'division_id' => $data->division_id,
                            'district_id' => $data->district_id,
                            'location_type' => $data->location_type,
                            'city_corp_id' => $data->city_corp_id,
                            'upazila_id' => $data->upazila_id,
                            'district_pourashava_id' => $data->district_pourashava_id,
                            'thana_id' => $data->thana_id??null,
                            'pourashava_id' => $data->pourashava_id??null,
                            'union_id' => $data->union_id??null,
                            'ward_id' => $data->ward_id??null,
                            'location_id' => $data->location_id,
                            'type_id' => $data->type_id??null,
                            'office_id' => $data->office_id??null,
                            'created_at' => now(),
                        ];
                    }
                }
            }

            $budget->budgetDetail()->createMany($budgetDetails);
            \DB::commit();

            return response()->json([
                'message' => 'File processed successfully',
                // 'data' => $budgetDetails, // Contains all sheet data
                'budget_id' => $budget->id
            ], 200);
        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json(['message' => 'Error processing file: ' . $e->getMessage()], 500);
        }
    }



    /**
     * @param $budget_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function detailList($budget_id, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $budget = Budget::find($budget_id);
            if (!$budget) {
                return response()->json([
                    'success' => false,
                    'message' => $this->notFoundMessage,
                ], ResponseAlias::HTTP_OK);
            }
            $budgetDetailList = $this->budgetService->detailList($budget_id, $request);
//            return response()->json($budgetList);
            return BudgetDetailResource::collection($budgetDetailList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $budget_id
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function detailUpdate($budget_id, Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $budget = Budget::find($budget_id);
            if (!$budget) {
                return response()->json([
                    'success' => false,
                    'message' => $this->notFoundMessage,
                ], ResponseAlias::HTTP_OK);
            }
            $this->budgetService->detailUpdate($budget_id, $request);
            return response()->json([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ], ResponseAlias::HTTP_OK);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $budget_id
     * @param Request $request
     * @return ResponseAlias
     */
    public function getBudgetDetailListPdf($budget_id, Request $request): ResponseAlias
    {
        $budgetDetailList = $this->budgetService->detailList($budget_id, $request, true);
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
        $data = ['budgetDetailList' => $budgetDetailList, 'generated_by' => $generated_by, 'assign_location' => $assign_location];
        $view = view('reports.budget.budget_detail_list', $data)->render();
        // return $view;
        $chunks = str_split($view, 500000);
        $pdf = LaravelMpdf::loadHtml('',
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("budget.page_title"),
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 5,
            ]);

            foreach ($chunks as $chunk) {
                $pdf->getMpdf()->WriteHTML($chunk);
            }

        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf;charset=utf-8',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

//        $fileName = 'বাজেট_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
//        $pdfPath = public_path("/pdf/$fileName");
//        $pdf->save($pdfPath);
//        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

}
