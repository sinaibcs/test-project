<?php

namespace App\Http\Controllers\Api\V1\Admin;

use Validator;
use App\Models\Lookup;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\UserTrait;
use App\Http\Traits\LookupTrait;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use App\Http\Requests\Admin\Lookup\LookupRequest;
use App\Http\Services\Admin\Lookup\LookupService;
use App\Http\Resources\Admin\Lookup\LookupResource;
use Illuminate\Database\Events\TransactionBeginning;
use App\Http\Requests\Admin\Lookup\LookupUpdateRequest;

class AdminController extends Controller
{
    use MessageTrait,UserTrait,LookupTrait;
    private $lookupService;

    public function __construct(LookupService $lokupService) {
        $this->lookupService = $lokupService;
    }

    /**
    * @OA\Get(
    *     path="/admin/lookup/get",
    *      operationId="getAllLookupPaginated",
    *      tags={"LOOKUP MANAGEMENT"},
    *      summary="get paginated Lookup",
    *      description="get paginated Lookup",
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
    *         description="number of lookup per page",
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

 public function getAllLookupPaginated(Request $request){
    // Retrieve the query parameters
    $searchText = $request->query('searchText');
    $perPage = $request->query('perPage');
    $page = $request->query('page');

    $filterArrayNameEn = [];
    $filterArrayNameBn = [];
    $filterArrayKeyWord = [];

    if ($searchText) {
        $filterArrayNameEn[] = ['value_en', 'LIKE', '%' . $searchText . '%'];
        $filterArrayNameBn[] = ['value_bn', 'LIKE', '%' . $searchText . '%'];
        $filterArrayKeyWord[] = ['keyword', 'LIKE', '%' . $searchText . '%'];
    }

    // Build a unique cache key
    $cacheKey = 'lookups:' . md5(json_encode([
        'searchText' => $searchText,
        'perPage'    => $perPage,
        'page'       => $page,
    ]));

    // Get TTL from .env (default to 10 minutes)
    $ttl = now()->addMinutes(env('CACHE_TIMEOUT', 10));

    // Remember cached result
    $lookup = Cache::remember($cacheKey, $ttl, function () use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayKeyWord, $perPage, $page) {
        return Lookup::query()
            ->where(function ($query) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayKeyWord) {
                $query->where($filterArrayNameEn)
                    ->orWhere($filterArrayNameBn)
                    ->orWhere($filterArrayKeyWord);
            })
            ->orderBy('value_en', 'asc')
            ->latest()
            ->paginate($perPage, ['*'], 'page', $page);
    });

    return LookupResource::collection($lookup)->additional([
        'success' => true,
        'message' => $this->fetchSuccessMessage,
    ]);
}

     /**
     * @OA\Get(
     *      path="/admin/lookup/get/{type}",
     *      operationId="getLookupByType",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary="get lookup by type",
     *      description="get lookup by type",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="type of lookup to return",
     *         in="path",
     *         name="type",
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

     public function getLookupByType($type){
        $validator = Validator::make(['type' => $type], [
            'type' => 'required|exists:lookups,type',
        ]);

        $validator->validated();
        $lookup = Lookup::whereType($type->orderBy('value_en', 'asc'))->get();
        return LookupResource::collection($lookup)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
     }


    public function getClassList(){
        $lookup = Lookup::whereType(20)->orderBy('id')->get();
        return LookupResource::collection($lookup)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/lookup/insert",
     *      operationId="insertlookup",
     *      tags={"LOOKUP MANAGEMENT"},
     *      summary="insert a lookup",
     *      description="insert a lookup",
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
     *                      property="type",
     *                      description="insert type",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="value_en",
     *                      description="insert value in english",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="value_bn",
     *                      description="insert value in bangla",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="keyword",
     *                      description="insert Keyword of Lookup",
     *                      type="text",
     *
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
    public function insertlookup(LookupRequest $request){
        // return $request->all();
        try {
            $lookup = $this->lookupService->createLookup($request);
            activity("Lookup")
            ->causedBy(auth()->user())
            ->performedOn($lookup)
            ->log('Lookup Created !');
            return LookupResource::make($lookup)->additional([
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
     *      path="/admin/lookup/update",
     *      operationId="lookupUpdate",
     *      tags={"LOOKUP MANAGEMENT"},
     *      summary="update a Lookup",
     *      description="update a lookup",
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
     *                      description="id of the lookup",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="type",
     *                      description="type of the Lookup",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="value_en",
     *                      description="english name of the Lookup",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="value_bn",
     *                      description="bangla name of the lookup",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="keyword",
     *                      description="keyword of the Lookup",
     *                      type="text",
     *                   ),
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
    public function lookupUpdate(LookupUpdateRequest $request){

        try {
            $lookup = $this->lookupService->updateLookup($request);
            activity("Lookup")
            ->causedBy(auth()->user())
            ->performedOn($lookup)
            ->log('Lookup Update !');
            return LookupResource::make($lookup)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function lookupReorder(Request $request){
        try {
            $lookups = Lookup::where('type', $request->type)->orderBy('display_order', 'asc')->orderBy('id','asc')->get();
            $i = -1;
            foreach($lookups as $key => $lookup ){
                if($lookup->id == $request->id){
                    $i = $key;
                    break;
                }
            }
            if($i > -1){
                if($request->action == 'UP'){
                    // return [ $i -1,  $lookup[$i - 1]];
                    $tmp = clone $lookups[$i];
                    $lookups[$i] = $lookups[$i - 1];
                    $lookups[$i - 1] = $tmp;
                    // return $lookup[$i];
                }elseif($request->action == "DOWN"){
                    $tmp = clone $lookups[$i];
                    $lookups[$i] = $lookups[$i + 1];
                    $lookups[$i + 1] = $tmp;
                }
                \DB::beginTransaction();
                foreach($lookups as $key => $lookup){
                    \DB::table('lookups')
                    ->where('id', $lookup->id)
                    ->update(['display_order' => $key]);
                }
                \DB::commit();
            return LookupResource::collection($lookups)->additional([
                'success' => true,
                'message' => $this->fetchSuccessMessage,
            ]);
        }
        } catch (\Throwable $th) {
            \DB::rollBack();
            throw $th;
        }
    }


 /**
     *
     * @OA\Post(
     *      path="/admin/lookup/get/{type}",
     *      operationId="getAllLookupByType",
     *      tags={"LOOKUP-MANAGEMENT"},
     *      summary="update a Lookup",
     *      description="update a lookup",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of thana to return",
     *         in="path",
     *         name="type",
     *         @OA\Schema(
     *           type="string",
     *         )
     *     ),
     *
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


    public function getAllLookupByType($type){
        $lookup = Cache::remember("lookup_type.{$type}", now()->addMinutes(env('CACHE_TIMEOUT', 10)), function () use ($type) {
            return Lookup::whereType($type)
                ->orderBy('display_order', 'asc')
                ->orderBy('id', 'asc')
                ->get();
        });

        return LookupResource::collection($lookup)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

        /**
     * @OA\Get(
     *      path="/admin/lookup/destroy/{id}",
     *      operationId="destroyLookup",
     *      tags={"LOOKUP MANAGEMENT"},
     *      summary=" destroy lookups",
     *      description="Returns lookup destroy by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of lookup to return",
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
    public function destroyLookup($id)
    {


        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:lookups,id',
        ]);

        $validator->validated();

        $lookup = Lookup::whereId($id)->first();
        if($lookup){
            $lookup->delete();
        }
        activity("Lookup")
        ->causedBy(auth()->user())
        ->log('Lookup Deleted!!');
         return $this->sendResponse($lookup, $this->deleteSuccessMessage, Response::HTTP_OK);
    }

}