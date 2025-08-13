<?php


use App\Http\Controllers\Api\V1\Admin\BankController;
use App\Http\Controllers\Api\V1\Admin\BranchController;
use App\Http\Controllers\Api\V1\Admin\GlobalSettingController;
use App\Http\Controllers\Api\V1\Admin\MfsController;


Route::middleware('auth:sanctum')->group(function () {


    /* -------------------------------------------------------------------------- */
    /*                                Beneficiary Routes                          */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/globalsetting')->group(function () {

        Route::post('/insert', [GlobalSettingController::class, 'insertGlobalSetting'])->middleware(['role_or_permission:super-admin|demo-graphic-create']);
        Route::get('/get', [GlobalSettingController::class, 'getAllGlobalSettingPaginated'])->middleware(['role_or_permission:super-admin|demo-graphic-view']);
        Route::post('/update', [GlobalSettingController::class, 'globalSettingUpdate'])->middleware(['role_or_permission:super-admin|demo-graphic-update']);
        Route::get('/destroy/{id}', [GlobalSettingController::class, 'destroyGlobalSetting'])->middleware(['role_or_permission:super-admin|demo-graphic-destroy']);
        Route::get('/{id}', [GlobalSettingController::class, 'editGlobalSetting'])->middleware(['role_or_permission:super-admin|demo-graphic-destroy']);

    });
    //bank
    Route::apiResource('admin/banks', BankController::class);
    Route::apiResource('admin/mfs', MfsController::class);
    Route::get('/admin/get-all-banks', [BankController::class, 'getAllBanks']);
    Route::put('/admin/branches-status/{id}', [BranchController::class, 'updateStatus']);
    Route::apiResource('admin/branches', BranchController::class);
    Route::get('/admin/bank-branches/{id}', [BranchController::class, 'getBranchesByBankId']);
    Route::get('/admin/get-all-mfs', [BankController::class, 'getAllMfs']);
});
Route::get('banks', [BankController::class, 'get']);
Route::get('mfses', [MfsController::class, 'get']);
