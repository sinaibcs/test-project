<?php

use App\Http\Controllers\Mobile\V1\Auth\AuthController;

Route::controller(AuthController::class)->group(function () {
        //login
        Route::post('mobile/forgot-password', 'forgotPassword');
        Route::post('mobile/forgot-password/submit', 'forgotPasswordSubmit');
        Route::post('mobile/login/otp', 'LoginOtp');
        Route::post('mobile/login', 'LoginAdmin');
        Route::post('mobile/reset/password', 'resetPassword');
        //check token
        Route::post('auth/token/check', 'checkToken');
});

Route::group(['middleware' => ['auth:sanctum']], function () {
    //logout
    Route::get('mobile/tokens', [AuthController::class, 'adminTokens']);
    Route::get('mobile/logout', [AuthController::class, 'LogoutAdmin']);
    Route::get('mobile/users/blocked/list',[AuthController::class, 'getAllBlockedUsers'])->middleware(['role_or_permission:super-admin|main-block-list']);
    Route::post('mobile/users/unblock',[AuthController::class, 'unBlockUser'])->middleware(['role_or_permission:super-admin|main-block-update']);

});
