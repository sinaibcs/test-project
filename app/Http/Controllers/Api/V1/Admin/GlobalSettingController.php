<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Illuminate\Http\Request;
use App\Models\GlobalSetting;
use Illuminate\Http\Response;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use App\Http\Requests\Admin\GlobalSetting\GlobalSettingRequest;
use App\Http\Services\Admin\GlobalSetting\GlobalSettingService;
use App\Http\Resources\Admin\GlobalSetting\GlobalSettingResource;
use App\Http\Requests\Admin\GlobalSetting\GlobalSettingUpdateRequest;

class GlobalSettingController extends Controller
{
    //
    use MessageTrait;
       private $globalsettingService;

    public function __construct(GlobalSettingService $globalsettingService) {
        $this->globalsettingService = $globalsettingService;
    }
    
    /**
    * @OA\Get(
    *     path="/admin/globalsetting/get",
    *      operationId="getAllGlobalSettingPaginated",
    *      tags={"GLOBAL-SETTING"},
    *      summary="get paginated GlobalSettinga",
    *      description="get paginated GlobalSettings",
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

 public function getAllGlobalSettingPaginated(Request $request){
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $filterArrayValue=[];
       

        if ($searchText) {
            $filterArrayValue[] = ['value', 'LIKE', '%' . $searchText . '%'];
          
        }
        $globalsetting = GlobalSetting::query()
        ->where(function ($query) use ($filterArrayValue) {
            $query->where($filterArrayValue);
                  
        })
        ->with('areaType')
        ->orderBy('value')
        ->paginate($perPage, ['*'], 'page');

        return GlobalSettingResource::collection($globalsetting)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
 }

    /**
     *
     * @OA\Post(
     *      path="/admin/globalsetting/insert",
     *      operationId="insertGlobalSetting",
     *      tags={"GLOBAL-SETTING"},
     *      summary="insert a global setting",
     *      description="insert a global setting",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="area_type",
     *                      description="Area Type",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="value",
     *                      description="Value of Area Type",
     *                      type="text",
     *                   ),
     *                 
     *
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
        public function insertGlobalSetting(GlobalSettingRequest $request){

        try {
            $globalsetting = $this->globalsettingService->createGlobalSetting($request);
            activity("GlobalSetting")
            ->causedBy(auth()->user())
            ->performedOn($globalsetting)
            ->log('GlobalSetting Created !');
            return GlobalSettingResource::make($globalsetting)->additional([
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
     *      path="/admin/globalsetting/destroy/{id}",
     *      operationId="destroyGlobalSetting",
     *      tags={"GLOBAL-SETTING"},
     *      summary=" destroy global setting",
     *      description="Returns global setting destroy by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of global setting to return",
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

        public function destroyGlobalSetting($id)
    {

        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:global_settings,id',
        ]);

        $validator->validated();

        $globalsetting = GlobalSetting::whereId($id)->first();

    
        if($globalsetting){
            $globalsetting->delete();
        }
        activity("GlobalSetting")
        ->causedBy(auth()->user())
        ->log('GlobalSetting Deleted!!');
         return $this->sendResponse($globalsetting, $this->deleteSuccessMessage, Response::HTTP_OK);

    }
     /**
     *
     * @OA\Post(
     *      path="/admin/globalsetting/update",
     *      operationId="globalsettingUpdate",
     *      tags={"GLOBAL-SETTING"},
     *      summary="update a Global Setting",
     *      description="update a Global Setting",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="enter inputs",
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                   @OA\Property(
     *                      property="id",
     *                      description="id of the Global Setting",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="area_type",
     *                      description="Area Type",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="value",
     *                      description="value",
     *                      type="text",
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
    public function globalsettingUpdate(GlobalSettingUpdateRequest $request){

        try {
            $globalsetting = $this->globalsettingService->updateGlobalSetting($request);
            activity("GlobalSetting")
            ->causedBy(auth()->user())
            ->performedOn($globalsetting)
            ->log('Global Setting Update !');
            return GlobalSettingResource::make($globalsetting)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }


}
