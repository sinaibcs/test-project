<?php



use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Setting\ActivityLogController;


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('admin')->group(function () {

        Route::get('/activity-log/division-districtwise/office-lists/{id}',[ActivityLogController::class, 'divisionDistrictWiseOfficeList'])->middleware(['role_or_permission:super-admin|activityLog-view']);
        Route::get('/activity-log/all-log-name',[ActivityLogController::class, 'getAllLogName'])->middleware(['role_or_permission:super-admin|activityLog-view']);
        Route::any('/',[ActivityLogController::class, 'getAllActivityLogsPaginated'])->middleware(['role_or_permission:super-admin|activityLog-view']);
        Route::any('/activity-log/all/filtered',[ActivityLogController::class, 'getAllActivityLogsPaginated'])->middleware(['role_or_permission:super-admin|activityLog-view']);
        Route::get('/activity-log/view/{id}',[ActivityLogController::class, 'viewAnonymousActivityLog'])->middleware(['role_or_permission:super-admin|activityLog-view']);
        Route::delete('/activity-log/destroy/{id}',[ActivityLogController::class, 'destroyActivityLog'])->middleware(['role_or_permission:super-admin|activityLog-delete']);

    });
});
Route::get('/activity-log/get-information',[ActivityLogController::class, 'getAnonymousActivityLog']);
