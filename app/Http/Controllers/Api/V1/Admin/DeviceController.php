<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Models\User;
use App\Models\Device;
use App\Models\Office;
use App\Helpers\Helper;
use Illuminate\Http\Request;
use App\Constants\DeviceType;
use Illuminate\Http\Response;
use App\Http\Traits\RoleTrait;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use App\Http\Requests\Admin\Device\DeviceRequest;
use App\Http\Services\Admin\Device\DeviceService;
use App\Http\Resources\Admin\Device\DeviceResource;
use App\Http\Requests\Admin\Device\DeviceUpdateRequest;

class DeviceController extends Controller
{
    use MessageTrait, RoleTrait;
    private $DeviceService;

    public function __construct(DeviceService $DeviceService) {
        $this->DeviceService = $DeviceService;
    }

    /**
    * @OA\Get(
    *     path="/admin/device/get",
    *      operationId="getAllDevicePaginated",
    *      tags={"DEVICE"},
    *      summary="get paginated device",
    *      description="get paginated device",
    *      security={{"bearer_token":{}}},
    *     @OA\Parameter(
    *         name="searchText",
    *         in="query",
    *         description="search by user ID",
    *         @OA\Schema(type="string")
    *     ),
    *     @OA\Parameter(
    *         name="perPage",
    *         in="query",
    *         description="number of device per page",
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

 public function getAllDevicePaginated(Request $request){
     $user = auth()->user();
     $device = new Device;

     if ($request->has('sortBy') && $request->has('sortDesc')) {
        $sortBy = $request->query('sortBy');
        $sortDesc = $request->query('sortDesc') == true ? 'desc' : 'asc';
        if ($sortBy == 'id') {
            $device = $device->orderBy('id', $sortDesc);
        } else {
            $device = $device->orderBy($sortBy, $sortDesc);
        }
    } else {
        $device = $device->orderBy('id', 'desc');
    }


     $device->with(['user.office','user.committee.office']);

     if(!$user->hasRole($this->superAdmin)){
         $device->where('user_id', '!=', $user->id);
     }

     if(!$user->hasRole($this->superAdmin)){
        $weight = $user->roles()->min('weight');
        if(!($user->office?->office_type == 4 || $user->office?->office_type == 5)){
            $device->whereHas('user', function($q) use($user){
                // $userOffice = $user->office;
                $officeIds = [$user->office->id, ...$this->getSubOfficeIds($user->office->assignLocation)];
                // $officeIds = [$user->office->id, ...Office::whereIn('assign_location_id',$user->office->assignLocation->children()->pluck('id'))->pluck('id')];
                $q->whereIn('office_id', $officeIds)
                    ->orWhereHas('committee', function($q)use($officeIds){
                        $q->whereIn('office_id', $officeIds);
                    });
            });
        }

        $device->whereHas('user', function($q)use($weight){
            $q->whereRaw("(select MIN(roles.weight) from roles JOIN `model_has_roles` ON `roles`.`id` = `model_has_roles`.`role_id`
            WHERE
            `users`.`id` = `model_has_roles`.`model_id` AND `model_has_roles`.`model_type` = 'App\\\Models\\\User') > $weight");
        });
     }

     $searchValue = $request->input('search');

     if($searchValue)
     {
         $device->where(function($query) use ($searchValue) {
             $query->where('name', 'like', '%' . $searchValue . '%');
             $query->orWhere('device_name', 'like', '%' . $searchValue . '%');
//             $query->orWhere('device_type', 'like', '%' . $searchValue . '%');
             $query->orWhere('user_id', 'like', '%' . $searchValue . '%');
         });

         $itemsPerPage = 10;

         if($request->has('itemsPerPage')) {
             $itemsPerPage = $request->get('itemsPerPage');

             return $device->paginate($itemsPerPage, ['*'], $request->get('page'));
         }
     }else{
         $itemsPerPage = 10;

         if($request->has('itemsPerPage'))
         {
             $itemsPerPage = $request->get('itemsPerPage');

             return $device->paginate($itemsPerPage);
         }
     }
    }

    public function getSubOfficeIds($location){
        $subLocations = $location?->children()->with('office')->whereHas('office')->get()??[];
        $ids = [];
        foreach($subLocations as $subLocation){
            foreach($subLocation->office as $office){
                $ids[] =  $office->id;
            }
            $ids = array_merge($ids , $this->getSubOfficeIds($subLocation));
        }
        return $ids;
    }

    public function getUsers()
    {
        $users = User::where('status',1)->latest()->get();

        return \response()->json([
            'users' => $users
        ],Response::HTTP_OK);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/device/insert",
     *      operationId="insertDevice",
     *      tags={"DEVICE"},
     *      summary="insert a device",
     *      description="insert a device",
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
     *                      property="user_id",
     *                      description="user id of the device user",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="name",
     *                      description="user name",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="device_name",
     *                      description="device name",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="device_id",
     *                      description="browser fingerprint of the user",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="ip_address",
     *                      description="IP address of the user",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="device_type",
     *                      description="Device type of the user",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="purpose_use",
     *                      description="purpose of the user device",
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
    public function insertDevice(DeviceRequest $request){
        // $count=Device::whereUserId($request->user_id)->count();
        // if($count>=5){
        //     return $this->sendError("maximum Device Registered", [], 422);
        // }
        try {
            $device = $this->DeviceService->createDevice($request);

            Helper::activityLogInsert($device, '','Device','Device Created !');

            return DeviceResource::make($device)->additional([
                'success' => true,
                'message' => $this->insertSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function deviceEdit($id)
    {
        $device = Device::where('id', $id)->first();

        return \response()->json([
            'device' => $device
        ],Response::HTTP_OK);
    }


    public function show(Device $device)
    {
        $device->load(['user.office.assignLocation','user.committee.office.assignLocation']);
        $device->deviceTypeName = DeviceType::TYPES[$device->device_type] ?? '';
        return $this->sendResponse($device);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/device/update",
     *      operationId="deviceUpdate",
     *      tags={"DEVICE"},
     *      summary="update a device",
     *      description="update a device",
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
     *                      description="id of the device",
     *                      type="integer",
     *                   ),
     *                   @OA\Property(
     *                      property="user_id",
     *                      description="user id of the device user",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="name",
     *                      description="user name",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="device_name",
     *                      description="device name",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="device_id",
     *                      description="browser fingerprint of the user",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="ip_address",
     *                      description="IP address of the user",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="device_type",
     *                      description="Device type of the user",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="purpose_use",
     *                      description="purpose of the user device",
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
    public function deviceUpdate(DeviceUpdateRequest $request){

        try {
            $beforeUpdate                       = Device::find($request->id);

            $device = $this->DeviceService->editDevice($request);

            Helper::activityLogUpdate($device, $beforeUpdate,'Device','Device Updated !');

            return DeviceResource::make($device)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    /**
     * @OA\Get(
     *      path="/admin/device/destroy/{id}",
     *      operationId="destroyDevice",
     *      tags={"DEVICE"},
     *      summary=" destroy device",
     *      description="Returns device destroy by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of device to return",
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
    public function destroyDevice($id)
    {


        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:devices,id',
        ]);

        $validator->validated();

        $device = Device::whereId($id)->first();
        if($device){
            $device->delete();
        }

        Helper::activityLogDelete($device, '','Device','Device Deleted !');

         return $this->sendResponse($device, $this->deleteSuccessMessage, Response::HTTP_OK);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/device/status",
     *      operationId="deviceStatusUpdate",
     *      tags={"DEVICE"},
     *      summary="update publish status of a device",
     *      description="update publish status of a device",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="update the device",
     *
     *
     *            @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *
     *                    @OA\Property(
     *                      property="id",
     *                      description="id of the device",
     *                      type="text",
     *
     *                   ),
     *                    @OA\Property(
     *                      property="status",
     *                      description="status or not.boolean 0 or 1",
     *                      type="text",
     *
     *                   ),
     *
     *                   ),
     *               ),
     *
     *         ),
     *
     *
     *
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation with no content",
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
    public function deviceStatusUpdate($id)
    {
        $beforeUpdate = Device::findOrFail($id);
        $device = Device::findOrFail($id);

        $device->status = !$device->status;
        $device->save();

        Helper::activityLogUpdate($device, $beforeUpdate,'Device','Device Updated !');

        return response()->json([
            'message' => 'Device Status Updated Successful'
        ],Response::HTTP_OK);

    }

    public function deviceReportExcel(Request $request){

        $device = new Device;

        if ($request->has('sortBy') && $request->has('sortDesc')) {
            $sortBy = $request->query('sortBy');

            $sortDesc = $request->query('sortDesc') == true ? 'desc' : 'asc';

            $device = $device->orderBy($sortBy, $sortDesc);
        } else {
            $device = $device->orderBy('name', 'asc');
        }


        $device->with('user');

        $searchValue = $request->input('search');

        if($searchValue)
        {
            $device->where(function($query) use ($searchValue) {
                $query->where('name', 'like', '%' . $searchValue . '%');
                $query->orWhere('ip_address', 'like', '%' . $searchValue . '%');
                $query->orWhere('device_type', 'like', '%' . $searchValue . '%');
            });

             $device->get();

        }else{
                 $device->get();
        }

        $items = $device->get()->toArray();
return $items;
        return $this->sendResponse($items, 'Excell Data');
    }

    public function deviceReportPdf(Request $request)
    {

        $data =  $this->deviceReportExcel($request);

        $CustomInfo = array_map(function($i, $index) use($request) {
            return [
                $request->language == "bn" ? Helper::englishToBangla($index + 1) : $index + 1,
                $i['user']['username'],
                $i['device_type'],
                $i['device_id'],
                $i['purpose_use'],
            ];
        }, $data, array_keys($data));

        $data = ['headerInfo' => $request->header,'dataInfo'=>$CustomInfo,'fileName' => $request->fileName];

        ini_set("pcre.backtrack_limit", "5000000");
        $pdf = LaravelMpdf::loadView('reports.dynamic', $data, [],
            [
                'mode' => 'utf-8',
                'format' => 'A4-P',
                'title' => $request->fileName,
                'orientation' => 'L',
                'default_font_size' => 10,
                'margin_left' => 10,
                'margin_right' => 10,
                'margin_top' => 10,
                'margin_bottom' => 10,
                'margin_header' => 10,
                'margin_footer' => 10,
            ]);


        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]);

    }



}
