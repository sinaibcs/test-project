<?php


use App\Http\Controllers\Api\V1\Admin\PMTScoreController;
use App\Http\Controllers\Api\V1\Admin\VariableController;

Route::middleware('auth:sanctum')->group(function () {
    /* -------------------------------------------------------------------------- */
    /*                                PMT SCORE Routes                          */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/poverty')->group(function () {

        //////////////////////////
        ///         CUTT OFF
        //////////////////////////
        Route::get('/get', [PMTScoreController::class, 'getAllPMTScorePaginated'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-view']);
        Route::post('/poverty-cut-off/insert', [PMTScoreController::class, 'insertCutOff'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-create']);
        Route::post('/poverty-cut-off/update', [PMTScoreController::class, 'updateCutOff'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-edit']);
        Route::post('/poverty-cut-off/filter', [PMTScoreController::class, 'getFiltered'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-view']);
        Route::get('filter/get', [PMTScoreController::class, 'getAllFilterCutoffPaginated'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-view']);
         Route::get('filter/edit', [PMTScoreController::class, 'getEditFiterCutoffPaginated'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-edit']);
        Route::post('/poverty-cut-off/destroy', [PMTScoreController::class, 'destroyCutoff'])->middleware(['role_or_permission:super-admin|poverty-cut-off-score-delete']);

        //////////////////////////
        ///    DISTRICT FIXED EFFECT
        //////////////////////////
        Route::get('/get/district-fixed-effect', [PMTScoreController::class, 'getAllDistrictFixedEffectPaginated'])->middleware(['role_or_permission:super-admin|district-fixed-effect-view']);
        Route::post('/district-fixed-effect/update', [PMTScoreController::class, 'updateDistrictFixedEffect'])->middleware(['role_or_permission:super-admin|district-fixed-effect-edit']);

        //////////////////////////
        ///         VARIABLE
        //////////////////////////
        Route::get('/get/variable', [VariableController::class, 'getAllVariablePaginated'])->middleware(['role_or_permission:super-admin|variable-view']);
        Route::post('/variable/insert', [VariableController::class, 'insertVariable'])->middleware(['role_or_permission:super-admin|variable-create']);
        Route::post('/variable/update', [VariableController::class, 'updateVariable'])->middleware(['role_or_permission:super-admin|variable-edit']);
        Route::post('/variable/destroy', [VariableController::class, 'destroyVariable'])->middleware(['role_or_permission:super-admin|variable-delete']);

        //////////////////////////
        ///         SUB VARIABLE
        //////////////////////////

        Route::get('/get/sub-variable', [VariableController::class, 'getAllSubVariablePaginated'])->middleware(['role_or_permission:super-admin|sub-variable-view']);
        Route::get('/get/sub-variable/variable-list', [VariableController::class, 'getAllVariableListForSubVariable'])->middleware(['role_or_permission:super-admin|sub-variable-view']);
        Route::post('/sub-variable/insert', [VariableController::class, 'insertSubVariable'])->middleware(['role_or_permission:super-admin|sub-variable-create']);
        Route::post('/sub-variable/update', [VariableController::class, 'updateSubVariable'])->middleware(['role_or_permission:super-admin|sub-variable-edit']);
        Route::post('/sub-variable/destroy', [VariableController::class, 'destroySubVariable'])->middleware(['role_or_permission:super-admin|sub-variable-delete']);


        // Route::post('/poverty-cut-off/insert', [PMTScoreController::class, 'insertDivisionCutOff'])->middleware(['role_or_permission:super-admin|demo-graphic-create']);
        // Route::post('/poverty-cut-off/update', [PMTScoreController::class, 'updatePMTScore'])->middleware(['role_or_permission:super-admin|demo-graphic-create']);
        // Route::post('/poverty-cut-off/filter', [PMTScoreController::class, 'getFiltered'])->middleware(['role_or_permission:super-admin|demo-graphic-create']);
        // Route::post('/update', [PMTScoreController::class, 'committeeUpdate'])->middleware(['role_or_permission:super-admin|demo-graphic-update']);
        // Route::get('/destroy/{id}', [PMTScoreController::class, 'destroyPovertyScoreCuttOff'])->middleware(['role_or_permission:super-admin|demo-graphic-destroy']);
        // Route::get('/{id}', [PMTScoreController::class, 'editPovertyScoreCuttOff'])->middleware(['role_or_permission:super-admin|demo-graphic-destroy']);


    });
});

