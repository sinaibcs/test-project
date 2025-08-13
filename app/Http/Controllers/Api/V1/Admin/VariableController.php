<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Validator;
use App\Helpers\Helper;
use App\Models\Variable;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\PMTScore\VariableRequest;
use App\Http\Services\Admin\PMTScore\VariableService;
use App\Http\Resources\Admin\PMTScore\VariableResource;

class VariableController extends Controller
{
    use MessageTrait;
    private $VariableService;

    public function __construct(VariableService  $VariableService)
    {
        $this->VariableService = $VariableService;
    }

    /**
     * @OA\Get(
     *     path="/admin/poverty/get/variable",
     *      operationId="getAllVariablePaginated",
     *      tags={"PMT-Score"},
     *      summary="get paginated Variables",
     *      description="get paginated Variables",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of division per page",
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

    public function getAllVariablePaginated(Request $request)
    {
       
        // $searchText = $request->query('searchText');
        // $perPage = $request->query('perPage');
        // $page = $request->query('page');
        // $filterArrayNameEn = [];
        // if ($searchText) {
        //     $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
          
        // }

        // $office = Variable::query()
        //     ->where(function ($query) use ($filterArrayNameEn) {
        //         $query->where($filterArrayNameEn) ;
             
               
        //     })
         
        //     ->latest()
        //     ->with('children')
        //     ->where('parent_id', null) // Variable
        //     ->paginate($perPage, ['*'], 'page');

        // return VariableResource::collection($office)->additional([
        //     'success' => true,
        //     'message' => $this->fetchSuccessMessage,
        // ]);
         $emu = new Variable;
         $variable=$emu->where('parent_id', null)->with('children');
      

        if ($request->has('sortBy') && $request->has('sortDesc')) {
            $sortBy = $request->query('sortBy');

            $sortDesc = $request->query('sortDesc') == true ? 'desc' : 'asc';

            $variable = $variable->orderBy($sortBy, $sortDesc);
        } else {
            $variable = $variable->orderBy('name_en', 'asc');
        }

        $searchValue = $request->input('search');

        if($searchValue)
        {
            $variable->where(function($query) use ($searchValue) {
                $query->where('name_en', 'like', '%' . $searchValue . '%')
                 ->orWhere('name_bn', 'like', '%' . $searchValue . '%');
                  
              
            });

            $itemsPerPage = 5;

            if($request->has('itemsPerPage')) {
                $itemsPerPage = $request->get('itemsPerPage');

                return $variable->paginate($itemsPerPage, ['*'], $request->get('page'));
            }
        }else{
            $itemsPerPage = 5;

            if($request->has('itemsPerPage'))
            {
                $itemsPerPage = $request->get('itemsPerPage');

            }
            
        $variable_final=$variable->paginate($itemsPerPage);
           return VariableResource::collection($variable_final)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
        }
    }

    /**
     * @OA\Get(
     *     path="/admin/poverty/get/sub-variable/variable-list",
     *      operationId="getAllVariableListForSubVariable",
     *      tags={"PMT-Score"},
     *      summary="get paginated Variables",
     *      description="get paginated Variables",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of division per page",
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

    public function getAllVariableListForSubVariable(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $filterArrayNameEn = [];
        // $filterArrayNameBn = [];
        // $filterArrayComment = [];
        // $filterArrayAddress = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            // $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            // $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
        }
        // $menu = Menu::select(
        //     'menus.*',
        //     'permissions.page_url as link'
        // )
        // ->leftJoin('permissions', function ($join) {
        //     $join->on('menus.page_link_id', '=', 'permissions.id');
        // });
        $office = Variable::query()
            ->where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn);
         
                
            })
            ->with('children')
            ->latest()
            ->where('parent_id', null) 
           
            ->paginate($perPage, ['*'], 'page');

        return VariableResource::collection($office)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    /**
     * @OA\Get(
     *     path="/admin/poverty/get/sub-variable",
     *      operationId="getAllSubVariablePaginated",
     *      tags={"PMT-Score"},
     *      summary="get paginated subVariables",
     *      description="get paginated subVariables",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by name",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="perPage",
     *         in="query",
     *         description="number of division per page",
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

    public function getAllSubVariablePaginated(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        // $filterArrayComment = [];
        // $filterArrayAddress = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            // $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
        }
        // $menu = Menu::select(
        //     'menus.*',
        //     'permissions.page_url as link'
        // )
        // ->leftJoin('permissions', function ($join) {
        //     $join->on('menus.page_link_id', '=', 'permissions.id');
        // });
        $office = Variable::query()
            ->where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn)
                    ->orWhere($filterArrayNameBn)
                    // ->orWhere($filterArrayComment)
                    // ->orWhere($filterArrayAddress)
                ;
            })
            // ->with('assign_location.parent.parent.parent', 'assign_location.locationType')
            ->whereNotNull('parent_id') // Sub Variable
            ->with('parent')
            ->latest()
            ->paginate($perPage, ['*'], 'page');

        return VariableResource::collection($office)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/variable/filter",
     *      operationId="filterVariable",
     *      tags={"PMT-Score"},
     *      summary="filter a povertyVariable",
     *      description="filter a povertyVariable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(

     *                    @OA\Property(
     *                      property="financial_year_id",
     *                      description="filter type",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="type",
     *                      description="filter type",
     *                      type="integer",
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    public function getFiltered(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $financial_year_id = null;
        $type = null;
        if ($request->has('financial_year_id')) {
            $financial_year_id = $request->financial_year_id;
        }
        if ($request->has('type')) {
            $type = $request->type;
        }

        if ($request->has('financial_year_id') && $request->has('type')) {
            if (!$this->check_if_exists($financial_year_id, $type)) {
                // entry all division/district values with that financial ID and load the table table to be editable
                $this->insertVariable($financial_year_id, $type);
            }
        }

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayComment = [];
        $filterArrayAddress = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
        }
        $office = Variable::query()
            ->where(function ($query) use ($filterArrayNameEn, $financial_year_id, $type) {
                $query->where($filterArrayNameEn)
                    ->where('financial_year_id', $financial_year_id)
                    ->where('type', $type)
                    // ->orWhere($filterArrayNameBn)
                    // ->orWhere($filterArrayComment)
                    // ->orWhere($filterArrayAddress)
                ;
            })
            ->with('assign_location.parent.parent.parent', 'assign_location.locationType')
            ->latest()
            ->paginate($perPage, ['*'], 'page');

        return VariableResource::collection($office)->additional([
            'success' => true,
            // 'message' => $this->fetchSuccessMessage,
        ]);
    }

    private function check_if_exists($financial_year_id, $type)
    {
        $data = Variable::get()
            ->where('financial_year_id', $financial_year_id)
            ->where('type', $type);

        if (count($data) > 0) {
            return true;
        } else {
            return false;
        }
    }

    // private function insertVariable($financial_year_id, $type)
    // {
    //     // THIS FUNCTION POVERTY CUT OFF INSERT 
    //     // IF NOT EXISTED FOR A SPECIFIC FINANCIAL YEAR

    //     if ($type == 0) {

    //         // ALL OVER BANGLADESH CUTTOFF
    //         $poverty_score_cut_offs = new Variable;
    //         $poverty_score_cut_offs->type         = $type;
    //         $poverty_score_cut_offs->financial_year_id  = $financial_year_id;
    //         $poverty_score_cut_offs->score        = 0;
    //         $poverty_score_cut_offs->default      = 0;
    //         $poverty_score_cut_offs->save();
    //         // END ALL OVER BANGLADESH CUTTOFF

    //     } else {
    //         if ($type == 1) {
    //             $locations = Location::get()->where('type', 'division'); // DIVISION CUTTOFF
    //         }
    //         if ($type == 2) {
    //             $locations = Location::get()->where('type', 'district'); //DISTRICT CUTTOFF
    //         }

    //         foreach ($locations as $value) {

    //             $poverty_score_cut_offs = new Variable;
    //             $poverty_score_cut_offs->type         = $type;
    //             $poverty_score_cut_offs->location_id  = $value['id'];
    //             $poverty_score_cut_offs->financial_year_id  = $financial_year_id;
    //             $poverty_score_cut_offs->score        = 0;
    //             $poverty_score_cut_offs->default      = 0;
    //             $poverty_score_cut_offs->save();
    //         }
    //     }
    // }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/variable/update",
     *      operationId="updateVariable",
     *      tags={"PMT-Score"},
     *      summary="update a povertyVariable",
     *      description="update a povertyVariable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(

     *                    @OA\Property(
     *                      property="id",
     *                      description="id",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="type",
     *                      description="update type",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="division_id",
     *                      description="update division_id",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="location_id",
     *                      description="update location_id",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="score",
     *                      description="score",
     *                      type="float",
     *
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    public function updateVariable(VariableRequest $request)
    {
       

        try {
           
            $BeforeUpdate = Variable::find($request->id);
            $Variable = $this->VariableService->updateVariable($request);
            Helper::activityLogUpdate($Variable,$BeforeUpdate,"Variable","Variable Update !");
         
            return VariableResource::make($Variable)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/variable/insert",
     *      operationId="insertVariable",
     *      tags={"PMT-Score"},
     *      summary="insert a povertyVariable",
     *      description="insert a povertyVariable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(

     *                    @OA\Property(
     *                      property="name_en",
     *                      description="insert name_en",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="field_type",
     *                      description="insert field_type",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="score",
     *                      description="insert score",
     *                      type="integer",
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    public function insertVariable(VariableRequest $request)
    {

        try {
            $Variable = $this->VariableService->createVariable($request);
             Helper::activityLogInsert($Variable,'','Variable','Variable Created !');
            
            return VariableResource::make($Variable)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/sub-variable/insert",
     *      operationId="insertSubVariable",
     *      tags={"PMT-Score"},
     *      summary="insert a povertyVariable",
     *      description="insert a povertyVariable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(

     *                    @OA\Property(
     *                      property="variable_id",
     *                      description="Variable ID",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="name_en",
     *                      description="sub variable name_en",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="score",
     *                      description="score",
     *                      type="integer",
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    public function insertSubVariable(VariableRequest $request)
    {

        try {
            $Variable = $this->VariableService->createSubVariable($request);
            activity("Variable")
                ->causedBy(auth()->user())
                ->performedOn($Variable)
                ->log('Variable Created !');
            return VariableResource::make($Variable)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/sub-variable/update",
     *      operationId="updateSubVariable",
     *      tags={"PMT-Score"},
     *      summary="update a SubVariable",
     *      description="update a SubVariable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(

     *                    @OA\Property(
     *                      property="id",
     *                      description="id",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="variable_id",
     *                      description="Variable ID",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="name_en",
     *                      description="sub variable name_en",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="score",
     *                      description="score",
     *                      type="integer",
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    public function updateSubVariable(VariableRequest $request)
    {

        try {
            $BeforeUpdate = Variable::find($request->id);
            $Variable = $this->VariableService->updateSubVariable($request);
            Helper::activityLogUpdate($Variable,$BeforeUpdate,"Variable","Variable Update !");
         
          
            return VariableResource::make($Variable)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/variable/destroy",
     *      operationId="destroyVariable",
     *      tags={"PMT-Score"},
     *      summary="delete Variable",
     *      description="delete Variable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                    @OA\Property(
     *                      property="id",
     *                      description="id",
     *                      type="integer",
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    // public function destroyVariable(Request $request)
    // {
       

    //     $validator = Validator::make(['id' => $request->delete_id], [
    //         'id' => 'required|exists:variables,id,deleted_at,NULL',
    //     ]);

    //     $validator->validated();
    //     // Delete sub-variables directly from the query builder
    // $emu=Variable::whereParentId($request->delete_id)->delete();
   

    // // Delete the main variable
    // $variable = Variable::find($request->delete_id);

    // if ($variable) {
    //     $variable->delete();
    // }

    //     activity("Variable")
    //         ->causedBy(auth()->user())
    //         ->log('Variable Deleted!!');
    //     return $this->sendResponse($variable, $this->deleteSuccessMessage, Response::HTTP_OK);
    // }
    



public function destroyVariable(Request $request)
{

        // Validate the request
        $validator = Validator::make(['id' => $request->delete_id], [
            'id' => 'required|exists:variables,id,deleted_at,NULL',
        ]);
        
        $validator->validated();

        // Check if the variable has associated poverty values
        $variable = Variable::with('povertyValues')->find($request->delete_id);

        if ($variable && $variable->povertyValues->isNotEmpty()) {
            // If it has associated poverty values, prevent deletion
           
            return response()->json([
                        'success' => false,
                        'message' => 'Variable has associated poverty values and cannot be deleted.',
                    ]);
        }

        // Delete sub-variables directly from the query builder
        $emu = Variable::whereParentId($request->delete_id)->delete();

        // Delete the main variable
        if ($variable) {
            $variable->delete();
        }

       Helper::activityLogDelete($variable,'','Variable','Variable Deleted!!');
        return response()->json([
                        'success' => true,
                        'message' => 'Delete Success',
                    ]);
  
}

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/sub-variable/destroy",
     *      operationId="destroySubVariable",
     *      tags={"PMT-Score"},
     *      summary="delete Sub Variable",
     *      description="delete Sub Variable",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                    @OA\Property(
     *                      property="id",
     *                      description="id",
     *                      type="integer",
     *                   ),
     *                 ),
     *             ),
     *
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
     *
     */

    public function destroySubVariable()
    {

        $validator = Validator::make(['id' => request()->id], [
            'id' => 'required|exists:variables,id,deleted_at,NULL',
        ]);

        $validator->validated();

        $variable = Variable::whereId(request()->id)->first();

        // check if variable has any child if yes then return exception else delete
        if ($variable->children->count() > 0) {

            return $this->sendError('This record cannot be deleted because it is linked to other data.', [], 500);
        }


        if ($variable) {
            $variable->delete();
        }

        activity("Variable")
            ->causedBy(auth()->user())
            ->log('Variable Deleted!!');
        return $this->sendResponse($variable, $this->deleteSuccessMessage, Response::HTTP_OK);
    }
}
