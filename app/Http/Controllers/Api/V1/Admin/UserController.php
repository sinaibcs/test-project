<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Requests\Admin\User\UploadImageSignatureRequest;
use Auth;
use Cache;
use App\Models\User;
use App\Models\Office;
use App\Helpers\Helper;
use App\Jobs\SendEmail;
use App\Models\Location;
use App\Jobs\UserCreateJob;
use App\Models\UserHasWard;
use App\Mail\UserCreateMail;
use Illuminate\Http\Request;
use App\Mail\PasswordChanged;
use Illuminate\Http\Response;
use App\Http\Traits\RoleTrait;
use App\Http\Traits\UserTrait;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\LocationTrait;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;
use App\Http\Traits\PermissionTrait;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;
use App\Http\Services\Auth\AuthService;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;
use App\Exceptions\AuthBasicErrorException;
use App\Http\Requests\Admin\User\UserRequest;
use App\Http\Services\Admin\User\UserService;
use App\Http\Services\Notification\SMSservice;
use Illuminate\Validation\ValidationException;
use App\Http\Resources\Admin\User\UserResource;
use App\Http\Requests\Admin\User\UserUpdateRequest;
use App\Http\Resources\Admin\Office\OfficeResource;
use App\Http\Services\Admin\User\OfficeHeadService;
use App\Http\Requests\Admin\User\UploadImageRequest;


class UserController extends Controller
{
    use MessageTrait, UserTrait, RoleTrait, PermissionTrait, LocationTrait;
    private $UserService;

    public function __construct(UserService $UserService, public OfficeHeadService $officeHeadService, public SMSservice $SMSservice, public AuthService $authService)
    {
        $this->UserService = $UserService;
        $this->authService = $authService;
        $this->SMSservice = $SMSservice;
    }

