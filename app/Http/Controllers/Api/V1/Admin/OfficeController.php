<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Helpers\Helper;
use App\Http\Resources\Admin\Office\OfficeDropDownResource;
use App\Http\Services\Admin\Office\OfficeListService;
use App\Models\Allotment;
use App\Models\Beneficiary;
use App\Models\Location;
use App\Models\Payroll;
use App\Models\PayrollDetail;
use Mccarlosen\LaravelMpdf\Facades\LaravelMpdf;
use Validator;
use App\Models\Office;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Http\Traits\MessageTrait;
use App\Http\Controllers\Controller;
use App\Http\Services\Admin\Office\OfficeService;
use App\Http\Resources\Admin\Office\OfficeResource;
use App\Http\Requests\Admin\System\Office\OfficeRequest;
use App\Http\Requests\Admin\System\Office\OfficeUpdateRequest;
use App\Http\Traits\PermissionTrait;
use App\Models\OfficeHasWard;
use App\Models\User;
use Illuminate\Validation\Rule;

class OfficeController extends Controller
{
    use MessageTrait, PermissionTrait;

    private $OfficeService;
    private $office_location_id;

    public function __construct(OfficeService $OfficeService)
    {
        $this->OfficeService = $OfficeService;
    }

    /**
     * @OA\Get(
     *     path="/admin/office/get",
     *      operationId="getAllOfficePaginated",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary="get paginated Offices",
     *      description="get paginated Offices",
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
     *
     *     @OA\Parameter(
     *         name="user_id",
     *         in="query",
     *         description="user_id",
     *         @OA\Schema(type="integer")
     *     ),
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
     * )
     */

    public function getAllOfficePaginated(Request $request)
    {
        $searchText = $request->query('searchText');
        $perPage    = $request->query('perPage', 15);
        $sortBy     = $request->query('sortBy', 'name_en');
        $orderBy    = $request->query('orderBy', 'asc');
        $startDate  = $request->query('from_date');
        $endDate    = $request->query('to_date');

        $filters = [
            'office_type'  => $request->query('office_type'),
            'division_id'  => $request->query('division_id'),
            'district_id'  => $request->query('district_id'),
            'thana_id'     => $request->query('thana_id'),
            'city_id'      => $request->query('city_id'),
            'dist_pouro_id'=> $request->query('dist_pouro_id'),
            'upazila_id'   => $request->query('upazila_id'),
        ];

        $query = Office::with([
            'assignLocation', 
            'assignLocation.parent.parent.parent',
            'assignLocation.locationType',
            'officeType',
            'wards' => fn($q) => $q->with('division', 'district', 'city', 'thana', 'union', 'pouro')
        ])->orderBy($sortBy, $orderBy);

        // Search filter
        if ($searchText) {
            $query->where(function ($q) use ($searchText) {
                $q->where('name_en', 'LIKE', "%{$searchText}%")
                ->orWhere('name_bn', 'LIKE', "%{$searchText}%")
                ->orWhere('comment', 'LIKE', "%{$searchText}%")
                ->orWhere('office_address', 'LIKE', "%{$searchText}%")
                ->orWhere('office_address_bn', 'LIKE', "%{$searchText}%")
                ->orWhereHas('assignLocation', fn($sub) => 
                        $sub->where('name_en', 'LIKE', "%{$searchText}%")
                            ->orWhere('name_bn', 'LIKE', "%{$searchText}%")
                            ->orWhere('code', '=', $searchText)
                );
            });
        }

        // Date filter
        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }

        // Location-based filters
        foreach ($filters as $key => $value) {
            if (!$value) continue;

            match ($key) {
                'office_type' => $query->where('office_type', $value),
                'division_id' => $query->whereHas('assignLocation', function ($q) use ($value) {
                    $q->where('id', $value)
                    ->orWhere('parent_id', $value)
                    ->orWhereHas('parent', fn($p) => 
                        $p->where('id', $value)
                            ->orWhereHas('parent', fn($pp) => 
                                $pp->where('id', $value)
                                ->orWhereHas('parent', fn($ppp) => 
                                    $ppp->where('id', $value)
                                )
                            )
                    );
                }),
                'district_id' => $query->whereHas('assignLocation', fn($q) => $q->where('id', $value)->orWhere('parent_id', $value)),
                'thana_id'    => $query->whereHas('assignLocation.parent.parent', fn($q) => $q->where('id', $value)),
                'city_id'     => $query->whereHas('assignLocation', fn($q) => $q->where('id', $value)->orWhere('parent_id', $value)),
                'dist_pouro_id'=> $query->whereHas('assignLocation', fn($q) => $q->where('id', $value)->orWhere('parent_id', $value)),
                'upazila_id'  => $query->whereHas('assignLocation', fn($q) => $q->where('id', $value)->orWhere('parent_id', $value)),
                default       => null
            };
        }

        $this->filterByLocation($query);

