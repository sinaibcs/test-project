<?php

namespace App\Http\Controllers\Api\V1\Admin\Budget;

use App\Helpers\Helper;
use App\Models\Allotment;
use App\Imports\ExcelImport;
use Illuminate\Http\Request;
use App\Models\AllowanceProgram;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Crypt;
use App\Exports\AllotmentFormatExport;
use Illuminate\Support\Facades\Validator;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Resources\Admin\Budget\BudgetResource;
use App\Http\Requests\Admin\Budget\UpdateBudgetRequest;
use App\Http\Resources\Admin\Allotment\AllotmentResource;
use App\Http\Requests\Admin\Allotment\UpdateAllotmentRequest;
use App\Http\Resources\Admin\Allotment\AllotmentListResource;
use App\Http\Services\Admin\BudgetAllotment\AllotmentService;
use Symfony\Component\HttpFoundation\Response as ResponseAlias;
use App\Http\Resources\Admin\Allotment\AllotmentSummaryResource;

class AllotmentController extends Controller
{
    use MessageTrait;

    /**
     * @var AllotmentService
     */
    private AllotmentService $allotmentService;

    /**
     * @param AllotmentService $allotmentService
     */
    public function __construct(AllotmentService $allotmentService)
    {
        $this->allotmentService = $allotmentService;
    }