    /**
     * @OA\Get(
     *     path="/admin/user/get",
     *      operationId="getAllUserPaginated",
     *      tags={"ADMIN-USER"},
     *      summary="get paginated users",
     *      description="get paginated users",
     *      security={{"bearer_token":{}}},
     *     @OA\Parameter(
     *         name="searchText",
     *         in="query",
     *         description="search by name, phone, email, username",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="userId",
     *         in="query",
     *         description="search by user_id",
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Parameter(
     *         name="officeId",
     *         in="query",
     *         description="search by office_id",
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
    // public function getAllUserPaginated(Request $request)
    // {
    //     $perPage = $request->query('perPage', 10);
    //     $page = $request->query('page', 1);

    //     $data = User::query()
    //         ->with(['office', 'assign_location.parent.parent.parent.parent', 'officeTypeInfo', 'roles', 'committee', 'userWards', 'assign_location', 'unions'])
    //         ->orderByDesc('id');

    //     // Date filter
    //     $data->when($request->filled(['from_date', 'to_date']), function ($query) use ($request) {
    //         $query->whereBetween('created_at', [$request->from_date, $request->to_date]);
    //     });

    //     // User type conditions
    //     $data->when($request->filled('user_type'), function ($query) use ($request) {
    //         if ($request->user_type == 1) {
    //             $query->where('user_type', 2)->whereNull('committee_type_id')->whereNotNull('office_type');
    //         } elseif ($request->user_type == 2) {
    //             $query->where('user_type', 2)->whereNotNull('committee_type_id')->whereNull('office_type');
    //         } elseif ($request->user_type == 3) {
    //             $query->where('user_type', 1)->whereNull('committee_type_id')->whereNull('office_type');
    //         }
    //     });

    //     // General filters
    //     $filters = [
    //         'office_type' => 'office_type',
    //         'committee_type' => 'committee_type_id',
    //         'office_id' => 'office_id',
    //         'committee_id' => 'committee_id'
    //     ];
    //     foreach ($filters as $requestKey => $dbColumn) {
    //         $data->when($request->filled($requestKey), fn($query) => $query->where($dbColumn, $request->$requestKey));
    //     }

    //     // Location-based filtering
    //     $locationFilters = ['division_id', 'district_id', 'upazila_id', 'thana_id', 'city_corpo_id', 'paurashava_id'];
    //     foreach ($locationFilters as $location) {
    //         $data->when($request->filled($location), function ($query) use ($location, $request) {
    //             $query->whereHas('assign_location', fn($q) => $q->where('id', $request->$location)->orWhere('parent_id', $request->$location));
    //         });
    //     }

    //     // Search filters
    //     if ($searchText = $request->query('searchText')) {
    //         $data->where(function ($query) use ($searchText) {
    //             $query->where('full_name', 'LIKE', "%{$searchText}%")
    //                 ->orWhere('username', 'LIKE', "%{$searchText}%")
    //                 ->orWhere('email', 'LIKE', "%{$searchText}%")
    //                 ->orWhere('mobile', 'LIKE', "%{$searchText}%");
    //         });
    //     }

    //     // Restrict to users under office head
    //     $data->whereIn('id', $this->officeHeadService->getUsersUnderOffice());

    //     return $data->paginate($perPage, ['*'], 'page', $page);
    // }

    public function getAllUserPaginated(Request $request)
    {
        // Retrieve the query parameters
        $searchText = $request->query('searchText');
        $userId = $request->query('userId');
        $officeId = $request->query('officeId');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $userType = $request->query('user_type');
        $officeType = $request->query('office_type');
        $committeeType = $request->query('committee_type');
        $divisionId = $request->query('division_id');
        $districtId = $request->query('district_id');
        $upazilaId = $request->query('upazila_id');
        $thanaId = $request->query('thana_id');
        $office_id = $request->query('office_id');
        $committeeId = $request->query('committee_id');
        $cityCorpoId = $request->query('city_corpo_id');
        $paurashavaId = $request->query('paurashava_id');
        $startDate = $request->from_date;
        $endDate = $request->to_date;


        $data = User::query()
            ->with(['office', 'assign_location.parent.parent.parent.parent', 'officeTypeInfo', 'roles', 'committee', 'userWards', 'assign_location', 'unions'])
            ->orderByDesc('id');

        if ($startDate && $endDate) {
            $data->whereBetween('created_at', [$startDate, $endDate]);
//                ->orWhere('created_at', $startDate);
        }

        if ($request->filled('user_type')) {
            if ($userType == 1) {
                $data->where('user_type', 2)->whereNull('committee_type_id')->whereNotNull('office_type');
            }
            if ($userType == 2) {
                $data->where('user_type', 2)->whereNotNull('committee_type_id')->whereNull('office_type');
            }
            if ($userType == 3) {
                $data->where('user_type', 1)->whereNull('committee_type_id')->whereNull('office_type');
            }
        }
        if ($request->filled('office_type')) {
            $data->where('office_type', $officeType);
        }
        if ($request->filled('committee_type')) {
            $data->where('committee_type_id', $committeeType);
        }
        if ($request->filled('division_id')) {

            if ($userType == 1) {
                $data->where('user_type', 2)->whereNull('committee_type_id')->whereNotNull('office_type')
                    ->where('office_type', $officeType)
                    ->whereHas('assign_location', function ($query) use ($divisionId) {
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
            if ($userType == 2) {
                $data->where('user_type', 2)->whereNotNull('committee_type_id')->whereNull('office_type')
                    ->where('committee_type_id', $committeeType)
                    ->whereHas('assign_location', function ($query) use ($divisionId) {
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
        }

        if ($request->filled('district_id')) {
            $data->whereHas('assign_location', function ($query) use ($districtId) {
                $query->where('id', $districtId)
                    ->orWhere('parent_id', $districtId);
            });
        }

        if ($request->filled('upazila_id')) {
            $data->whereHas('assign_location', function ($query) use ($upazilaId) {
                $query->where('id', $upazilaId)
                    ->orWhere('parent_id', $upazilaId);
            });
        }

        if ($request->filled('thana_id')) {
            $data->whereHas('assign_location', function ($query) use ($thanaId) {
                $query->where('id', $thanaId)
                    ->orWhere('parent_id', $thanaId);
            });
        }

        if ($request->filled('city_corpo_id')) {
            $data->whereHas('assign_location', function ($query) use ($cityCorpoId) {
                $query->where('id', $cityCorpoId)
                    ->orWhere('parent_id', $cityCorpoId);
            });
        }

        if ($request->filled('paurashava_id')) {
            $data->whereHas('assign_location', function ($query) use ($paurashavaId) {
                $query->where('id', $paurashavaId)
                    ->orWhere('parent_id', $paurashavaId);
            });
        }


        if ($request->filled('office_id')) {
            $data->where('office_id', $office_id);
        }
        if ($request->filled('committee_id')) {
            $data->where('committee_id', $committeeId);
        }


        $filterArrayName = [];
        $filterArrayUserName = [];
        $filterArrayUserId = [];
        $filterArrayEmail = [];
        $filterArrayPhone = [];
        $filterArrayOfficeId = [];

        if ($searchText) {
            $filterArrayName[] = ['full_name', 'LIKE', '%' . $searchText . '%'];
            $filterArrayUserName[] = ['username', 'LIKE', '%' . $searchText . '%'];
            $filterArrayUserId[] = ['user_id', 'LIKE', '%' . $userId . '%'];
            $filterArrayEmail[] = ['email', 'LIKE', '%' . $searchText . '%'];
            $filterArrayPhone[] = ['mobile', 'LIKE', '%' . $searchText . '%'];
            $filterArrayOfficeId[] = ['office_id', 'LIKE', '%' . $officeId . '%'];
        }


        // check this user is super-admin or not if not then check this user is office head or not if yes then get users under this office
        $data->where(function ($query) use ($filterArrayName, $filterArrayUserName, $filterArrayUserId, $filterArrayEmail, $filterArrayPhone, $filterArrayOfficeId) {
            $query->where($filterArrayName)
                ->orWhere($filterArrayUserName)
                //              ->orWhere($filterArrayUserId)
                ->orWhere($filterArrayEmail)
                //              ->orWhere($filterArrayOfficeId)
                ->orWhere($filterArrayPhone);
        })
            ->whereIn('id', $this->officeHeadService->getUsersUnderOffice());


        $users = $data->paginate($perPage, ['*'], 'page');
        // }
        return $users;
//        return UserResource::collection($users)->additional([
//            'success' => true,
//            'message' => $this->fetchSuccessMessage,
//        ]);
    }


    public function getUsersId()
    {
        return $this->officeHeadService->getUsersUnderOffice();
    }





    /**
     *
     * @OA\Post(
     *      path="/admin/user/insert",
     *      operationId="insertUser",
     *      tags={"ADMIN-USER"},
     *      summary="insert a user",
     *      description="insert a user",
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
     *                      property="full_name",
     *                      description="full name",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="username",
     *                      description="unique username",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="mobile",
     *                      description="Mobile number",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="email",
     *                      description="user email address",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="role_id[0]",
     *                      description="id of role",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="status",
     *                      description="enter status. ex: 0 => pending, 1 => active",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="office_type",
     *                      description="id of office type",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="office_id",
     *                      description="id of office",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="committee_id",
     *                      description="id of Committtee",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="division_id",
     *                      description="id of division",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="district_id",
     *                      description="id of district",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="thana_id",
     *                      description="id of thana",
     *                      type="text",
     *                   ),
     *                   @OA\Property(
     *                      property="city_corpo_id",
     *                      description="id of city corporation",
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


    //user Logics
    // 1. check if user is super admin or not
    // 2. if not super admin then check if user is office head or not

    // Users under Offices
    // 1. if user is office head then check if office already has a office head or not
    // 2. if office already has a office head then return error
    // 3. if office does not have a office head then system will allow to create office head user else create any office user except office Head
    // 4. if user is super admin then create any user

    // Users under Committees
    ////// Some rules for creating users under committees
    ///// Commiittee does not must belong to a office But there is ID assigned to it.

    // 1. if the user has a committee type then the user will be created under that committee type - which means that the users will have the committtee ID


    public function checkWardOnStore($request)
    {
        $wards = $request->office_ward_id ? explode(',', $request->office_ward_id) : [];

        if (UserHasWard::whereIn('ward_id', $wards)->exists()) {
            throw ValidationException::withMessages([
                'office_id' => 'This office already has a office head',
            ]);
        }
    }

    public function insertUser(UserRequest $request)
    {

        $password = Helper::GeneratePassword();
        // check any user assign this office as a officeHead role permission or not and this request roles has officeHead role or not
        if ($request->role_id && $request->user_type == 1) {
            $role = Role::whereName($this->officeHead)->first();
            if (in_array($role->id, $request->role_id)) {
                $officeHead = User::where('office_id', $request->office_id)->whereHas('roles', function ($query) {
                    $query->where('name', $this->officeHead);
                })->first();

                if ($officeHead && !in_array($request->office_type, [9, 10])) {
                    return $this->sendError('This office already has a office head', [
                        'office_id' => 'This office already has a office head',
                    ], 422);
                } else {
                    $this->checkWardOnStore($request);
                }
            }
        }

        $user = $this->UserService->createUser($request, $password);

        Helper::activityLogInsert($user, '', 'User', 'User Created !');


        return UserResource::make($user)->additional([
            'success' => true,
            'message' => $this->insertSuccessMessage,
        ]);
    }


    public function checkWardOnUpdate($request, $userId)
    {
        $wards = $request->office_ward_id ? explode(',', $request->office_ward_id) : [];

        if (UserHasWard::whereIn('ward_id', $wards)->whereNot('user_id', $userId)->exists()) {
            throw ValidationException::withMessages([
                'office_id' => 'This office already has a office head',
            ]);
        }
    }

    public function update(UserUpdateRequest $request, $id)
    {
        try {
            if ($request->role_id && $request->user_type == 1) {
                $role = Role::whereName($this->officeHead)->first();
                if (in_array($role->id, $request->role_id)) {
                    $officeHead = User::where('office_id', $request->office_id)->whereHas('roles', function ($query) {
                        $query->where('name', $this->officeHead);
                    })->whereNot('id', $id)
                        ->first();
                    if ($officeHead && !in_array($request->office_type, [9, 10])) {
                        return $this->sendError('This office already has a office head', [], 500);
                    } else {
                        $this->checkWardOnUpdate($request, $id);
                    }
                }
            }


            $beforeUpdate            = User::findOrFail($id);

            $user = $this->UserService->upddateUser($request, $id);

            Helper::activityLogUpdate($user, $beforeUpdate, 'User', 'User Updated !');

            return UserResource::make($user)->additional([
                'success' => true,
                'message' => $this->updateSuccessMessage,
            ]);
        } catch (\Exception $e) {
            \DB::rollBack();

            $error = $e->getMessage();

            return $this->sendError($error, [], 500);
        }
    }



    public function approveUser($user)
    {
        $password = Helper::GeneratePassword();

        $user->status = 1;
        $user->password = bcrypt($user->salt . $password);
        $user->save();

        $tokenLink = env('APP_FRONTEND_URL') . '/browser-registration';

        $message = "Welcome to the CTM application.Your account has been approved." .
            "\nTo register your device please visit {$tokenLink} then a send device registration request."
            .
            "\nOnce your device is registered you can access the CTM Application using following credentials:
        \nUsername: " . $user->username
            . "\nPassword: " . $password .
            "\nLogin URL: " . env('APP_FRONTEND_URL') . '/login'
            . "\n-MIS, DSS";

        Log::info('password-' . $user->id, [$message]);

        $this->SMSservice->sendSms($user->mobile, $message);

        //        $this->dispatch(new UserCreateJob($user->email,$user->username, $password));

        Mail::to($user->email)->send(new UserCreateMail($user->email, $user->username, $password, $user->full_name));
    }




    public function changeStatus($id)
    {
        $user = User::findOrFail($id);
        $beforeUpdate = User::findOrFail($id);

        $user->status == 0 ? $this->approveUser($user)
            : $this->updateStatus($user);

        $status = $user->fresh()->status;

        Helper::activityLogUpdate($user, $beforeUpdate, 'User', 'User Change Status !');

        $status = $status == 1 ? 'active' : ($status == 2 ? 'banned' : ($status == $this->userAccountInactive ? 'inactive' : 'not changed'));

        return $this->sendResponse($user, "User is $status");
    }


    //1 = active, 2 = banned, 5 = inactive
    public function updateStatus($user)
    {
        $user->status = match ($user->status) {
            1 => 5,
            2 => 1,
            5 => 1,
            default => $user->status
        };

        $user->save();
    }


    public function banUser($id)
    {
        $user = User::findOrFail($id);

        if ($user->status != 2) {
            $user->prev_status = $user->status;
            $user->status = 2;
        } else {
            $user->status = $user->prev_status;
        }

        $user->save();


        $status = $user->status == 2 ? 'banned' : 'un-banned';

        return $this->sendResponse($user, "User has been $status");
    }


    public function sendSmsTest(Request $request)
    {
        $url = "bulksmsbd.net/api/smsapi";

        $message = $request->message ?: "Your OTP is 12132\n-MIS, DSS";
        $number = $request->number ?: "01747970935";


        $data = [
            'api_key' => "xp143oLW8GJtKk3ggwxW",
            'type' => "text",
            'message' => $message,
            'number' => $number,
            'senderid' => "8809617617434",
        ];

        $response = Http::contentType('application/json')
            ->post($url, $data);

        return $response->json();
    }


    public function sendMail(Request $request)
    {
        $user = User::find(1);
        $user->email = $request->email ?: "tarikul5357@gmail.com";

        $this->dispatch(new UserCreateJob($user->email, $user->username, '0000', 'Queue'));
//        Mail::to($user->email)->send(new UserCreateMail($user->email, $user->username, '1234', $user->full_name));
    }


    /**
     *
     * @OA\Post(
     *      path="/admin/user/office/by-location",
     *      operationId="getOfficeByLocationAssignId",
     *      tags={"SYSTEM-OFFICE-MANAGEMENT"},
     *      summary="get a office",
     *      description="get a office",
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
     *                      property="assign_location_id",
     *                      description="get office by assign location Id",
     *                      type="integer",
     *
     *                   ),
     *                   @OA\Property(
     *                      property="office_type_id",
     *                      description="get office by office type Id",
     *                      type="integer",
     *
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
    public function getOfficeByLocationAssignId(Request $request)
    {
        try {
            $getAssignLocationId = [];
            if ($request->has('location_type_id')) {
                $getAssignLocationId[] = ['assign_location_id', $request->location_type_id];
            }
            $office = Office::query()
                ->where(function ($query) use ($getAssignLocationId, $request) {
                    $query->where($getAssignLocationId)
                        ->where('office_type', $request->office_type_id);
                })
                ->whereStatus(1)
                ->with('assignLocation.parent.parent.parent', 'assignLocation.locationType', 'officeType')
                ->get();

            return OfficeResource::collection($office)->additional([
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
     * @OA\Delete(
     *      path="/admin/user/destroy/{id}",
     *      operationId="destroyUser",
     *      tags={"ADMIN-USER"},
     *      summary="delete a user",
     *      description="delete a user",
     *      security={{"bearer_token":{}}},
     *
     *
     *       @OA\Parameter(
     *         name="id",
     *         in="path",
     *         description="id of user",
     *         required=true,
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
     *        )
     *     )
     *
     */
    public function destroyUser($id)
    {
        try {
            $user = User::findOrFail($id);
            $user->delete();

            Helper::activityLogDelete($user, '', 'User', 'User Deleted !');

            return UserResource::make($user)->additional([
                'success' => true,
                'message' => $this->deleteSuccessMessage,
            ]);
        } catch (\Throwable $th) {
            //throw $th;
            return $this->sendError($th->getMessage(), [], 500);
        }
    }