        return $query->paginate($perPage);
    }



    public function getAllOffice(Request $request)
    {
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');
        $sortBy = $request->query('sortBy') ?? 'name_en';
        $orderBy = $request->query('orderBy') ?? 'asc';
        $startDate = $request->query('from_date');
        $endDate = $request->query('to_date');

        $officeType = $request->query('office_type');
        $divisionId = $request->query('division_id');
        $districtId = $request->query('district_id');
        $thanaId = $request->query('thana_id');
        $cityId = $request->query('city_id');
        $distPouroId = $request->query('dist_pouro_id');
        $upazilaId = $request->query('upazila_id');

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayComment = [];
        $filterArrayAddress = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAddress[] = ['office_address', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAddress[] = ['office_address_bn', 'LIKE', '%' . $searchText . '%'];

            if ($searchText != null) {
                $page = 1;
            }
        }
        $data = Office::query()->with('assignLocation', 'assignLocation.parent.parent.parent', 'assignLocation.locationType', 'officeType', 'wards')
            ->orderBy($sortBy, $orderBy);

        $data->when(str_contains($searchText, '%'), function ($q) {
            $q->whereId(null);
        })
            ->where(function ($query) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayComment, $filterArrayAddress) {
                $query->where($filterArrayNameEn)
                    ->orWhere($filterArrayNameBn)
                    ->orWhere($filterArrayComment)
                    ->orWhere($filterArrayAddress);
            });

        $this->filterByLocation($data);

        if ($startDate && $endDate) {
            $data->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->filled('office_type')) {
            $data->where('office_type', $officeType);
        }

        if ($request->filled('division_id')) {
            $data->whereHas('assignLocation', function ($query) use ($divisionId) {
                $query->where('id', $divisionId)
                    ->orWhere('parent_id', $divisionId)
                    ->orWhereHas('parent', function ($query) use ($divisionId) {
                        $query->where('id', $divisionId)
                            ->orWhereHas('parent', function ($query) use ($divisionId) {
                                $query->where('id', $divisionId)
                                    ->orWhereHas('parent', function ($query) use ($divisionId) {
                                        $query->where('id', $divisionId);
                                    });
                            });
                    });
            });
        }

        if ($request->filled('district_id')) {
            $data->whereHas('assignLocation', function ($query) use ($districtId) {
                $query->where('id', $districtId)
                    ->orWhere('parent_id', $districtId);
            });
        }

        if ($request->filled('thana_id')) {
            $data->whereHas('assignLocation.parent.parent', function ($query) use ($thanaId) {
                $query->where('id', $thanaId);
            });
        }

        if ($request->filled('city_id')) {
            $data->whereHas('assignLocation', function ($query) use ($cityId) {
                $query->where('id', $cityId)
                    ->orWhere('parent_id', $cityId);
            });
        }

        if ($request->filled('dist_pouro_id')) {
            $data->whereHas('assignLocation', function ($query) use ($distPouroId) {
                $query->where('id', $distPouroId)
                    ->orWhere('parent_id', $distPouroId);
            });
        }

        if ($request->filled('upazila_id')) {
            $data->whereHas('assignLocation', function ($query) use ($upazilaId) {
                $query->where('id', $upazilaId)
                    ->orWhere('parent_id', $upazilaId);
            });
        }

        // Retrieve all data without pagination
        $offices = $data->get();

        return OfficeResource::collection($offices)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    public function filterByLocation($query)
    {
        return (new OfficeListService)->getOfficesUnderUser($query);
        return $query;
    }


    /**
     * @OA\Get(
     *     path="/admin/office/get-ward-under-office",
     *     operationId="getAllWardUnderOffice",
     *     tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *     summary="get paginated Ward Offices",
     *     description="get paginated Ward under Offices",
     *     security={{"bearer_token":{}}},
     *
     *
     *     @OA\Parameter(
     *         name="id",
     *         in="query",
     *         description="Office Id",
     *         @OA\Schema(type="integer")
     *     ),
     *
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Successful Insert operation",
     *         @OA\JsonContent()
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthenticated"
     *     ),
     *     @OA\Response(
     *         response=403,
     *         description="Forbidden"
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Unprocessable Entity"
     *     )
     * )
     */
    public function getAllWardUnderOffice(Request $request)
    {
        // Retrieve the query parameters
        // echo "jelp";
        // return;
        $id = $request->query('id');
        // $searchText = $request->query('searchText');
        // $perPage = $request->query('perPage');
        // $page = $request->query('page');
        // $sortBy = $request->query('sortBy') ?? 'name_en';
        // $orderBy = $request->query('orderBy') ?? 'asc';

        // $filterArrayNameEn=[];
        // $filterArrayNameBn=[];
        // $filterArrayComment=[];
        // $filterArrayAddress=[];

        // if ($searchText) {
        //     $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
        //     $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
        //     $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
        //     $filterArrayAddress[] = ['office_address', 'LIKE', '%' . $searchText . '%'];
        //     if ($searchText != null) {
        //         $page = 1;
        //     }
        // }


        $office = Office::query()
            ->whereId($id)
            ->with('wards.parent.parent.parent.parent.parent')
            ->get();

        // ->latest()
        // ->paginate($perPage, ['*'], 'page');
        // ->orderBy($sortBy, $orderBy)
        // ->paginate($perPage, ['*'], 'page', $page);

        return $office;
        return OfficeResource::collection($office)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }


    public function getWardList($officeId)
    {

        $wards = OfficeHasWard::where('office_id', $officeId)->pluck('ward_id');

        return $this->sendResponse(Location::whereIn('id', $wards)->get());
    }


    /**
     *
     * @OA\Post(
     *      path="/admin/office/insert",
     *      operationId="insertOffice",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary="insert a office",
     *      description="insert a office",
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
     *                      property="division_id",
     *                      description="insert Division Id",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="district_id",
     *                      description="insert District Id",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="thana_id",
     *                      description="insert Thana Id",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="city_corpo_id",
     *                      description="insert city corporation Id",
     *                      type="integer",
     *
     *                   ),
     *                  @OA\Property(
     *                      property="office_type",
     *                      description="insert office_type",
     *                      type="integer",
     *
     *                   ),
     *                 @OA\Property(
     *                      property="name_en",
     *                      description="insert name_en",
     *                      type="text",
     *
     *                   ),
     *                 @OA\Property(
     *                      property="name_bn",
     *                      description="insert name_en",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="office_address",
     *                      description="bangla name of office_address",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="comment",
     *                      description="comment",
     *                      type="text",
     *                   ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="status",
     *                      type="tinyInteger",
     *                   ),
     *
     *                  @OA\Property(
     *                      property="ward_under_office[0][office_id]",
     *                      description="insert Office id",
     *                      type="integer",
     *                   ),
     *
     *                  @OA\Property(
     *                     property="ward_under_office[0][ward_id]",
     *                      description="insert ward id",
     *                      type="integer",
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
    public function insertOffice(Request $request)
    {

        // $validation = Validator::make($request->all(), [
        //     'selectedWards.*' => [
        //         'required',
        //         Rule::unique('office_has_wards', 'ward_id'),
        //     ],
        // ]);

        // if ($validation->fails()) {
        //     $officeHasWard = OfficeHasWard::whereIn('ward_id', $request->selectedWards)->with(['parent.parent','office'])->first();
        //     $message = null;
        //     if($officeHasWard){
        //         $message = $officeHasWard->parent->name_en." ward of ".$officeHasWard->parent->parent->name_en." is already assigned to ".$officeHasWard->office->name_en.", ".$officeHasWard->office->assignLocation->name_en;
        //     }
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Validation failed',
        //         'errors' => $message ?? 'One or more wards are already assigned to another office',
        //     ], 400);
        // }



        try {
            $office = $this->OfficeService->createOffice($request);

            Helper::activityLogInsert($office, '', 'Office', 'Office Created !');

            return OfficeResource::make($office)->additional([
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
     *      path="/admin/office/update",
     *      operationId="officeUpdate",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary="update a office",
     *      description="update a office",
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
     *                      description="id of the Office",
     *                      type="integer",
     *                   ),
     *                  @OA\Property(
     *                      property="division_id",
     *                      description="insert Division Id",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="district_id",
     *                      description="insert District Id",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="thana_id",
     *                      description="insert Thana Id",
     *                      type="integer",
     *
     *                   ),
     *                    @OA\Property(
     *                      property="city_corpo_id",
     *                      description="insert city corporation Id",
     *                      type="integer",
     *
     *                   ),
     *                  @OA\Property(
     *                      property="office_type",
     *                      description="insert office_type",
     *                      type="integer",
     *
     *                   ),
     *                 @OA\Property(
     *                      property="name_en",
     *                      description="insert name_en",
     *                      type="text",
     *
     *                   ),
     *                 @OA\Property(
     *                      property="name_bn",
     *                      description="insert name_en",
     *                      type="text",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="office_address",
     *                      description="bangla name of office_address",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="comment",
     *                      description="comment",
     *                      type="text",
     *                   ),
     *                  @OA\Property(
     *                      property="status",
     *                      description="status",
     *                      type="tinyInteger",
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

    public function officeUpdate(OfficeUpdateRequest $request)
    {
        // $validation = Validator::make($request->all(), [
        //     'selectedWards.*' => [
        //         'required',
        //         Rule::unique('office_has_wards', 'ward_id')->whereNot('office_id',$request->id),
        //     ],
        // ]);

        // if ($validation->fails()) {
        //     $officeHasWard = OfficeHasWard::whereIn('ward_id', $request->selectedWards)->whereNot('office_id',$request->id)
        //                     ->with(['parent.parent','office'])->first();
        //     $message = null;
        //     if($officeHasWard){
        //         $message = $officeHasWard->parent->name_en." ward of ".$officeHasWard->parent->parent->name_en." is already assigned to ".$officeHasWard->office->name_en.", ".$officeHasWard->office->assignLocation->name_en;
        //     }
        //     return response()->json([
        //         'success' => false,
        //         'message' => 'Validation failed',
        //         'errors' => $message ?? 'One or more wards are already assigned to another office',
        //     ], 400);
        // }

        try {
            $beforeUpdate = Office::find($request->id);
            $office = $this->OfficeService->updateOffice($request);
            Helper::activityLogUpdate($office, $beforeUpdate, 'Office', 'Office Updated !');

            return OfficeResource::make($office)->additional([
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
     *      path="/admin/office/get/{district_id}",
     *      operationId="getAllOfficeByDistrictId",
     *     tags={"SySTEM-OFFICE-MANAGEMENT"},
     *      summary=" get office by district",
     *      description="get office by district",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of district to return",
     *         in="path",
     *         name="district_id",
     *         @OA\Schema(
     *           type="integer",
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

    public function getAllOfficeByDistrictId($district_id)
    {


        $office = Office::whereDistrictId($district_id)->get();

        return OfficeResource::collection($office)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    /**
     * @OA\Get(
     *      path="/admin/office/destroy/{id}",
     *      operationId="destroyOffice",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary=" destroy Office",
     *      description="Returns office destroy by id",
     *      security={{"bearer_token":{}}},
     *
     *       @OA\Parameter(
     *         description="id of office to return",
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
    public function destroyOffice($id)
    {


        $validator = Validator::make(['id' => $id], [
            'id' => 'required|exists:offices,id',
        ]);

        $validator->validated();

        $office = Office::whereId($id)->first();
        if ($office) {
            $office->wards()->delete();
            $office->delete();
        }
        Helper::activityLogDelete($office, '', 'Office', 'Office Deleted !');

        //        activity("Office")
        //            ->causedBy(auth()->user())
        //            ->log('Office Deleted!!');
        return $this->sendResponse($office, $this->deleteSuccessMessage, Response::HTTP_OK);
    }

    /**
     * @OA\Post(
     *      path="/admin/office/destroy/ward-under-office",
     *      operationId="destroyWardUnderOffice",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary=" destroy Office",
     *      description="Returns office destroy by id",
     *      security={{"bearer_token":{}}},
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
     *                      description="id of the Office",
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
    public function destroyWardUnderOffice()
    {

        $id = request()->id;

        // $validator = Validator::make(['id' => $id], [
        //     'id' => 'required|exists:offices,id',
        // ]);

        $office = OfficeHasWard::where('id', $id)->delete();


        return $this->sendResponse($office, $this->deleteSuccessMessage, Response::HTTP_OK);
    }

    /**
     * @param $location_id
     * @return \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
     */
    public function getAllOfficeList(Request $request): \Illuminate\Http\JsonResponse|\Illuminate\Http\Resources\Json\AnonymousResourceCollection
    {
        try {
            $officeList = $this->OfficeService->getAllOfficeList($request);
            //            return response()->json($beneficiaryList);
            return OfficeDropDownResource::collection($officeList)->additional([
                'success' => true,
                'message' => $this->fetchDataSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            return $this->sendError($th->getMessage(), [], 500);
        }
    }

    public function generatePdf(Request $request)
    {

        set_time_limit(120);
        $searchText = $request->query('searchText');
        $sortBy = $request->query('sortBy') ?? 'name_en';
        $orderBy = $request->query('orderBy') ?? 'asc';

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayComment = [];
        $filterArrayAddress = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAddress[] = ['office_address', 'LIKE', '%' . $searchText . '%'];
        }
        $query = Office::query()
            ->where(function ($query) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayComment, $filterArrayAddress) {
                $query->where($filterArrayNameEn)
                    ->orWhere($filterArrayNameBn)
                    ->orWhere($filterArrayComment)
                    ->orWhere($filterArrayAddress);
            });

        $query->with('assignLocation.parent.parent.parent', 'assignLocation.locationType', 'officeType', 'wards')
            ->orderBy($sortBy, $orderBy);

        $fullData = $query->get();

        $OBJ = $fullData->toArray();
        $CustomInfo = array_map(function ($i, $index) use ($request) {
            return [
                $request->language == "bn" ? Helper::englishToBangla($index + 1) : $index + 1,
                $request->language == "bn" ? Helper::englishToBangla($i['assign_location']['id']) : $i['assign_location']['id'],
                $request->language == "bn" ? Helper::englishToBangla($i['office_type']['value_bn']) : $i['office_type']['value_en'],
                $request->language == "bn" ? $i['name_bn'] : $i['name_en'],
                $request->language == "bn" ? $i['assign_location']['parent']['name_bn'] : $i['assign_location']['parent']['name_en'],
                $request->language == "bn" ? $i['assign_location']['parent']['parent']['name_bn'] : $i['assign_location']['parent']['parent']['name_en']
            ];
        }, $OBJ, array_keys($OBJ));

        $data = ['headerInfo' => $request->header, 'dataInfo' => $CustomInfo, 'fileName' => $request->fileName];

        ini_set("pcre.backtrack_limit", "5000000");
        $pdf = LaravelMpdf::loadView(
            'reports.dynamic',
            $data,
            [],
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
            ]
        );


        return \Illuminate\Support\Facades\Response::stream(
            function () use ($pdf) {
                echo $pdf->output();
            },
            200,
            [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'inline; filename="preview.pdf"',
            ]
        );
    }

    public function getLocationsUnderOfficeArea($office_id){
        $office = Office::find($office_id);
        $selectedWardIds = $office->wards()->pluck('ward_id');
        $location = Location::find($office->assign_location_id);
        if($location == null){
            return [
                "location" => null,
                "office" => $office,
            ];
        }
        if(in_array($office->office_type, [8,9,10,11,35])){
            $location = $location->parent;
            $location->load('children.children.children.children.children');
        }
        // if($office->office_type == 8 || $office->office_type == 10 || $office->office_type == 11){
        //     $location->load('children.children.children');
        // }elseif($office->office_type == 9){
        //     $location = $location->parent;
        //     $location->load('children.children.children.children');
        // }elseif($office->office_type == 35){
        //     $location->load('children.children');
        // }
        function addSelected($children, $selectedIds){
            foreach($children as $child){
                if($child->children){
                    addSelected($child->children, $selectedIds);
                }
                if(in_array($child->id, $selectedIds)){
                    $child->is_selected = true;
                }elseif($child->type=='ward'){
                    $child->is_selected = false;
                }
            }
        };
        addSelected($location->children, $selectedWardIds->toArray());
        return [
            "location" => $location,
            "office" => $office,
            // 'selected_ward_ids' => $selectedWardIds
        ];
    }

    public function assignWardsToOffice(Request $request){
        $validation = Validator::make($request->all(), [
            'ward_ids.*' => [
                'required',
                Rule::unique('office_has_wards', 'ward_id')->whereNot('office_id',$request->office_id)->whereNull('deleted_at'),
            ],
        ]);

        if ($validation->fails()) {
            $officeHasWard = OfficeHasWard::whereIn('ward_id', $request->ward_ids)->whereNot('office_id',$request->office_id)->with(['parent.parent','office'])->first();
            $message = null;
            if($officeHasWard){
                $message = $officeHasWard->parent->name_en." ward of ".$officeHasWard->parent->parent->name_en." is already assigned to ".$officeHasWard->office->name_en.", ".$officeHasWard->office->assignLocation->name_en;
            }
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $message ?? 'One or more wards are already assigned to another office',
            ], 400);
        }

        try{
            $this->OfficeService->assignWardsToOffice($request);
        }catch(\Throwable $t){
            throw $t;
        }

        return [
            'success' => true,
            'message' => $this->updateSuccessMessage,
        ];
    }

    public function getAllOfficeReportPrevious(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $sortBy = $request->query('sortBy') ?? 'name_en';
        $orderBy = $request->query('orderBy') ?? 'asc';
        $startDate = $request->query('from_date');
        $endDate = $request->query('to_date');

        $officeType = $request->query('office_type');
        $divisionId = $request->query('division_id');
        $districtId = $request->query('district_id');
        $thanaId = $request->query('thana_id');
        $cityId = $request->query('city_id');
        $distPouroId = $request->query('dist_pouro_id');
        $upazilaId = $request->query('upazila_id');

        $filterArrayNameEn = [];
        $filterArrayNameBn = [];
        $filterArrayComment = [];
        $filterArrayAddress = [];
        $filterArrayAssignLocationNameEn = [];
        $filterArrayAssignLocationNameBn = [];
        $filterArrayAssignLocationCodeEn = [];

        if ($searchText) {
            $filterArrayNameEn[] = ['name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayNameBn[] = ['name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayComment[] = ['comment', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAddress[] = ['office_address', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAddress[] = ['office_address_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAssignLocationNameEn[] = ['locations.name_en', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAssignLocationNameBn[] = ['locations.name_bn', 'LIKE', '%' . $searchText . '%'];
            $filterArrayAssignLocationCodeEn[] = ['locations.code', '=', $searchText];


            if ($searchText != null) {
                $page = 1;
            }
        }

        $data = Office::query()->with(['assignLocation', 'assignLocation.parent.parent.parent', 'assignLocation.locationType', 'officeType', 'wards' => function ($q){
            $q->with('division','district','city','thana','union','pouro');
        }])
            ->orderBy($sortBy, $orderBy);


        $data->when( $searchText && str_contains($searchText, '%'), function ($q) {
                $q->whereId(null);
            })
            ->when($searchText, function ($q)use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayComment, $filterArrayAddress, $filterArrayAssignLocationNameEn, $filterArrayAssignLocationNameBn, $filterArrayAssignLocationCodeEn ){
                $q->where(function ($query) use ($filterArrayNameEn, $filterArrayNameBn, $filterArrayComment, $filterArrayAddress, $filterArrayAssignLocationNameEn, $filterArrayAssignLocationNameBn, $filterArrayAssignLocationCodeEn ) {
                    $query->where($filterArrayNameEn)
                        ->orWhere($filterArrayNameBn)
                        ->orWhere($filterArrayComment)
                        ->orWhere($filterArrayAddress)
                        ->orWhereHas('assignLocation', function ($q) use ($filterArrayAssignLocationNameEn) {
                            $q->where($filterArrayAssignLocationNameEn);
                        })
                        ->orWhereHas('assignLocation', function ($q) use ($filterArrayAssignLocationNameBn) {
                            $q->where($filterArrayAssignLocationNameBn);
                        })
                        ->orWhereHas('assignLocation', function ($q) use ($filterArrayAssignLocationCodeEn) {
                            $q->where($filterArrayAssignLocationCodeEn);
                        });
                });
            });

        $this->filterByLocation($data);

        if ($startDate && $endDate) {
            $data->whereBetween('created_at', [$startDate, $endDate]);
        }

        if ($request->filled('office_type')) {
            $data->where('office_type', $officeType);
        }

        if ($request->filled('division_id')) {
            $data->whereHas('assignLocation', function ($query) use ($divisionId) {
                $query->where('id', $divisionId)
                    ->orWhere('parent_id', $divisionId)
                    ->orWhereHas('parent', function ($query) use ($divisionId) {
                        $query->where('id', $divisionId)
                            ->orWhereHas('parent', function ($query) use ($divisionId) {
                                $query->where('id', $divisionId)
                                    ->orWhereHas('parent', function ($query) use ($divisionId) {
                                        $query->where('id', $divisionId);
                                    });
                            });
                    });
            });
        }

        if ($request->filled('district_id')) {
            $data->whereHas('assignLocation', function ($query) use ($districtId) {
                $query->where('id', $districtId)
                    ->orWhere('parent_id', $districtId);
            });
        }

        if ($request->filled('thana_id')) {
            $data->whereHas('assignLocation.parent.parent', function ($query) use ($thanaId) {
                $query->where('id', $thanaId);
            });
        }

        if ($request->filled('city_id')) {
            $data->whereHas('assignLocation', function ($query) use ($cityId) {
                $query->where('id', $cityId)
                    ->orWhere('parent_id', $cityId);
            });
        }

        if ($request->filled('dist_pouro_id')) {
            $data->whereHas('assignLocation', function ($query) use ($distPouroId) {
                $query->where('id', $distPouroId)
                    ->orWhere('parent_id', $distPouroId);
            });
        }

        if ($request->filled('upazila_id')) {
            $data->whereHas('assignLocation', function ($query) use ($upazilaId) {
                $query->where('id', $upazilaId)
                    ->orWhere('parent_id', $upazilaId);
            });
        }


        $pageData = $data->paginate($perPage);

        $totalBeneficiaries = 0;
        $beneficiary_status_1 = 0;
        $beneficiary_status_2 = 0;
        $beneficiary_status_3 = 0;
        $beneficiary_live_verify = 0;
        $totalAllottedBeneficiaries = 0;
        $totalAllottedAmount = 0;

        $officesWithStats = $pageData->getCollection()->map(function ($office) use (&$totalBeneficiaries, &$beneficiary_status_1, &$beneficiary_status_2, &$beneficiary_status_3, &$beneficiary_live_verify, &$totalAllottedBeneficiaries, &$totalAllottedAmount) {

            $office->total_beneficiaries = 0;
            $office->beneficiary_status_1 = 0;
            $office->beneficiary_status_2 = 0;
            $office->beneficiary_status_3 = 0;
            $office->beneficiary_live_verify = 0;
            $office->beneficiary_has_account = 0;
            $office->beneficiary_has_no_account = 0;

            $office->allotted_beneficiaries = 0;
            $office->allotted_amount = 0;

            $office->payroll_total_beneficiaries = 0;
            $office->payroll_total_amount = 0;
            $office->total_payroll_approved = 0;
            $office->total_payroll_pending = 0;
            $office->total_payroll_rejected = 0;

            // Determine mapping columns
            $allotmentColumn = null;
            $beneficiaryColumn = null;

            if ($office->office_type == 6) {
                $allotmentColumn = 'division_id';
                $beneficiaryColumn = 'permanent_division_id';
            } elseif ($office->office_type == 7) {
                $allotmentColumn = ['district_id', 'division_id'];
                $beneficiaryColumn = ['permanent_district_id', 'permanent_division_id'];
            } elseif ( $office->office_type == 8 || $office->office_type == 10 || $office->office_type == 11) {
                $allotmentColumn = 'upazila_id';
                $beneficiaryColumn = 'permanent_upazila_id';
            }  elseif ($office->office_type == 9) {
                $allotmentColumn = 'city_corp_id';
                $beneficiaryColumn = 'permanent_city_corp_id';
            }  elseif ($office->office_type == 35) {
                $allotmentColumn = 'district_pourashava_id';
                $beneficiaryColumn = 'permanent_district_pourashava_id';
            }  else {
                return $office;
            }

            // --- Beneficiaries ---
            $beneficiaryQuery = \App\Models\Beneficiary::query();

            if (is_array($beneficiaryColumn)) {
                $beneficiaryQuery->where(function ($q) use ($beneficiaryColumn, $office) {
                    foreach ($beneficiaryColumn as $col) {
                        $q->orWhere($col, $office->assign_location_id);
                    }
                });
            } else {
                $beneficiaryQuery->where($beneficiaryColumn, $office->assign_location_id);
            }

            $counts = (clone $beneficiaryQuery)
                ->selectRaw('status, COUNT(*) as count')
                ->groupBy('status')
                ->pluck('count', 'status');

            $liveVerified = (clone $beneficiaryQuery)
                ->where('is_verified', 1)
                ->count();

            $beneficiaryHasAccount = (clone $beneficiaryQuery)
                ->whereNotNull('account_number')
                ->count();

            $beneficiaryHasNoAccount = $counts->sum() - $beneficiaryHasAccount;

            $office->total_beneficiaries = $counts->sum();
            $office->beneficiary_status_1 = $counts[1] ?? 0;
            $office->beneficiary_status_2 = $counts[2] ?? 0;
            $office->beneficiary_status_3 = $counts[3] ?? 0;
            $office->beneficiary_live_verify = $liveVerified;
            $office->beneficiary_has_account = $beneficiaryHasAccount;
            $office->beneficiary_has_no_account = $beneficiaryHasNoAccount;

            $totalBeneficiaries += $office->total_beneficiaries;
            $beneficiary_status_1 += $office->beneficiary_status_1;
            $beneficiary_status_2 += $office->beneficiary_status_2;
            $beneficiary_status_3 += $office->beneficiary_status_3;
            $beneficiary_live_verify += $office->beneficiary_live_verify;

            // --- Allotments ---
            $allotmentQuery = \App\Models\Allotment::query();

            if (is_array($allotmentColumn)) {
                $allotmentQuery->where(function ($q) use ($allotmentColumn, $office) {
                    foreach ($allotmentColumn as $col) {
                        $q->orWhere($col, $office->assign_location_id);
                    }
                });
            } else {
                $allotmentQuery->where($allotmentColumn, $office->assign_location_id);
            }


            $allotmentSummary = (clone $allotmentQuery)
                ->selectRaw('SUM(total_beneficiaries) as total_beneficiaries, SUM(total_amount) as total_amount')
                ->first();

            $office->allotted_beneficiaries = $allotmentSummary->total_beneficiaries ?? 0;
            $office->allotted_amount = $allotmentSummary->total_amount ?? 0;

            $totalAllottedBeneficiaries += $office->allotted_beneficiaries;
            $totalAllottedAmount += $office->allotted_amount;

            // --- Payrolls ---
            $payrollSummary = \App\Models\Payroll::query()
                ->join('allotments', 'payrolls.allotment_id', '=', 'allotments.id')
                ->where(function ($q) use ($allotmentColumn, $office) {
                    if (is_array($allotmentColumn)) {
                        foreach ($allotmentColumn as $col) {
                            $q->orWhere("allotments.$col", $office->assign_location_id);
                        }
                    } else {
                        $q->where("allotments.$allotmentColumn", $office->assign_location_id);
                    }
                })
                ->selectRaw('SUM(payrolls.total_beneficiaries) as total_beneficiaries, SUM(payrolls.total_amount) as total_amount')
                ->first();

            $office->payroll_total_beneficiaries = $payrollSummary->total_beneficiaries ?? 0;
            $office->payroll_total_amount = $payrollSummary->total_amount ?? 0;

            // --- PayrollDetails ---
            $allotmentIds = \App\Models\Allotment::query()
                ->when(is_array($allotmentColumn), function ($q) use ($allotmentColumn, $office) {
                    $q->where(function ($q2) use ($allotmentColumn, $office) {
                        foreach ($allotmentColumn as $col) {
                            $q2->orWhere($col, $office->assign_location_id);
                        }
                    });
                }, function ($q) use ($allotmentColumn, $office) {
                    $q->where($allotmentColumn, $office->assign_location_id);
                })
                ->pluck('id');

            $payrollIds = \App\Models\Payroll::whereIn('allotment_id', $allotmentIds)->pluck('id');

            if ($payrollIds->isNotEmpty()) {
                $payrollDetails = \App\Models\PayrollDetail::whereIn('payroll_id', $payrollIds);

                $office->total_payroll_approved = (clone $payrollDetails)->where('status_id', 2)->count();
                $office->total_payroll_pending = (clone $payrollDetails)->where('status_id', 1)->where('is_set', 1)->count();
                $office->total_payroll_rejected = (clone $payrollDetails)->where('status_id', 3)->count();
            }

            return $office;
        });




//        $pageData = $data->paginate($perPage);

//        \Log::info("office report - total: ", ['data' => $pageData->items()]);//
//        return $pageData;

        return response()->json([
            'data' => $officesWithStats,
            'meta' => [
                'pagination' => [
                    'total' => $pageData->total(),
                    'per_page' => $pageData->perPage(),
                    'current_page' => $pageData->currentPage(),
                    'last_page' => $pageData->lastPage(),
                ],
                'beneficiary_aggregation' => [
                    'total_beneficiaries' => $totalBeneficiaries,
                    'beneficiary_status_1' => $beneficiary_status_1,
                    'beneficiary_status_2' => $beneficiary_status_2,
                    'beneficiary_status_3' => $beneficiary_status_3,
                    'beneficiary_live_verify' => $beneficiary_live_verify,
                ],
                'allotment_aggregation' => [
                    'total_allotted_beneficiaries' => $totalAllottedBeneficiaries ?? 0,
                    'total_allotted_amount' => $totalAllottedAmount ?? 0,
                ]
            ]
        ]);


    }

    public function getAllOfficeReport(Request $request)
    {
        // 1. Pagination and optional filters
        $perPage = $request->get('per_page', 10);

        $officeQuery = Office::select(
            'id',
            'name_en',
            'name_bn',
            'office_address',
            'office_address_bn',
            'office_type',
            'assign_location_id'
        )
            ->with([
                'officeType',
                'assignLocation',
                'assignLocation.parent.parent.parent',
                'assignLocation.locationType',
//                'wards' => function ($q) {
//                    $q->with(['division', 'district', 'city', 'thana', 'union', 'pouro']);
//                }
            ]);

        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage', 10);
        $page = $request->query('page', 1);
        $sortBy = $request->query('sortBy') ?? 'name_en';
        $orderBy = $request->query('orderBy') ?? 'asc';
        $startDate = $request->query('from_date');
        $endDate = $request->query('to_date');

        $officeType = $request->query('office_type');
        $divisionId = $request->query('division_id');
        $districtId = $request->query('district_id');
        $thanaId = $request->query('thana_id');
        $cityId = $request->query('city_id');
        $distPouroId = $request->query('dist_pouro_id');
        $upazilaId = $request->query('upazila_id');

        // Optional filtering
        if ($request->filled('office_type')) {
            $officeQuery->where('office_type', $officeType);
        }
        if ($request->filled('division_id')) {
            $officeQuery->whereHas('assignLocation', function ($query) use ($divisionId) {
                $query->where('id', $divisionId)
                    ->orWhere('parent_id', $divisionId)
                    ->orWhereHas('parent', function ($query) use ($divisionId) {
                        $query->where('id', $divisionId)
                            ->orWhereHas('parent', function ($query) use ($divisionId) {
                                $query->where('id', $divisionId)
                                    ->orWhereHas('parent', function ($query) use ($divisionId) {
                                        $query->where('id', $divisionId);
                                    });
                            });
                    });
            });
        }
        if ($request->filled('district_id')) {
            $officeQuery->whereHas('assignLocation', function ($query) use ($districtId) {
                $query->where('id', $districtId)
                    ->orWhere('parent_id', $districtId);
            });
        }
        if ($request->filled('thana_id')) {
            $officeQuery->whereHas('assignLocation.parent.parent', function ($query) use ($thanaId) {
                $query->where('id', $thanaId);
            });
        }
        if ($request->filled('city_id')) {
            $officeQuery->whereHas('assignLocation', function ($query) use ($cityId) {
                $query->where('id', $cityId)
                    ->orWhere('parent_id', $cityId);
            });
        }
        if ($request->filled('dist_pouro_id')) {
            $officeQuery->whereHas('assignLocation', function ($query) use ($distPouroId) {
                $query->where('id', $distPouroId)
                    ->orWhere('parent_id', $distPouroId);
            });
        }
        if ($request->filled('upazila_id')) {
            $officeQuery->whereHas('assignLocation', function ($query) use ($upazilaId) {
                $query->where('id', $upazilaId)
                    ->orWhere('parent_id', $upazilaId);
            });
        }


        $offices = $officeQuery->paginate($perPage);

        // 2. Collect location IDs by office type
        $divisionIds = [];
        $districtIds = [];
        $upazilaIds  = [];
        $districtPouroIds = [];
        $cityCorpIds    = [];
        $wardIds        = [];

        foreach ($offices as $office) {
            switch ($office->office_type) {
                case 6:
                    $divisionIds[] = $office->assign_location_id;
                    break;
                case 7:
                    $districtIds[] = $office->assign_location_id;
                    break;
                case 8:
                case 10:
                case 11:
                    $upazilaIds[] = $office->assign_location_id;
                    break;
                case 9:
                    $cityCorpIds[] = $office->assign_location_id;
                    $wardIds = $office->wards->pluck('id')->toArray() ;
                    break;
                case 35:
                    $districtPouroIds[] = $office->assign_location_id;
                    break;
            }
        }

        // 3. Get beneficiaries in bulk
        $beneficiaries = Beneficiary::select('id', 'permanent_division_id', 'permanent_district_id', 'permanent_upazila_id', 'permanent_district_pourashava_id', 'permanent_city_corp_id', 'status', 'is_verified', 'account_number')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($divisionIds, $districtIds, $upazilaIds, $districtPouroIds, $cityCorpIds, $wardIds) {
                if (!empty($divisionIds)) {
                    $query->orWhereIn('permanent_division_id', $divisionIds);
                }
                if (!empty($districtIds)) {
                    $query->orWhereIn('permanent_district_id', $districtIds);
                }
                if (!empty($upazilaIds)) {
                    $query->orWhereIn('permanent_upazila_id', $upazilaIds);
                }
                if (!empty($districtPouroIds)) {
                    $query->orWhereIn('permanent_district_pourashava_id', $districtPouroIds);
                }
                if (!empty($cityCorpIds) && !empty($wardIds)) {
                    $query->orWhere(function ($q) use ($cityCorpIds, $wardIds) {
                        $q->whereIn('permanent_city_corp_id', $cityCorpIds)
                            ->whereIn('permanent_ward_id', $wardIds);
                    });
                } elseif (!empty($cityCorpIds)) {
                    $query->orWhereIn('permanent_city_corp_id', $cityCorpIds);
                }
            })
            ->get();

        $groupedBeneficiaries = [
            'division' => $beneficiaries->groupBy('permanent_division_id'),
            'district' => $beneficiaries->groupBy('permanent_district_id'),
            'upazila'  => $beneficiaries->groupBy('permanent_upazila_id'),
            'districtPouroshava'    => $beneficiaries->groupBy('permanent_district_pourashava_id'),
            'cityCorporation'       => $beneficiaries->groupBy('permanent_city_corp_id'),
        ];

        // 4. Get allotments in bulk
        $allotments = Allotment::select('id', 'division_id', 'district_id', 'upazila_id', 'district_pourashava_id', 'city_corp_id', 'total_beneficiaries', 'total_amount')
            ->whereNull('deleted_at')
            ->where(function ($query) use ($divisionIds, $districtIds, $upazilaIds, $districtPouroIds, $cityCorpIds) {
                if (!empty($divisionIds)) {
                    $query->orWhereIn('division_id', $divisionIds);
                }
                if (!empty($districtIds)) {
                    $query->orWhereIn('district_id', $districtIds);
                }
                if (!empty($upazilaIds)) {
                    $query->orWhereIn('upazila_id', $upazilaIds);
                }
                if (!empty($districtPouroIds)) {
                    $query->orWhereIn('district_pourashava_id', $districtPouroIds);
                }
                if (!empty($cityCorpIds)) {
                    $query->orWhereIn('city_corp_id', $cityCorpIds);
                }
            })
            ->get();

        $groupedAllotments = [
            'division' => $allotments->groupBy('division_id'),
            'district' => $allotments->groupBy('district_id'),
            'upazila'  => $allotments->groupBy('upazila_id'),
            'districtPouroshava'  => $allotments->groupBy('district_pourashava_id'),
            'cityCorporation'  => $allotments->groupBy('city_corp_id'),
        ];

        // payroll block
        $allotmentIds = $allotments->pluck('id');

        $payrolls = Payroll::select('id', 'allotment_id', 'total_beneficiaries', 'total_amount')
            ->whereIn('allotment_id', $allotmentIds)
            ->get()
            ->groupBy('allotment_id');

        $payrollDetailQuery = PayrollDetail::select('payroll_id', 'status_id', 'is_set');

        $payrollIds = $payrolls->flatten()->pluck('id');

        $payrollDetails = $payrollIds->isNotEmpty()
            ? $payrollDetailQuery->whereIn('payroll_id', $payrollIds)->get()->groupBy('payroll_id')
            : collect();

        // 5. Enrich paginated offices with stats
        $offices->getCollection()->transform(function ($office) use ($groupedBeneficiaries, $groupedAllotments, $payrolls, $payrollDetails) {

            $locationId = $office->assign_location_id;
            $level = match(true) {
                $office->office_type == 6 => 'division',
                $office->office_type == 7 => 'district',
                in_array($office->office_type, [8, 10, 11]) => 'upazila',
                $office->office_type == 9  => 'cityCorporation',
                $office->office_type == 35 => 'districtPouroshava',
            };

            $beneficiaries = $groupedBeneficiaries[$level][$locationId] ?? collect();

            $office->total_beneficiaries  = $beneficiaries->count();

            $office->beneficiary_status_1 = $beneficiaries->where('status', 1)->count();
            $office->beneficiary_status_2 = $beneficiaries->where('status', 2)->count();
            $office->beneficiary_status_3 = $beneficiaries->where('status', 3)->count();

            $office->beneficiary_live_verify = $beneficiaries->where('is_verified', 1)->count();
            $office->beneficiary_has_account = $beneficiaries->whereNotNull('account_number')->count();
            $office->beneficiary_has_no_account = $office->total_beneficiaries - $office->beneficiary_has_account;

            // ---------- Allotments ----------
            $officeAllotments = $groupedAllotments[$level][$locationId] ?? collect();

            $office->allotted_beneficiaries = $officeAllotments->sum('total_beneficiaries');
            $office->allotted_amount        = $officeAllotments->sum('total_amount');

            // ---------- Payrolls ----------
            $officeAllotmentIds = $officeAllotments->pluck('id');

            $relatedPayrolls = $officeAllotmentIds->flatMap(fn ($id) => $payrolls[$id] ?? collect());
            $relatedPayrollIds = $relatedPayrolls->pluck('id');

            $relatedDetails = $relatedPayrollIds->flatMap(fn ($pid) => $payrollDetails[$pid] ?? collect());

            $office->payroll_total_beneficiaries    = $relatedPayrolls->sum('total_beneficiaries');
            $office->payroll_total_amount           = $relatedPayrolls->sum('total_amount');

            $office->total_payroll_approved         = $relatedDetails->where('status_id', 2)->count();
            $office->total_payroll_pending          = $relatedDetails->where('status_id', 1)->where('is_set', 1)->count();
            $office->total_payroll_rejected         = $relatedDetails->where('status_id', 3)->count();

            return $office;
        });

        return response()->json([
            'success' => true,
            'data'    => $offices,
            'meta'    => [
                'pagination' => [
                    'current_page' => $offices->currentPage(),
                    'last_page'    => $offices->lastPage(),
                    'per_page'     => $offices->perPage(),
                    'total'        => $offices->total(),
                ]
            ]
        ]);
    }

}
