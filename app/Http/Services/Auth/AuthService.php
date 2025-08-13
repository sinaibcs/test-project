<?php

namespace App\Http\Services\Auth;

use App\Exceptions\AuthBasicErrorException;
use App\Helpers\Helper;
use App\Http\Services\Notification\SMSservice;
use App\Http\Traits\MessageTrait;
use App\Http\Traits\RoleTrait;
use App\Http\Traits\UserTrait;
use App\Models\Device;
use App\Models\OtpLog;
use App\Models\User;
use Cache;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class AuthService
{
    use RoleTrait, AuthenticatesUsers, UserTrait, MessageTrait;

    protected $maxAttempts = 5;
    protected $decayMinutes = 2;
    protected $warning = 3;
    protected function sendNonAllowedAdminResponse()
    {
        throw new AuthBasicErrorException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->NonAllowedAdminTextErrorCode,
            $this->NonAllowedAdminErrorResponse,
        );
    }
    protected function sendBannedLoginResponse()
    {
        throw new AuthBasicErrorException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->bannedUserTextErrorCode,
            $this->bannedUserErrorResponse,
        );
    }

    protected function sendInactiveLoginResponse()
    {
        throw new AuthBasicErrorException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->inactiveUserTextErrorCode,
            $this->inactiveUserErrorResponse,
        );
    }

    protected function sendDefaultPasswordResponse()
    {
        throw new AuthBasicErrorException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->defaultPasswordTextErrorCode,
            $this->defaultPasswordErrorResponse,
        );
    }

    protected function sendUnVerifiedLoginResponse(Request $request)
    {
        throw new AuthBasicErrorException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->authUnverifiedUserTextErrorCode,
            $this->unverifiedUserErrorResponse,
        );

        // throw ValidationException::withMessages([
        //     'message' => $this->unverifiedUserErrorResponse,
        // ]);
    }

    public function generateTokenKey(Request $request)
    {
        $key = $this->accessTokenSpaDevice;
        if ($request->filled('device')) {
            $key = $request->device;
        }
        return $key;
    }

    public function validatePhone(Request $request)
    {

        $request->validate(
            [
                'phone'      => 'required|exists:users,mobile',
                'username'      => 'required|exists:users,username',
            ],
            [
                'username.exists'     => 'This username does not match our record!',
                'phone.exists'     => 'This phone does not match our record!',
            ]
        );
    }
    public function validatePasswordPhone(Request $request)
    {

        $request->validate(
            [
                'username'      => 'required|exists:users,username',
                'phone'      => 'required|exists:users,mobile',
                'password'      => 'required|min:6',
                'confirm_password'      => 'required|same:password',
            ],
            [
                'username.exists'     => 'This username does not match our record!',
                'phone.exists'     => 'This phone does not match our record!',
            ]
        );
    }
    public function validateLogin(Request $request)
    {

        $request->validate(
            [
                'username'      => 'required|exists:users,username',
                'password'              => 'required|string|min:6',
            ],
            [
                'username.exists'     => 'This username does not match our database record!',
            ]
        );
    }

    protected function sendFailedLoginResponse(Request $request)
    {
        throw new AuthBasicErrorException(
            Response::HTTP_UNPROCESSABLE_ENTITY,
            $this->authWrongCredentialTextErrorCode,
            trans('auth.failed')
        );
        // throw ValidationException::withMessages([
        //     'message' => [trans('auth.failed')],
        // ]);
    }


    protected function verifyBeforeLogin(Request $request, User $user)
    {
        $passwordCheck = Hash::check($user->salt . $request->password, $user->password);

        if (!$passwordCheck) {
            return $this->authBasicErrorCode;
        }


        if ($user->status == $this->userAccountDeactivate) return $this->authDeactivateUserErrorCode;
        if ($user->status == $this->userAccountBanned) return $this->authBannedUserErrorCode;
        if ($user->status == $this->userAccountInactive) return $this->authInactiveUserErrorCode;
        if (!$user->email_verified_at) return $this->authUnverifiedUserErrorCode;
        if ($user->user_type) {
            if ($user->user_type == $this->superAdminUserType || $user->user_type == $this->staffType) {
                if($user->user_type == $this->staffType){
                    // check this user password is_default_password or not if default password then request to change password first then login
                    if($user->is_default_password==1){
                        return $this->authDefaultPasswordErrorCode;
                    }

                }
                if ($passwordCheck) {
                    return $this->authSuccessCode;
                }
            } else {
                return $this->nonAllowedUserErrorCode;
            }
        }
        if ($passwordCheck) {
            return $this->authSuccessCode;
        }

        return $this->authBasicErrorCode;
    }

    protected function bannedUser($user){
        if ($user->status != 0) {
            $user->status= $this->userAccountBanned;
            $user->save();
            activity("Automation")
                ->causedBy(auth()->user())
                ->performedOn($user)
                ->log('User Blocked For Attempt Many time!!');
        }
    }

    public function AdminForgotPassword($user){
        $code = $this->generateOtpCode($user, 5);
        $message = 'Your OTP is '. $code . "\n-MIS, DSS";

        return $message;
    }

    public function isExistingPassword(Request $request){
        $user = User::whereUsername( $request->username)->first();
        return Hash::check($user->salt . $request->password, $user->password);
    }

    public function AdminForgotPasswordSubmit(Request $request){
        $user = User::whereUsername($request->username)->first();
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
        // password encryption with salt
        $user->password = bcrypt($user->salt . $request->password);
        $user->save();
        $cachedCode = Cache::forget($this->userLoginOtpPrefix . $user->id);

        return $user;

    }

    public function passwordReset(Request $request)
    {
        // validate password and confirm password
        $request->validate(
            [
                'username'      => 'required|exists:users,username',
                'old_password'              => 'required|string',
                'password'              => 'required|string|min:6',
                'confirm_password'      => 'required|same:password',
            ],
            [
                'old_password'     => 'Please try login again',
                'username.exists'     => 'Please try login again',
                'confirm_password.same' => 'Password and Confirm Password does not match!',
            ]
        );
        $user = User::withoutGlobalScope('assign_location_type')->where("username", $request->username)->first();

        // if (
        //     method_exists($this, 'hasTooManyLoginAttempts') &&
        //     $this->hasTooManyLoginAttempts($request)
        // ) {
        //     $this->fireLockoutEvent($request);
        //     $this->bannedUser($user);
        //     return $this->sendLockoutResponse($request);
        // }

        if ($user == null) {
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }

        if($user->is_default_password==1){
            if (Hash::check($user->salt . $request->old_password, $user->password)) {
                $user->salt = Helper::generateSalt();
                // password encryption with salt
                $user->password = bcrypt($user->salt . $request->password);
                $user->is_default_password=0;
                $user->save();
                return $user;

            }
            $this->incrementLoginAttempts($request);

            throw new AuthBasicErrorException(
                Response::HTTP_UNPROCESSABLE_ENTITY,
                $this->authWrongCredentialTextErrorCode,
                'Please try login again'
            );
        }

        return $this->sendNonAllowedAdminResponse();

    }

    public function Adminlogin(Request $request,$type=1)
    {

        // if (
        //     method_exists($this, 'hasTooManyLoginAttempts') &&
        //     $this->hasTooManyLoginAttempts($request)
        // ) {
        //     $this->fireLockoutEvent($request);
        //     $user = User::withoutGlobalScope('assign_location_type')->where("username", $request->username)->first();
        //     $this->bannedUser($user);
        //     return $this->sendLockoutResponse($request);
        // }
        $user = User::withoutGlobalScope('assign_location_type')->where("username", $request->username)->first();

        if ($user == null) {
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }

        if ($authCode = $this->verifyBeforeLogin($request, $user)) {

            if ($authCode == $this->nonAllowedUserErrorCode) return $this->sendNonAllowedAdminResponse();

            if ($authCode == $this->authBannedUserErrorCode) return $this->sendBannedLoginResponse();

            if ($authCode == $this->authInactiveUserErrorCode) return $this->sendInactiveLoginResponse();

            if ($authCode == $this->authUnverifiedUserErrorCode) return $this->sendUnVerifiedLoginResponse($request);
            if ($authCode == $this->authDefaultPasswordErrorCode) return $this->sendDefaultPasswordResponse();
            if ($authCode == $this->authBasicErrorCode) {

                $this->incrementLoginAttempts($request);
                return $this->sendFailedLoginResponse($request);
            }
            if ($authCode == $this->authSuccessCode) {
                $this->clearLoginAttempts($request);
                if($type==1){
                    // check device registration
                    // $device = Device::whereUserId($user->id)->whereStatus(1)->whereDeviceId($request->device_token)->first();
                    // \Log::info("". $user->user_id ."");
                    // \Log::info("". $request->device_token ."");
                    // if(!$device){
                    //     throw new AuthBasicErrorException(
                    //         Response::HTTP_UNPROCESSABLE_ENTITY,
                    //         'device_not_found',
                    //         "your device is not registered",
                    // );
                    // }
                    return $otp = $this->sendLoginOtp($user);
                }
                if($type==2){
                    // check OTP
                    $code = $request->otp;
                    $cachedCode = Cache::get($this->userLoginOtpPrefix . $user->id);
                    if (!$cachedCode || $code != $cachedCode) {
                        throw new AuthBasicErrorException(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            'verify_failed',
                            "Verification code invalid !",
                        );
                    }
                    // check device registration
                    // $device = Device::whereUserId($user->id)->whereStatus(1)->whereDeviceId($request->device_token)->first();
                    // if(!$device){
                    //     throw new AuthBasicErrorException(
                    //         Response::HTTP_UNPROCESSABLE_ENTITY,
                    //         'device_not_found',
                    //         "your device is not registered",
                    // );
                    // }

                    //logging in
                    $token = $user->createToken($this->generateTokenKey($request) . $user->id)->plainTextToken;
                    return [
                        'user'      => $user->load('roles','assign_location','office'),
                        'token'     => $token,
                    ];
                }

            }
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    public function BrowserRegisterOtp(Request $request,$type=1)
    {

        // if (
        //     method_exists($this, 'hasTooManyLoginAttempts') &&
        //     $this->hasTooManyLoginAttempts($request)
        // ) {
        //     $this->fireLockoutEvent($request);
        //     $user = User::withoutGlobalScope('assign_location_type')->where("username", $request->username)->first();
        //     $this->bannedUser($user);
        //     return $this->sendLockoutResponse($request);
        // }
        $user = User::withoutGlobalScope('assign_location_type')->where("username", $request->username)->first();

        if ($user == null) {
            $this->incrementLoginAttempts($request);
            return $this->sendFailedLoginResponse($request);
        }

        if ($authCode = $this->verifyBeforeLogin($request, $user)) {

            if ($authCode == $this->nonAllowedUserErrorCode) return $this->sendNonAllowedAdminResponse();

            if ($authCode == $this->authBannedUserErrorCode) return $this->sendBannedLoginResponse();

            if ($authCode == $this->authInactiveUserErrorCode) return $this->sendInactiveLoginResponse();

            if ($authCode == $this->authUnverifiedUserErrorCode) return $this->sendUnVerifiedLoginResponse($request);
            if ($authCode == $this->authDefaultPasswordErrorCode) return $this->sendDefaultPasswordResponse();
            if ($authCode == $this->authBasicErrorCode) {

                $this->incrementLoginAttempts($request);
                return $this->sendFailedLoginResponse($request);
            }
            if ($authCode == $this->authSuccessCode) {
                $this->clearLoginAttempts($request);
                if($type==1){
                    return $otp = $this->sendOtp($user,'DEVICE_REGISTRATION');
                }
                if($type==2){
                    // check OTP
                    $code = $request->otp;
                    $cachedCode = Cache::get($this->userLoginOtpPrefix . $user->id);
                    if (!$cachedCode || $code != $cachedCode) {
                        throw new AuthBasicErrorException(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            'verify_failed',
                            "Verification code invalid !",
                        );
                    }
                    // check device registration
                    $device = Device::whereUserId($user->id)->whereStatus(1)->whereDeviceId($request->device_token)->first();
                    if(!$device){
                        throw new AuthBasicErrorException(
                            Response::HTTP_UNPROCESSABLE_ENTITY,
                            'device_not_found',
                            "your device is not registered",
                    );
                    }

                    //logging in
                    $token = $user->createToken($this->generateTokenKey($request) . $user->id)->plainTextToken;
                    return [
                        'user'      => $user->load('roles','assign_location','office'),
                        'token'     => $token,
                    ];
                }

            }
        }

        $this->incrementLoginAttempts($request);
        return $this->sendFailedLoginResponse($request);
    }

    public function AdminloginTest(Request $request,$type=1)
    {

        $user = User::withoutGlobalScope('assign_location_type')->where("username", $request->username)->first();

        $token = $user->createToken($this->generateTokenKey($request) . $user->id)->plainTextToken;
        return [
            'user'      => $user->load('roles','assign_location','office'),
            'token'     => $token,
        ];
    }

    public function generateOtpCode($user, $time)
    {
        Cache::forget($this->userLoginOtpPrefix . $user->id);
        //generate code
        $code =  mt_rand(100000, 999999);
        //put them in cache
        Cache::put($this->userLoginOtpPrefix . $user->id, $code, now()->addMinutes($time));
        //return generated code
        return $code;
    }
    protected function sendLoginOtp($user){
        return $this->sendOtp($user, "LOGIN");
    }
    
    protected function sendOtp($user, $type){

        $code = $this->generateOtpCode($user, 3);

        $message = "Your OTP is ". $code . "\n-MIS, DSS";
        (new SMSservice())->sendOtpSms($user->mobile, $message);
        OtpLog::create(['user_id' => $user->id, 'phone_number' => $user->mobile, 'type' => $type, 'body' => $message]);
        Log::info("otp message: ". $user->mobile ."". $message);

        return $code;
    }

    public function sendGeneralOtp($user){
        return $this->sendOtp($user, "PAYROLL_CREATE");
    }

    public function logout(Request $request)
    {

        DB::beginTransaction();
        try {
            $user = User::withoutGlobalScope('assign_location_type')->findOrFail(Auth::user()->id);

            if ($request->filled('device') && !empty($request->device)) {
                $user->tokens()->where('name', $this->generateTokenKey($request) . $user->id)->delete();
            } else {
                $user->tokens()->where('name', $this->generateTokenKey($request) . $user->id)->delete();

                // Auth::user()->tokens->each(function ($token, $key) {
                //     $token->delete();
                // });
            }
            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            throw $th;
        }
    }
}