    public function getRoles()
    {
        $isAdmin = auth()->user()->hasRole($this->superAdmin);

        $roles[] = $this->committee;
        $roles[] = $this->superAdmin;

        if (!$isAdmin) {
            // $roles[] = $this->superAdmin;
            $weight = auth()->user()->roles()->min('weight');
            return Role::where('weight','>' , $weight)->get();
        }


        return Role::whereNotIn('name', $roles)->get();
    }

    public function getUser($id)
    {
        $user = User::findOrFail($id);
        $user->load('office', 'officeTypeInfo', 'roles', 'assign_location', 'parent', 'committee.office', 'committeePermission');
        return  UserResource::make($user);
    }
    //user profile image update
    public function uploadImage(UploadImageRequest $request)
    {
        // return auth()->user()->id;
        // dd($request);
        $user = User::findOrFail(auth()->user()->id);

        $existingPhoto = $user->photo;

        if ($existingPhoto) {
            try{
                Storage::delete($existingPhoto);
            }catch(\Throwable $t){}
        }


        $image = $request->file('image');
        $destinationPath = env('FILE_STORE_ROOT').'/user/profile-images';
        $imageUrl = Helper::uploadImage($image, $destinationPath);
        $user->update(["photo" => $imageUrl]);

        // Save $imageUrl to the database or perform other actions

        // return response()->json(['imageUrl' => $imageUrl], 200);
        $user->save();
        return UserResource::make($user)->additional([
            'success' => true,
            'message' => $this->insertSuccessMessage,
        ]);
    }

