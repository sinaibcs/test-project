<?php

use App\Http\Controllers\Api\V1\Admin\AdminController;


Route::middleware('auth:sanctum')->group(function () {

    /* -------------------------------------------------------------------------- */
    /*                               Lookup Management Routes                              */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/lookup')->group(function () {
        Route::post('/insert', [AdminController::class, 'insertlookup'])/*->middleware(['role_or_permission:super-admin|demo-graphic-create'])*/;
        Route::get('/get',[AdminController::class, 'getAllLookupPaginated'])/*->middleware(['role_or_permission:super-admin|demo-graphic-view'])*/;
        Route::get('/get/{type}',[AdminController::class, 'getLookupByType'])/*->middleware(['role_or_permission:super-admin|demo-graphic-view'])*/;
        Route::post('/update', [AdminController::class, 'LookupUpdate'])/*->middleware(['role_or_permission:super-admin|demo-graphic-update'])*/;
        Route::get('/destroy/{id}', [AdminController::class, 'destroyLookup'])/*->middleware(['role_or_permission:super-admin|demo-graphic-destroy'])*/;

    });




});
