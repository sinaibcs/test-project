<?php

namespace App\Http\Controllers\Mobile\V1\Auth;

use App\Events\RealTimeMessage;
use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Http\Resources\AdminAuthResource;
use App\Http\Services\Mobile\Auth\AuthService;
use App\Http\Services\Notification\SMSservice;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\UserTrait;
use App\Models\Device;
use App\Models\User;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Validator;
use Laravel\Sanctum\PersonalAccessToken;

class AuthController extends Controller
{
    use UserTrait, MessageTrait;
    private $authService;
    private $SMSservice;
    public function __construct(
        AuthService $authService,
        SMSservice $SMSservice
    ) {
        $this->authService = $authService;
        $this->SMSservice = $SMSservice;
        // $this->middleware('permission:user-list|user-create|user-edit|user-delete', ['only' => ['index', 'store']]);
        // $this->middleware('permission:user-create', ['only' => ['create', 'store']]);
        // $this->middleware('permission:user-edit', ['only' => ['edit', 'update']]);
        // $this->middleware('permission:user-delete', ['only' => ['destroy']]);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/forgot-password",
     *      operationId="forgotPassword",
     *      tags={"Auth"},
     *      summary="forgot-password to the Application",
     *      description="forgot-password to the application",
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *
     *                   @OA\Property(
     *                      property="phone",
     *                      description="user phone number",
     *                      type="string",
     *                   ),
     *               ),
     *               ),
     *
     *         ),
     *
     *
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */

     public function forgotPassword(Request $request)
     {
         //validate login
         $this->authService->validatePhone($request);
         //forgot password
         $data = $this->authService->AdminForgotPassword($request);
         $this->SMSservice->sendOtpSms($request->phone, $data);
         activity("Forgot")
         ->withProperties(['userInfo' => Helper::BrowserIpInfo(),'data' => $data])
         ->log('Forgot Password OTP Send!!');


         return response()->json(['success' => true, 'message' => 'Verification OTP Sent!', 'data' => $data]);

     }
    /**
     *
     * @OA\Post(
     *      path="/admin/forgot-password/submit",
     *      operationId="forgotPasswordSubmit",
     *      tags={"Auth"},
     *      summary="forgot-password submit to the Application",
     *      description="forgot-password submit to the application",
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *
     *                   @OA\Property(
     *                      property="phone",
     *                      description="user phone number",
     *                      type="string",
     *                   ),
     *                   @OA\Property(
     *                      property="otp",
     *                      description="otp number",
     *                      type="string",
     *                   ),
     *                   @OA\Property(
     *                      property="password",
     *                      description="new password",
     *                      type="string",
     *                   ),
     *                   @OA\Property(
     *                      property="confirm_password",
     *                      description="confirm password",
     *                      type="string",
     *                   ),
     *               ),
     *               ),
     *
     *         ),
     *
     *
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */

     public function forgotPasswordSubmit(Request $request)
     {

         //validate request
         $this->authService->validatePasswordPhone($request);
         //forgot password submit
         $data = $this->authService->AdminForgotPasswordSubmit($request);

         activity("Forgot")
         ->withProperties(['userInfo' => Helper::BrowserIpInfo(),'data' => $data])
         ->log('Forgot Password Submit Successfully!!');

         return response()->json(['success' => true, 'message' => 'Forgot Password Successfully!', 'data' => $data]);

     }
    /**
     *
     * @OA\Post(
     *      path="/admin/reset/password", *      operationId="resetPassword",
     *      tags={"Auth"},
     *      summary="reset default password the Application",
     *      description="reset default password to the application user",
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                    @OA\Property(
     *                      property="device_token",
     *                      description="Browser Fingerprint",
     *                      type="string",
     *                   ),
     *                   @OA\Property(
     *                      property="username",
     *                      description="username",
     *                      type="string",
     *                   ),
     *                  @OA\Property(
     *                      property="old_password",
     *                      description="old password",
     *                      type="text",
     *                   ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password",
     *                      type="text",
     *                   ),
     *                  @OA\Property(
     *                      property="confirm_password",
     *                      description="confirm password",
     *                      type="text",
     *                   ),
     *
     *               ),
     *               ),
     *
     *         ),
     *
     *
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */

     public function resetPassword(Request $request)
     {

         //validate login
         $this->authService->validateLogin($request);

         $data = $this->authService->passwordReset($request);


         activity("Reset")
         ->withProperties(['userInfo' => Helper::BrowserIpInfo(),'data' => $data])
         ->log('Reset Password Successfully!!');

         return response()->json(['success' => true, 'message' => 'Reset Password Successfully!', 'data' => $data]);
     }
    /**
     *
     * @OA\Post(
     *      path="/admin/login/otp",
     *      operationId="LoginOtp",
     *      tags={"Auth"},
     *      summary="Login to the Application",
     *      description="login to the application",
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *                    @OA\Property(
     *                      property="device_token",
     *                      description="Browser Fingerprint",
     *                      type="string",
     *                   ),
     *                   @OA\Property(
     *                      property="username",
     *                      description="username",
     *                      type="string",
     *                   ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password",
     *                      type="text",
     *
     *                   ),
     *
     *               ),
     *               ),
     *
     *         ),
     *
     *
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */


    //Test
     public function LoginOtpTest(Request $request)
     {

        // $device = Device::where("device_name", 'test')->first(); // remove during Live - this is only for testing purpose
        $mode = $request->username;

         $data = $this->authService->AdminloginTest($request,1); // remove during

         activity("Login")
             ->withProperties(['userInfo' => Helper::BrowserIpInfo(),'data' => $data])
             ->log("Login OTP Send!!");

         return response()->json(
            ['success' => true,
            'message' => 'Verification OTP Sent!',
            'data' => $data,
            'device' => $mode
        ]);

     }



     //Live
    public function LoginOtp(Request $request)
    {
        // $device = Device::where("device_name", 'test')->first(); // remove during Live - this is only for testing purpose
        $mode = $request->username;

        //  validate login
        $this->authService->validateLogin($request); //Keep in Live
        //  login
        $data = $this->authService->Adminlogin($request,1); //Keep in Live

        activity("Login")
            ->withProperties(['userInfo' => Helper::BrowserIpInfo(),'data' => $data])
            ->log("Login OTP Send!!");

        return response()->json(
            ['success' => true,
                'message' => 'Verification OTP Sent!',
                'data' => $data,
                'device' => $mode
            ]);

    }
    /**
     *
     * @OA\Post(
     *      path="/admin/login",
     *      operationId="LoginAdmin",
     *      tags={"Auth"},
     *      summary="Login to the Application",
     *      description="login to the application",
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="Pass user credentials",
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *
     *                   @OA\Property(
     *                      property="device_token",
     *                      description="Browser Fingerprint",
     *                      type="string",
     *                   ),
     *                  @OA\Property(
     *                      property="otp",
     *                      description="OTP code",
     *                      type="string",
     *                   ),
     *                   @OA\Property(
     *                      property="username",
     *                      description="username",
     *                      type="string",
     *                   ),
     *                  @OA\Property(
     *                      property="password",
     *                      description="password",
     *                      type="text",
     *                   ),
     *
     *               ),
     *               ),
     *
     *         ),
     *
     *
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */

    //test
     public function LoginAdmin(Request $request){
//        broadcast(new RealTimeMessage('Hello World! I am an event 😄'));



        $mode = $request->username;
          $this->authService->validateLogin($request); //Keep in Live
       // $authData = $this->authService->AdminloginTest($request,2); //remove during live - this is only for testing purpose
           $authData = $this->authService->Adminlogin($request,2); //Keep in Live
    //    dd( $authData['user']);
         $permissions = $authData['user']->getAllPermissions();

         $userInfo = [
             'User Name' => $authData['user']->username ?? '',
             'Full Name' => $authData['user']->full_name ?? '',
             'Email' => $authData['user']->email ?? '',
             'Mobile' => $authData['user']->mobile ?? '',
             'Photo' => $authData['user']->photo ?? '',
             'photo_url' => $authData['user']->photo_url ?? '',
             'Roles' => $authData['user']->roles[0]->name ?? '',
         ];
        //   dd( $userInfo);
         $changesWithPreviousValues = [
             'previous' => null,
             'new' => $userInfo,
         ];

         activity("Login Information")
             ->causedBy(auth()->user())
             ->performedOn($authData['user'])
             ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo(),'data' => $authData])
             ->log('Logged In!!');

         return AdminAuthResource::make($authData['user'])
             ->token($authData['token'])
             ->permissions($permissions)
             ->success(true)
             ->message("Login Success");
     }




     //Live
    public function LoginAdminLive(Request $request){
        broadcast(new RealTimeMessage('Hello World! I am an event 😄'));

        // validate login
        // login
        $this->authService->validateLogin($request); //Keep in Live
        $authData = $this->authService->Adminlogin($request,2); //Keep in Live

        $permissions = $authData['user']->getAllPermissions();

        $userInfo = [
            'User Name' => $authData['user']->username ?? '',
            'Full Name' => $authData['user']->full_name ?? '',
            'Email' => $authData['user']->email ?? '',
            'Mobile' => $authData['user']->mobile ?? '',
            'Photo' => $authData['user']->photo ?? '',
            'Roles' => $authData['user']->roles[0]->name ?? '',
        ];

        $changesWithPreviousValues = [
            'previous' => null,
            'new' => $userInfo,
        ];

        activity("Login Information")
            ->causedBy(auth()->user())
            ->performedOn($authData['user'])
            ->withProperties(['changes' => $changesWithPreviousValues, 'userInfo' => Helper::BrowserIpInfo(),'data' => $authData])
            ->log('Logged In!!');

        return AdminAuthResource::make($authData['user'])
            ->token($authData['token'])
            ->permissions($permissions)
            ->success(true)
            ->message("Login Success");
    }

      /**
     * /**
     * @OA\Get(
     *      path="/admin/logout",
     *      summary="Logout From The Application",
     *      description="Logout user and invalidate token",
     *      operationId="LogoutAdmin",
     *      tags={"Auth"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *
     *  )
     * )

     */
    public function LogoutAdmin(Request $request)
    {
        $this->authService->logout($request);
        return new JsonResponse([], 204);
    }


      /**
     * /**
     * @OA\Get(
     *      path="/admin/tokens",
     *      summary="all token The Application",
     *      description="all token",
     *      operationId="adminTokens",
     *      tags={"Auth"},
     *      security={{"bearer_token":{}}},
     *      @OA\Response(
     *          response=204,
     *          description="Successful operation",
     *          @OA\JsonContent()
     *       ),
     *      @OA\Response(
     *          response=401,
     *          description="Returns when user is not authenticated",
     *
     *  )
     * )

     */
    public function adminTokens(){
        // return PersonalAccessToken::all();
        return $tokens = Auth()->user()->tokens;
    }

    /**
    * @OA\Get(
    *     path="/admin/users/blocked/list",
    *      operationId="getAllBlockedUsers",
    *      tags={"Auth"},
    *      summary="get paginated block users",
    *      description="get paginated block users",
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
    *         description="number of users per page",
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

    public function getAllBlockedUsers(Request $request){
        $searchText = $request->query('searchText');
        $perPage = $request->query('perPage');
        $page = $request->query('page');

        $filterArrayName=[];
        $filterArrayEmail=[];

        if ($searchText) {
            $filterArrayName[] = ['full_name', 'LIKE', '%' . $searchText . '%'];
            $filterArrayEmail[] = ['email', 'LIKE', '%' . $searchText . '%'];
        }
        $users = User::query()
            ->where(function ($query) use ($filterArrayName, $filterArrayEmail) {
                $query->where($filterArrayName)
                    ->orWhere($filterArrayEmail);
        })
        ->whereStatus($this->userAccountBanned)
        ->latest()
        ->paginate($perPage, ['*'], 'page');
        return AdminAuthResource::collection($users)->additional([
            'success' => true,
            'message' => $this->fetchSuccessMessage,
        ]);
    }

    /**
     *
     * @OA\Post(
     *      path="/admin/users/unblock",
     *      operationId="unBlockUser",
     *      tags={"Auth"},
     *      summary="update a blocked user",
     *      description="update a blocked user",
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
     *                      description="id of the user",
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
    public function unBlockUser(Request $request){
        $validator = Validator::make(['id' => $request->user_id], [
            'id' => 'required|exists:users,id,deleted_at,NULL',
        ]);
        $validator->validated();

        DB::beginTransaction();
        try {
                $user = User::findOrFail($request->user_id);
                $user->status = $this->userAccountApproved;
                $user->save();
                DB::commit();

            $changesWithPreviousValues = [
                'previous' => null,
                'new' => $user,
            ];

                activity("User")
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->withProperties(['changes' => $changesWithPreviousValues,'userInfo' => Helper::BrowserIpInfo(),'data' => $user])
                ->log('User Unblocked');
         return $this->sendResponse($user, $this->updateSuccessMessage, Response::HTTP_OK);


        } catch (\Throwable $th) {
            //throw $th;
        }
    }

    /**
     *
     * @OA\Post(
     *      path="/auth/token/check",
     *      operationId="checkToken",
     *      tags={"Auth"},
     *      summary="Check the token is valid or not",
     *      description="Check the token is valid or not",
     *
     *
     *       @OA\RequestBody(
     *          required=true,
     *          description="pass the client side token.example='10|qb8x9c7QEuYGgdaQMm7xBDg9JwwOwGtv3D7y7bq3'",
     *
     *           @OA\MediaType(
     *              mediaType="multipart/form-data",
     *           @OA\Schema(
     *
     *                   @OA\Property(
     *                      property="token",
     *                      description="the client side sanctum token",
     *                      type="text",
     *
     *                   ),
     *               ),
     *               ),
     *
     *         ),
     *
     *
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
     *          response=422,
     *          description="Unprocessable Entity",
     *
     *          )
     *        )
     *     )
     *
     */
    public function checkToken(Request $request)
    {
        $token = $request->token;
        $personalAccessToken =  PersonalAccessToken::findToken($token);

        if ($personalAccessToken) {
            return $this->sendResponse([], 'Token is Valid', 200);
        } else {
            return $this->sendError('Token is Invalid', [], 401);
        }
    }
}
