<?php

use App\Http\Controllers\Api\V1\Auth\AuthController;

Route::controller(AuthController::class)->group(function () {
        //login
        Route::post('admin/forgot-password', 'forgotPassword');
        Route::post('admin/forgot-password/submit', 'forgotPasswordSubmit');

        if (env('ENABLE_OTP') == 1) {
            Route::post('admin/login/otp', 'LoginOtpTest');
        } else {
            Route::post('admin/login/otp', 'LoginOtp');
        }

        Route::post('admin/browser-register/otp', 'BrowserRegisterOtp');

        if (env('ENABLE_OTP') == 1) {
            Route::post('admin/login', 'LoginAdminTest');
        } else {
            Route::post('admin/login', 'LoginAdmin');
        }

        Route::post('admin/reset/password', 'resetPassword');
        //check token
        Route::post('auth/token/check', 'checkToken');

});

Route::group(['middleware' => ['auth:sanctum']], function () {
    //logout
    Route::post('admin/send-otp', [AuthController::class, 'otp']);
    Route::get('admin/permissions', [AuthController::class, 'getPermissions']);
    Route::get('admin/tokens', [AuthController::class, 'adminTokens']);
    Route::get('admin/logout', [AuthController::class, 'LogoutAdmin']);
    Route::get('admin/users/blocked/list',[AuthController::class, 'getAllBlockedUsers'])->middleware(['role_or_permission:super-admin|main-block-list']);
    Route::post('admin/users/unblock',[AuthController::class, 'unBlockUser'])->middleware(['role_or_permission:super-admin|main-block-update']);

});