    public function uploadImageSignature(UploadImageSignatureRequest $request)
    {
        // return auth()->user()->id;
        // dd($request);
        $user = User::findOrFail(auth()->user()->id);

        $existingPhotoSignature = $user->photo_signature;

        if ($existingPhotoSignature) {
            try{
                Storage::delete($existingPhotoSignature);
            }catch(\Throwable $t){}
        }

        $image = $request->file('image_signature');
        $destinationPath = env('FILE_STORE_ROOT').'/user/profile-images';
        $imageUrl = Helper::uploadImage($image, $destinationPath);
        $user->update(["photo_signature" => $imageUrl]);

        // Save $imageUrl to the database or perform other actions

        // return response()->json(['imageUrl' => $imageUrl], 200);
        $user->save();
        return UserResource::make($user)->additional([
            'success' => true,
            'message' => $this->insertSuccessMessage,
        ]);
    }
    //auth user password update
    public function passwordUpdate(Request $request)
    {
        // return $request->all();
        $user = User::findOrFail($request->userId);
        $code = $request->otp;
        $cachedCode = Cache::get($this->userLoginOtpPrefix . $user->id);
        if (!$cachedCode || $code != $cachedCode) {
            throw new AuthBasicErrorException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                'verify_failed',
                "Verification code invalid !",
            );
        }
        $user->salt = Helper::generateSalt();

