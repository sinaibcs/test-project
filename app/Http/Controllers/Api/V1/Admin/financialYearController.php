<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Systemconfig\FinanacialYear\FinancialRequest;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Http\Services\Admin\Systemconfig\SystemconfigService;
use App\Http\Traits\MessageTrait;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;

class financialYearController extends Controller
{
    use MessageTrait;

    private $systemconfigService;

    public function __construct(SystemconfigService $systemconfigService)
    {
        $this->systemconfigService = $systemconfigService;
    }

    /**
     * @OA\Get(
     *     path="/admin/financial-year/get",
     *      operationId="getFinancialPaginated",
     *      tags={"ADMIN-FINANCIAL-YEAR"},
     *      summary="get paginated financial-year",
     *      description="get paginated financial-year",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of financial-year per page",
     *         @OA\Schema(type="integer")
     *     ),
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="page number",
     *         @OA\Schema(type="integer")
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     * )
     */

    public function getFinancialPaginated(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');


        $filterFinancialYear = [];
        $filterStartDate = [];
        $filterEndDate = [];


        if ($searchText) {
            $filterFinancialYear[] = ['financial_year', 'LIKE', '%' . $searchText . '%'];
            $filterStartDate[] = ['start_date', 'LIKE', '%' . $searchText . '%'];
            $filterEndDate[] = ['end_date', 'LIKE', '%' . $searchText . '%'];
            if ($searchText != null) {
                $page = 1;
            }
        }
        $financial = FinancialYear::query()
            ->where(function ($query) use ($filterFinancialYear, $filterStartDate, $filterEndDate) {
                $query->where($filterFinancialYear)
                    ->orWhere($filterStartDate)
                    ->orWhere($filterEndDate);
            })
            ->where('status', 1)
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);


        return FinancialResource::collection($financial)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    public function getFinancialYears(Request $request)
    {
        $query = FinancialYear::query();
        if (!$request->query('type') == 'all')
            $query = $query->where('status', '!=', 2);
        $financial = $query->orderBy('start_date', 'desc')
            ->limit(20)
            ->get();
        return FinancialResource::collection($financial)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/financial-year/insert",
     *      operationId="insertFinancialYear",
     *      tags={"ADMIN-FINANCIAL-YEAR"},
     *      summary="insert a financial-year",
     *      description="insert a financial-year",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="financial_year",
     *                      description="financial year. ex: 2023-2024",
     *                      type="text",
     *                   ),
     *                 ),
     *             ),
     *         ),
     *
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=201,
     *          description="Successful Insert operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     */
    public function insertFinancialYear(FinancialRequest $request)
    {

        try {
            $financial = $this->systemconfigService->createFinancialYear($request);
            activity("Financial-Year")
                ->causedBy(auth()->user())
                ->performedOn($financial)
                ->log('Financial Year Created !');
            return FinancialResource::make($financial)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/admin/financial-year/destroy/{id}",
     *      operationId="destroyFinancial",
     *      tags={"ADMIN-FINANCIAL-YEAR"},
     *      summary=" destroy financial-year",
     *      description="Returns financial-year destroy by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of financial-year to return",
     *         in="path",
     *         name="id",
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Unauthenticated",
     *      ),
     *      @OA\Response(
     *          response=403,
     *          description="Forbidden"
     *      ),
     *      @OA\Response(
     *          response=404,
     *          description="Not Found!"
     *      ),
     *      @OA\Response(
     *          response=422,
     *          description="Unprocessable Entity"
     *      ),
     *     )
     */
    public function destroyFinancial($id)
    {


        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:financial_years,id,deleted_at,NULL',
        ]);

        $validator->validated();

        $financial_years = FinancialYear::whereId($id)->whereStatus(0)->first();
        if ($financial_years) {
            $financial_years->delete();
        }
        activity("Financial-Year")
            ->causedBy(auth()->user())
            ->log('Financial Year Deleted!!');
        return $this->sendResponse($financial_years, $this->deleteSuccessMessage, Response::HTTP_OK);
    }
}