    public function summary(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $allotmentList = $this->allotmentService->summary($request);
//            return response()->json($beneficiaryList);
            return AllotmentSummaryResource::collection($allotmentList)->additional([
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
    public function list(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $allotmentList = $this->allotmentService->list($request);
//            return response()->json($beneficiaryList);
            return AllotmentResource::collection($allotmentList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $program_id
     * @param $financial_year_id
     * @param $location_id
     * @return \Illuminate\Http\JsonResponse|BudgetResource
     */
    public function getList($program_id, $financial_year_id, $location_id = null): \Illuminate\Http\JsonResponse|BudgetResource
    {
        try {
            $budget = $this->allotmentService->getList($program_id, $financial_year_id, $location_id);
            return response()->json([
                'data' => $budget,
                'success' => false,
                'message' => $this->notFoundMessage,
            ], ResponseAlias::HTTP_OK);

        } catch (\Throwable $th) {
            // throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    
    public function downloadFormat($program_id, $financial_year_id)
    {
        $allowanceProgram = AllowanceProgram::find($program_id);
        return Excel::download(new AllotmentFormatExport($allowanceProgram, $financial_year_id), "allowance-{$allowanceProgram->name_en}.xlsx");
    }

    public function uploadExcel(Request $request){
        $validator = Validator::make($request->all(), [
            // 'program_id' => [
            //     'required',
            //     'exists:allowance_programs,id',
            // ],
            // 'financial_year_id' => 'required|integer|exists:financial_years,id',
            'file' => 'required|mimes:xlsx,xls|max:204800',
        ]);
        $file = $request->file('file');
        \DB::beginTransaction();
        try{
            $sheetsData = Excel::toArray(new ExcelImport, $file);
            $benIndex = 8;
            $addiBenIndex = 9;
            foreach($sheetsData as $sheet){
                foreach($sheet as $key => $row){
                    if($key == 0){
                        if($row[$benIndex] != 'মোট উপকারভোগী' || $row[$addiBenIndex] != 'অতিরিক্ত উপকারভোগী'){
                            throw new \Exception("Invalid EXCEL format! please upload the correct format for this program.");
                        }
                    }else{
                        if($row[0] === null){
                            break;
                        }
                        $data = json_decode(Crypt::decrypt($row[0]));
                        
                        $rowBen = $row[$benIndex];
                        if($rowBen === null){
                            throw new \Exception("One or more rows have missing data. Please check the file before uploading");
                        }
                        $rowBen = Helper::banglaToEnglish($rowBen);
                        if(!is_numeric($rowBen)){
                            throw new \Exception("One or more rows have invalid data. Please check the file before uploading");
                        }
                        $rowAditionalBen = $row[$addiBenIndex];
                        // if($rowAditionalBen === null){
                        //     throw new \Exception("One or more rows have missing data. Please check the file before uploading");
                        // }
                        // $rowAditionalBen = Helper::banglaToEnglish($rowAditionalBen);
                        // if(!is_numeric($rowAditionalBen)){
                        //     throw new \Exception("One or more rows have invalid data. Please check the file before uploading");
                        // }
                        $numberOfBen = floor($rowBen);
                        $numberOfAddiBen = floor($rowAditionalBen??0);

                        $allotment = Allotment::find($data->id);
                        $perBen = $allotment->total_amount / $allotment->total_beneficiaries;
                        $allotment->regular_beneficiaries = $numberOfBen;
                        $allotment->additional_beneficiaries = $numberOfAddiBen;
                        $allotment->total_beneficiaries = $numberOfBen + $numberOfAddiBen;
                        $allotment->total_amount = $perBen * $allotment->total_beneficiaries;
                        $allotment->save();
                    }
                }
            }
            \DB::commit();
            return response()->json([
                'message' => 'File processed successfully',
            ], 200);
        }catch(\Exception $e){
            \DB::rollBack();
            return response()->json(['message' => 'Error processing file: ' . $e->getMessage()], 500);
        }
    }

    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function navigate(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $allotmentList = $this->allotmentService->navigate($request);
//            return response()->json($allotmentList);
            return AllotmentListResource::collection($allotmentList)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @param $id
     * @return BudgetResource|\Illuminate\Http\JsonResponse
     */
    public function show($id): \Illuminate\Http\JsonResponse|AllotmentResource
    {
        try {
            $allotment = $this->allotmentService->get($id);
            if ($allotment) {
                return AllotmentResource::make($allotment)->additional([
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
     * @param UpdateBudgetRequest $request
     * @param $id
     * @return BudgetResource|\Illuminate\Http\JsonResponse
     */
    public function update(UpdateAllotmentRequest $request, $id): \Illuminate\Http\JsonResponse|AllotmentResource
    {
        try {
            $data = $this->allotmentService->update($request, $id);
            activity("Budget")
                ->causedBy(auth()->user())
                ->performedOn($data)
                ->log('Budget Updated!');
            return AllotmentResource::make($data)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
    public function updateMany(Request $request)
    {
        try {
            $this->allotmentService->updateMany($request->items);
            return [
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ];
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
            $this->allotmentService->delete($id);
            activity("Allotment")
                ->causedBy(auth()->user())
                ->log('Allotment Deleted!!');
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
     * @return ResponseAlias
     */
    public function report(Request $request): ResponseAlias
    {
        $allotmentList = $this->allotmentService->list($request, true);
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
        $data = ['allotmentList' => $allotmentList, 'generated_by' => $generated_by, 'assign_location' => $assign_location];
        $pdf = LaravelMpdf::loadView('reports.allotment.allotment_list', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-L',
                'title' => __("allotment.page_title"),
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

//        $fileName = 'বাজেট_তালিকা_' . now()->timestamp . '_' . auth()->id() . '.pdf';
//        $pdfPath = public_path("/pdf/$fileName");
//        $pdf->save($pdfPath);
//        return $this->sendResponse(['url' => asset("/pdf/$fileName")]);
    }

    public function getReport($program_id, $financial_year_id, $location_id = null): \Illuminate\Http\JsonResponse|\Symfony\Component\HttpFoundation\StreamedResponse
    {
        try {
            $allotmentList = $this->allotmentService->getList($program_id, $financial_year_id, $location_id);
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
            $data = ['allotmentList' => $allotmentList, 'generated_by' => $generated_by, 'assign_location' => $assign_location];
            $pdf = LaravelMpdf::loadView('reports.allotment.allotment_list', $data, [],
                [
                    'mode' => 'utf-8',
                    'format' => 'A4-L',
                    'title' => __("allotment.page_title"),
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

        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

}
