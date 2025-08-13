<?php

use App\Http\Controllers\Api\V1\Admin\ApplicationController;
use App\Http\Controllers\Api\V1\Admin\ApplicationSelectionDashboardController;

Route::middleware('auth:sanctum')->group(function () {
    /* -------------------------------------------------------------------------- */
    /*                       APPLICATION SELECTION Routes                         */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/application')->group(function () {

    Route::get('/get', [ApplicationController::class, 'getAllApplicationPaginated'])->middleware(['role_or_permission:super-admin|application-entry-view']);

    Route::get('get/{id}', [ApplicationController::class, 'getApplicationById'])->middleware(['role_or_permission:super-admin|application-entry-view']);

    Route::get('/permissions', [ApplicationController::class, 'getApplicationPermission']);

    Route::get('/committee-list', [ApplicationController::class, 'getCommitteeList']);

    Route::post('/update-status', [ApplicationController::class, 'changeApplicationsStatus'])->middleware(['role_or_permission:super-admin|application-entry-edit']);
    
    Route::post('/update-location', [ApplicationController::class, 'updateLocation'])->middleware(['role_or_permission:super-admin|application-entry-edit']);

    Route::get('/generate-pdf', [ApplicationController::class, 'getPdf']);

    });
        /* -------------------------------------------------------------------------- */
    /*                      Mobile Operator Route                         */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/mobile-operator')->group(function () {

    Route::get('/get', [ApplicationController::class, 'getAllMobileOperatorPaginated'])->middleware(['role_or_permission:super-admin']);
    Route::post('/insert', [ApplicationController::class, 'insertMobileOperator'])->middleware(['role_or_permission:super-admin']);
    Route::get('/destroy/{id}', [ApplicationController::class, 'destroyMobileOperator'])->middleware(['role_or_permission:super-admin']);
     Route::post('/update', [ApplicationController::class, 'updateMobileOperator'])->middleware(['role_or_permission:super-admin']);
    // Route::get('/{id}', [ApplicationController::class, 'editMobileOperato'])->middleware(['role_or_permission:super-admin|demo-graphic-destroy']);

    });

    Route::prefix('admin/application-dashboard')->group(function () {
        Route::get('/get-total-received-application', [ApplicationSelectionDashboardController::class, 'programStatusWisetotalNumberOfdApplication'])/*->middleware(['role_or_permission:super-admin|applicationDashboard-view'])*/;
        Route::get('/get-total-numberof-application', [ApplicationSelectionDashboardController::class, 'programWisetotalNumberOfdApplication'])/*->middleware(['role_or_permission:super-admin|applicationDashboard-view'])*/;
    });


});
