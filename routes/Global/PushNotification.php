<?php

use App\Http\Controllers\Api\V1\PushNotification\PushNotificationDeviceController;

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/notifications-system/save-token',[PushNotificationDeviceController::class, 'SaveFcmToken']);

});
