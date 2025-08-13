<?php


use App\Http\Controllers\Api\V1\Admin\ApiDashboardController;
use App\Http\Controllers\Api\V1\Admin\ApiDataReceiveController;
use App\Http\Controllers\Api\V1\Admin\APIListController;
use App\Http\Controllers\Api\V1\Admin\APIURLController;
use App\Http\Controllers\Api\V1\NidServiceApiRequestLogController;

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::apiResource('api-url', APIURLController::class);
        Route::apiResource('api', APIListController::class);
        Route::apiResource('api-list', APIListController::class);

        Route::get('table-list', [APIListController::class, 'getTableList']);
        Route::get('get-modules', [APIListController::class, 'getModules']);
        Route::get('get-api-list', [APIListController::class, 'getApiList']);


        Route::apiResource('api-data-receive', ApiDataReceiveController::class);

        Route::prefix('api-manager')->group(function () {
            Route::get('dashboard/get-all-api-count', [ApiDashboardController::class, 'getAllApiCount']);
            Route::get('dashboard/organization-wise-count', [ApiDashboardController::class, 'organizationWiseCount']);
            Route::get('dashboard/date-wise-count', [ApiDashboardController::class, 'dateWiseCount']);
            Route::get('dashboard/nid-day-wise-status-count', [NidServiceApiRequestLogController::class, 'getDayWiseStatus']);
            Route::get('dashboard/nid-month-wise-status-count', [NidServiceApiRequestLogController::class, 'getMonthWiseStatus']);

            Route::get('send-email/{apiDataReceive}', [ApiDataReceiveController::class, 'sendEmail']);
            Route::get('organization-list', [ApiDataReceiveController::class, 'getOrganizationList']);

        });




    });


});