        $user->password = bcrypt($user->salt . $request->password);
        \DB::beginTransaction();
        try{
            $user->save();
            $message = "Your password has been successfully changed. If you didn't perform this action, please contact support immediately.";
            $this->SMSservice->sendSms($user->mobile, $message);
            Mail::to($user->email)->send(new PasswordChanged());
            \DB::commit();
        }catch(\Throwable $t){
            \DB::rollBack();
            return UserResource::make($user)->additional([
                'success' => false,
                'message' => "Server Error",
            ]);
        }
        $cachedCode = Cache::forget($this->userLoginOtpPrefix . $user->id);
        return UserResource::make($user)->additional([
            'success' => true,
            'message' => $this->insertSuccessMessage,
        ]);
    }


    public function updatePassOtp(Request $request)
    {
        // return $request->all();
        $request->validate(
            [
                'mobile'      => 'required|exists:users,mobile',
                'username'      => 'required|exists:users,username',
            ],
            [
                'username.exists'     => 'This username does not match our record!',
                'phone.exists'     => 'This phone does not match our record!',
            ]
        );

        //    return $user = User::findOrFail($request->id);

        if($this->authService->isExistingPassword($request)){
            return response()->json(['success' => false, 'message' => 'container.profile.enter_new_password', 'data'=> null], 403);
        }

        $data = $this->authService->AdminForgotPassword($request);
        $this->SMSservice->sendSms($request->user()->mobile, $data);
        activity("Update password")
            ->withProperties(['userInfo' => Helper::BrowserIpInfo(), 'data' => $data])
            ->log('Update Password OTP Send!!');

        return response()->json(['success' => true, 'message' => 'Verification OTP Sent!', 'data' => $data]);
    }
}
