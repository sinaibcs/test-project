<?php

use App\Http\Controllers\Mobile\V1\ApplicationController;

Route::middleware('auth:sanctum')->group(function () {
    /* -------------------------------------------------------------------------- */
    /*                       APPLICATION SELECTION Routes                         */
    /* -------------------------------------------------------------------------- */
    Route::prefix('mobile/application')->group(function () {

    Route::get('/get', [ApplicationController::class, 'getAllApplicationPaginated'])->middleware(['role_or_permission:super-admin|application-entry-view']);

    Route::get('get/{id}', [ApplicationController::class, 'getApplicationById'])->middleware(['role_or_permission:super-admin|application-entry-view']);

    Route::get('/permissions', [ApplicationController::class, 'getApplicationPermission']);

    Route::get('/committee-list', [ApplicationController::class, 'getCommitteeList']);

    Route::post('/update-status', [ApplicationController::class, 'changeApplicationsStatus'])->middleware(['role_or_permission:super-admin|application-entry-edit']);

    Route::get('/generate-pdf', [ApplicationController::class, 'getPdf']);

    });


});
