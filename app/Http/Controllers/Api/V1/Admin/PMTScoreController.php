<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Validator;
use App\Models\Lookup;
use App\Helpers\Helper;
use App\Models\Location;
use App\Models\PMTScore;
use Illuminate\Http\Request;

use Illuminate\Http\Response;
use App\Http\Traits\UserTrait;
use App\Http\Traits\LookupTrait;
use App\Http\Traits\MessageTrait;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Lookup\LookupRequest;
use App\Http\Services\Admin\Lookup\LookupService;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Requests\Admin\PMTScore\PMTScoreRequest;
use App\Http\Services\Admin\PMTScore\PMTScoreService;
use App\Http\Requests\Admin\Lookup\LookupUpdateRequest;
use App\Http\Resources\Admin\PMTScore\PMTScoreResource;
use App\Http\Requests\Admin\PMTScore\DistrictFixedEffectRequest;

class PMTScoreController extends Controller
{
    use MessageTrait;
    private $PMTScoreService;

    public function __construct(PMTScoreService  $PMTScoreService)
    {
        $this->PMTScoreService = $PMTScoreService;
    }

    /**
     * @OA\Get(
     *     path="/admin/poverty/get",
     *      operationId="getAllPMTScorePaginated",
     *      tags={"PMT-Score"},
     *      summary="get paginated PMTScores",
     *      description="get paginated PMTScores",
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

    public function getAllPMTScorePaginated(Request $request)

    {
         //   emu's code
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');
        $financial_year_id = $request->query('financial_year_id'); 
      
        $type = $request->query('type');
        
  

        $filterArrayNameEn = [];
       
        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
         
            
        }
     
        $cutOff = PMTScore::select(
            'poverty_score_cut_offs.*',
            'locations.name_en',
            'financial_years.financial_year',
        )
            ->leftJoin('locations', function ($join) {
                $join->on('poverty_score_cut_offs.location_id', '=', 'locations.id');
            })
            ->leftJoin('financial_years', function ($join) {
                $join->on('poverty_score_cut_offs.financial_year_id', '=', 'financial_years.id');
            })
            ->where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn);   
                
            })
            ->when($financial_year_id, function ($query, $financial_year_id) {
                return $query->where('financial_years.id', $financial_year_id);
            })
            ->when($type, function ($query, $type) {
                return $query->where('poverty_score_cut_offs.type', $type);
            })
            // 
            ->groupBy('financial_years.financial_year', 'poverty_score_cut_offs.type')
            ->latest('financial_years.financial_year')
            ->paginate($perPage, ['*'], 'page');

      return $cutOff;
        return PMTScoreResource::collection($cutOff)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
        // Retrieve the query parameters
        // $searchText = $request->query('searchText');
        // $perPage = $request->query('perPage');
        // $page = $request->query('page');
        // $finanlcial = $request->query('financial');
        // $location = $request->query('location');
        
        // $type = $request->query('type');
        
  

        // $filterArrayNameEn = [];
        // $filterArrayFinancial = [];
        // $filterArrayType = [];
        // $filterArrayLocation = [];

        // if ($searchText) {
        //     $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
         
            
        // }
     
        // $cutOff = PMTScore::select(
        //     'poverty_score_cut_offs.*',
        //     'locations.name_en',
        //     'financial_years.financial_year',
        // )
        //     ->leftJoin('locations', function ($join) {
        //         $join->on('poverty_score_cut_offs.location_id', '=', 'locations.id');
        //     })
        //     ->leftJoin('financial_years', function ($join) {
        //         $join->on('poverty_score_cut_offs.financial_year_id', '=', 'financial_years.id');
        //     })
        //     ->where(function ($query) use ($filterArrayNameEn) {
        //         $query->where($filterArrayNameEn);
                
                
        //     })
        //      ->when($finanlcial, function ($query, $finanlcial) {
        //         return $query->where('poverty_score_cut_offs.financial_year_id', '=', $finanlcial);
        //     })
        //     ->when($type, function ($query, $type) {
        //          if ($type != 0) {
        //        return $query->where('poverty_score_cut_offs.type', '=', $type);
        //       } 
            
        //     })
         
        //     ->latest()
        //     ->paginate($perPage, ['*'], 'page');

        // return $cutOff;
        // return PMTScoreResource::collection($cutOff)->additional([
        //     'success' => true,
        //     'message' => $this->fetchSuccessMessage,
        // ]);
       
     
     
    }
     /**
     * @OA\Get(
     *     path="/admin/poverty/filter/get",
     *      operationId="getAllFilterCutoffPaginated",
     *      tags={"PMT-Score"},
     *      summary="get paginated PMTScores",
     *      description="get paginated PMTScores",
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
    public function getAllFilterCutoffPaginated(Request $request){
         // Retrieve the query parameters
        $searchText = $request->query('searchText');
  
        $type = $request->query('type');
        $filterArrayNameEn = [];
        if( $type){
            if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
         
            
        }
   
        $cutOff = Location::where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn);
                 })
        ->when($type, function ($query, $type) {
 
           if ($type == 1) {
               return $query->where('locations.type', '=', 'division');
            } 
            elseif ($type == 2) {
               return $query->where('locations.type', '=', 'district');
              } 
              
            })
      
            ->orderBy('locations.name_en',"asc")
            ->whereIn('locations.type', ['division', 'district'])
            ->get();

        // return $cutOff;
        return ([
            'success' => true,
           'data'=>$cutOff,
           'total' => $cutOff->count(),

        ]);
     

        }
        else{
           return $type; 
        }
        

    }
     /**
     * @OA\Get(
     *     path="/admin/poverty/filter/edit",
     *      operationId="getEditFiterCutoffPaginated",
     *      tags={"PMT-Score"},
     *      summary="get paginated PMTScores",
     *      description="get paginated PMTScores",
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
    public function getEditFiterCutoffPaginated(Request $request){
         // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');
        $type = $request->query('type');
      
        $financial = $request->query('financial_year_id');
        $filterArrayNameEn = [];
        
        
            if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
         
            
        }
   
       $cutOff = PMTScore::select(
            'poverty_score_cut_offs.*',
            'locations.name_en',
            'locations.name_bn',

            'financial_years.financial_year',
        )
            ->leftJoin('locations', function ($join) {
                $join->on('poverty_score_cut_offs.location_id', '=', 'locations.id');
            })
            ->leftJoin('financial_years', function ($join) {
                $join->on('poverty_score_cut_offs.financial_year_id', '=', 'financial_years.id');
            })
            ->where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn);
                
                
            })
             ->when($financial, function ($query, $financial) {
                return $query->where('poverty_score_cut_offs.financial_year_id', '=', $financial);
            })
            ->when($type, function ($query, $type) {
          
               return $query->where('poverty_score_cut_offs.type', '=', $type);
            
            
            })
             ->orderByRaw("CASE WHEN locations.name_en = 'poverty cutoff' THEN 0 ELSE 1 END, locations.name_en ASC")
            ->get();

        // return $cutOff;
            return ([
            'success' => true,
           'data'=>$cutOff,
           'total' => $cutOff->count(),

        ]);
        return PMTScoreResource::collection($cutOff)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
     

       
        

    }
      /**
     *
     * @OA\Post(
     *      path="/admin/poverty/poverty-cut-off/insert",
     *      operationId="insertCutOff",
     *      tags={"PMT-Score"},
     *      summary="insert a povertyPMTScore",
     *      description="insert a povertyPMTScore",
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
     *                      property="type",
     *                      description="insert type",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="division_id",
     *                      description="insert division_id",
     *                      type="integer",
     *                   ),
     *                    @OA\Property(
     *                      property="location_id",
     *                      description="insert location_id",
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
 
    public function insertCutOff(Request $request){
         DB::beginTransaction();
    

      
        try {
            $input = $request->all();
           
            if($input = $request->type === "0"){
          
                   
                        
                    
                    $existingRecord = PMTScore::where('financial_year_id',$request->financial_year_id)
                    ->where('type',1)
                    ->first();
                    

                if ($existingRecord) {
                    // Duplicate entry found, handle accordingly (e.g., return an error response)
                    return response()->json([
                        'success' => false,
                        'error' => 'Already record created for this year .',
                    ]);
                }
                $existingRecord2 = PMTScore::where('financial_year_id',$request->financial_year_id)
                    ->where('type',2)
                    ->first();
                    if ($existingRecord2) {
                    // Duplicate entry found, handle accordingly (e.g., return an error response)
                    return response()->json([
                        'success' => false,
                        'error' => 'Already record created for this year.',
                    ]);
                }
                 
                $all = Location::where('type', 'division')->get();
                    foreach($all as $item) {
                        $pmt                      = new PMTScore;
                        $inputScore = $request->score;
                        $formattedScore = sprintf("%.3f", $inputScore);
                        // return    $formattedScore;
                        $pmt->score    	          = $formattedScore;
                     
                        $pmt->location_id    	   = $item['id'];
                        $pmt->financial_year_id   = $request->financial_year_id;
                        $pmt->type    	           = 1;
                        $pmt->default    	       = 0;
                        
                        $pmt->save();
                           Helper::activityLogInsert($pmt,'','PMT Score','PMT Score Created !');

                    }
                 
                    $all2 = Location::where('type', 'district')->get();
                    foreach($all2 as $item) {
                        $pmt                      = new PMTScore;
                
                        $inputScore = $request->score;
                        $formattedScore = sprintf("%.3f", $inputScore);
                        $pmt->score    	          = $formattedScore;
                        // return    $formattedScore;
                      
                        $pmt->location_id    	   = $item['id'];
                        $pmt->financial_year_id   = $request->financial_year_id;
                        $pmt->type    	           = 2;
                        $pmt->default    	       = 1;
                        
                        $pmt->save();
                         Helper::activityLogInsert($pmt,'',' PMT Score','PMT Score Created !');

                            }
                   


            }
            else{
               
              $input = $request->all();
                 foreach($input as $item) {
                    // Check if a record with the same values already exists
            $existingRecord1 = PMTScore::where('financial_year_id', $item['financialYearId'])
                ->where('type', $item['type'])
                ->where('location_id', $item['id'])
                ->first();

            if ($existingRecord1) {
                // Duplicate entry found, handle accordingly (e.g., return an error response)
                return response()->json([
                    'success' => false,
                    'error' => 'Already record created for this year.',
                ]);
            }

                 $pmt                      = new PMTScore;
                 $formattedScore = sprintf("%.3f", $item['inputScore']);
                //  return    $formattedScore;
              
                  $pmt->score            = $formattedScore; 

              
                 $pmt->score    	       = $item['inputScore'];
                 $pmt->location_id    	   = $item['id'];
                 $pmt->financial_year_id   = $item['financialYearId'];
                 $pmt->type    	           = $item['type'];
                
                 $pmt->save();
               Helper::activityLogInsert($pmt,'',' PMT Score','PMT Score  Created !');

             }
            
                
            }
            
        
         
            
           DB::commit();
             return response()->json([
        'success' => true,
        'message' => 'Data inserted successfully.',
    ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }


    }
      /**
     *
     * @OA\Post(
     *      path="/admin/poverty/poverty-cut-off/destroy",
     *      operationId="destroyCutoff",
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

    public function destroyCutoff(Request $request)

    {
        $input=$request->all();
       
       

     
    $validator = Validator::make($input, [
        'financial_year_id' => 'required|exists:poverty_score_cut_offs,financial_year_id',
        'type' => 'required|exists:poverty_score_cut_offs,type',
    ]);
        $validator->validated();

        $before = PMTScore::where("financial_year_id",'=',$request->financial_year_id)
        ->where("type",'=',$request->type)
        ->get();
        $cutoff = PMTScore::where("financial_year_id",'=',$request->financial_year_id)
        ->where("type",'=',$request->type)
        ->delete();
        // return $cutoff;
       

        // check if variable has any child if yes then return exception else delete
      

      
        Helper::activityLogDelete($before,'','PMT SCore','PMT SCore Deleted!!');
        return $this->sendResponse($cutoff, $this->deleteSuccessMessage, Response::HTTP_OK);
    }

    /**
     * @OA\Get(
     *     path="/admin/poverty/get/district-fixed-effect",
     *      operationId="getAllDistrictFixedEffectPaginated",
     *      tags={"PMT-Score"},
     *      summary="get paginated PMTScores",
     *      description="get paginated PMTScores",
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

    public function getAllDistrictFixedEffectPaginated(Request $request)
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
        $office = PMTScore::select(
            'poverty_score_cut_offs.*',
            'locations.name_en',
            'financial_years.financial_year',
        )
            ->leftJoin('locations', function ($join) {
                $join->on('poverty_score_cut_offs.location_id', '=', 'locations.id');
            })
            // ->leftJoin('financial_years', function ($join) {
            //     $join->on('poverty_score_cut_offs.financial_year_id', '=', 'financial_years.id');
            // })
            ->where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn)
                    // ->orWhere($filterArrayNameBn)
                    // ->orWhere($filterArrayComment)
                    // ->orWhere($filterArrayAddress)
                ;
            })
            ->where('default', '1') // Cut Off
            // ->with('assign_location.parent.parent.parent.parent', 'assign_location.locationType')
            ->latest()
            ->paginate($perPage, ['*'], 'page');

        return PMTScoreResource::collection($office)->additional([
            'success' => true,
            // 'message' => $this->fetchSuccessMessage,
        ]);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/poverty-cut-off/filter",
     *      operationId="filterDivisionCutOff",
     *      tags={"PMT-Score"},
     *      summary="filter a povertyPMTScore",
     *      description="filter a povertyPMTScore",
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
                $this->insertPMTScore($financial_year_id, $type);
            }
        }
        return;
        // if($type =){

        // }
        // $this->getAllPMTScorePaginated($request);
        // return;
        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayComment = [];
        $filterArrayAddress = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
        }
        $office = PMTScore::select(
            'poverty_score_cut_offs.*',
            'locations.name_en',
            'financial_years.financial_year',
        )
            ->leftJoin('locations', function ($join) {
                $join->on('poverty_score_cut_offs.location_id', '=', 'locations.id');
            })
            ->leftJoin('financial_years', function ($join) {
                $join->on('poverty_score_cut_offs.financial_year_id', '=', 'financial_years.id');
            })
            ->where(function ($query) use ($filterArrayNameEn) {
                $query->where($filterArrayNameEn)
                    // ->orWhere($filterArrayNameBn)
                    // ->orWhere($filterArrayComment)
                ;
            })
            // ->where('default', '0') // Cut Off
            // ->where('poverty_score_cut_offs.type', $request->type) // Cut Off
            ->where('poverty_score_cut_offs.financial_year_id', $request->financial_year_id); // Cut Off
        // ->with('assign_location.parent.parent.parent.parent', 'assign_location.locationType')
        // ->latest()

        if ($type == 1) {
            $office->where('default', '0') // Division Cut Off
                ->where('poverty_score_cut_offs.type', $request->type); // Cut Off
        }

        if ($type == 2) {
            $office->where('default', '1'); // District Cut Off
            // ->whereNull('poverty_score_cut_offs.type');
        }

        return $office->paginate($perPage, ['*'], 'page');

        return PMTScoreResource::collection($office)->additional([
            'success' => true,
            'financial_year_id' => $financial_year_id,
            'type' => $type,
            // 'message' => $this->fetchSuccessMessage,
        ]);
    }

    private function check_if_exists($financial_year_id, $type)
    {
        // echo "Type-if-".$type;

        if ($type == 1) {
            // echo "Type-if-".$type;
            $data = PMTScore::get()
                ->where('financial_year_id', $financial_year_id)
                ->where('default', '0');
        }
        if ($type == 2) {
            // echo "Type-if-".$type;
            $data = PMTScore::get()
            ->where('financial_year_id', $financial_year_id)
            ->where('default', '1')->whereNull('default', '1');
        }

        // print_r($data);
        if (count($data) > 0) {
            return true;
        } else {
            return false;
        }
    }

    private function insertPMTScore($financial_year_id, $type)
    {
        // THIS FUNCTION POVERTY CUT OFF INSERT 
        // IF NOT EXISTED FOR A SPECIFIC FINANCIAL YEAR

        if ($type == 0) {

            // ALL OVER BANGLADESH CUTTOFF
            $poverty_score_cut_offs = new PMTScore;
            $poverty_score_cut_offs->type         = $type;
            $poverty_score_cut_offs->financial_year_id  = $financial_year_id;
            $poverty_score_cut_offs->score        = 0;
            $poverty_score_cut_offs->default      = 0;
            $poverty_score_cut_offs->save();
            // END ALL OVER BANGLADESH CUTTOFF

        } else {
            if ($type == 1) {
                $locations = Location::get()->where('type', 'division'); // DIVISION CUTTOFF
            }
            if ($type == 2) {
                $locations = Location::get()->where('type', 'district'); //DISTRICT CUTTOFF
            }

            foreach ($locations as $value) {
                echo "Type".$type;
                echo "<br>";

                $poverty_score_cut_offs = new PMTScore;
                $poverty_score_cut_offs->type         = $type;
                $poverty_score_cut_offs->location_id  = $value['id'];
                $poverty_score_cut_offs->financial_year_id  = $financial_year_id;
                $poverty_score_cut_offs->score        = 0;

                if ($type == 1) {
                    $poverty_score_cut_offs->default  = 0;
                }
                if ($type == 2) {
                    $poverty_score_cut_offs->default  = 1;
                }

                $poverty_score_cut_offs->save();
            }
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/poverty/poverty-cut-off/update",
     *      operationId="updatePMTScore",
     *      tags={"PMT-Score"},
     *      summary="update a povertyPMTScore",
     *      description="update a povertyPMTScore",
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
    
    public function updateCutOff(Request $request){
         DB::beginTransaction();
    

      
        try {
            $input = $request->all();
            
          
        $validator = Validator::make($input, [
        'financial_year_id' => 'required|exists:poverty_score_cut_offs,financial_year_id',
        'type' => 'required|exists:poverty_score_cut_offs,type',
    ]);
        $validator->validated();
        if($input){
            
      
        $cutoff = PMTScore::where("financial_year_id",'=',$input[0]['financialYearId'])
     
        ->where("type",'=',$input[0]['type'])
        ->delete();
   
            
        
          foreach($input as $item) {
                 
         

        
                  
                 $pmt = new PMTScore();
                 $pmt->score    	       = $item['inputScore'];
                 $pmt->location_id    	   = $item['id'];
                 $pmt->financial_year_id   = $item['financialYearId'];
                 $pmt->type    	           = $item['type'];
                
                 $pmt->save();
               

             }
             
           
          

        }

            
           DB::commit();
             return response()->json([
        'success' => true,
        'message' => 'Data updated successfully.',
    ]);
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }


    }

    public function updatePMTScore(PMTScoreRequest $request)
    {

        try {
            $PMTScore = $this->PMTScoreService->updatePMTScore($request);
            activity("DivisionCutOff")
                ->causedBy(auth()->user())
                ->performedOn($PMTScore)
                ->log('PMTScore Created !');
            return PMTScoreResource::make($PMTScore)->additional([
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
     *      path="/admin/poverty/district-fixed-effect/update",
     *      operationId="updateDistrictFixedEffect",
     *      tags={"PMT-Score"},
     *      summary="update a povertyDistrictFixedEffect",
     *      description="update a povertyDistrictFixedEffect",
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
     *                      property="score",
     *                      description="score",
     *                      type="float",
     *                   ),
     *                    @OA\Property(
     *                      property="default",
     *                      description="default=1 for District Fixed Effect",
     *                      type="string",
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

    public function updateDistrictFixedEffect(DistrictFixedEffectRequest $request)
    {

        try {
            $PMTScore = $this->PMTScoreService->updatePMTScore($request);
            activity("DivisionCutOff")
                ->causedBy(auth()->user())
                ->performedOn($PMTScore)
                ->log('PMTScore Created !');
            return PMTScoreResource::make($PMTScore)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }


    // public function officeUpdate(OfficeUpdateRequest $request){

    //     try {
    //         $office = $this->OfficeService->updateOffice($request);
    //         activity("Office")
    //         ->causedBy(auth()->user())
    //         ->performedOn($office)
    //         ->log('Office Updated !');
    //         return OfficeResource::make($office)->additional([
    //             'success' => true,
    //             'message' => $this->updateSuccessMessage,
    //         ]);
    //     } catch (\Throwable $th) {
    //         //throw $th;
    //         return $this->sendError($th->getMessage(), [], 500);
    //     }
    // }
    

    public function insertDivisionCutOff(PMTScoreRequest $request)
    {

        try {
            $PMTScore = $this->PMTScoreService->createPMTScore($request);
            activity("DivisionCutOff")
                ->causedBy(auth()->user())
                ->performedOn($PMTScore)
                ->log('PMTScore Created !');
            return PMTScoreResource::make($PMTScore)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }
}
