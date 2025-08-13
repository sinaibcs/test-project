<?php

use App\Http\Controllers\Mobile\V1\BeneficiaryController;
use App\Http\Controllers\Mobile\V1\BeneficiaryDashboardController;
use App\Http\Controllers\Mobile\V1\CommitteeController;
use App\Http\Controllers\Mobile\V1\CommitteePermissionController;
use App\Http\Controllers\Mobile\V1\LocationController;


Route::middleware(['auth:sanctum', 'language'])->group(function () {

    Route::prefix('mobile/beneficiary')->group(function () {
        Route::get('/getUserLocation', [BeneficiaryController::class, 'getUserLocation']);
        Route::get('/list', [BeneficiaryController::class, 'list'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/listDropDown', [BeneficiaryController::class, 'listDropDown']);
        Route::get('/show/{id}', [BeneficiaryController::class, 'show'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/get/{id}', [BeneficiaryController::class, 'get'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/idCard/{id}', [BeneficiaryController::class, 'idCard'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getPaymentSummary/{beneficiary_id}', [BeneficiaryController::class, 'getPaymentSummary'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getPaymentHistory/{beneficiary_id}', [BeneficiaryController::class, 'getPaymentHistory'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getGrievanceSummary/{beneficiary_id}', [BeneficiaryController::class, 'getGrievanceSummary'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getGrievanceHistory/{beneficiary_id}', [BeneficiaryController::class, 'getGrievanceHistory'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getChangeTrackingSummary/{beneficiary_id}', [BeneficiaryController::class, 'getChangeTrackingSummary'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getChangeTrackingHistory/{beneficiary_id}', [BeneficiaryController::class, 'getChangeTrackingHistory'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getByBeneficiaryId/{beneficiary_id}', [BeneficiaryController::class, 'getByBeneficiaryId'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/edit/{id}', [BeneficiaryController::class, 'edit'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-edit']);
        Route::post('/update/{id}', [BeneficiaryController::class, 'update'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-edit']);
        Route::post('/updateNomineeInfo/{id}', [BeneficiaryController::class, 'updateNomineeInfo'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-edit']);
        Route::post('/updateAccountInfo/{id}', [BeneficiaryController::class, 'updateAccountInfo'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-edit']);
        Route::put('/delete/{id}', [BeneficiaryController::class, 'delete'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-delete']);
        Route::get('/deletedList', [BeneficiaryController::class, 'deletedList'])->middleware(['role_or_permission:super-admin|beneficiaryDeleteList-view']);
        Route::get('/restore/{id}', [BeneficiaryController::class, 'restore'])->middleware(['role_or_permission:super-admin|beneficiaryDeleteList-view']);
        Route::get('/restore-inactive/{id}', [BeneficiaryController::class, 'restoreInactive'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getListForReplace', [BeneficiaryController::class, 'getListForReplace'])->middleware(['role_or_permission:super-admin|beneficiaryReplacement-create']);
        Route::post('/replace/{id}', [BeneficiaryController::class, 'replaceSave'])->middleware(['role_or_permission:super-admin|beneficiaryReplacement-create']);
        Route::get('/replaceList', [BeneficiaryController::class, 'replaceList'])->middleware(['role_or_permission:super-admin|beneficiaryReplacement-view']);
        Route::get('/restore-replace/{id}', [BeneficiaryController::class, 'restoreReplace'])->middleware(['role_or_permission:super-admin|beneficiaryReplacement-view']);
        Route::post('/exit', [BeneficiaryController::class, 'exitSave'])->middleware(['role_or_permission:super-admin|beneficiaryExit-create']);
        Route::get('/exitList', [BeneficiaryController::class, 'exitList'])->middleware(['role_or_permission:super-admin|beneficiaryExit-view']);
        Route::get('/restore-exit/{id}', [BeneficiaryController::class, 'restoreExit'])->middleware(['role_or_permission:super-admin|beneficiaryExit-view']);
        Route::post('/shift', [BeneficiaryController::class, 'shiftingSave'])->middleware(['role_or_permission:super-admin|beneficiaryShifting-create']);
        Route::get('/shiftingList', [BeneficiaryController::class, 'shiftingList'])->middleware(['role_or_permission:super-admin|beneficiaryShifting-view']);
        Route::post('/locationShiftingSave', [BeneficiaryController::class, 'locationShiftingSave'])->middleware(['role_or_permission:super-admin|beneficiaryShifting-create']);
        Route::get('/locationShiftingList', [BeneficiaryController::class, 'locationShiftingList'])->middleware(['role_or_permission:super-admin|beneficiaryShifting-view']);
        // report
        Route::get('/getBeneficiaryListPdf', [BeneficiaryController::class, 'getBeneficiaryListPdf'])->middleware(['role_or_permission:super-admin|beneficiaryInfo-view']);
        Route::get('/getBeneficiaryExitListPdf', [BeneficiaryController::class, 'getBeneficiaryExitListPdf'])->middleware(['role_or_permission:super-admin|beneficiaryExit-view']);
        Route::get('/getBeneficiaryReplaceListPdf', [BeneficiaryController::class, 'getBeneficiaryReplaceListPdf'])->middleware(['role_or_permission:super-admin|beneficiaryReplacement-view']);
        Route::get('/getBeneficiaryShiftingListPdf', [BeneficiaryController::class, 'getBeneficiaryShiftingListPdf'])->middleware(['role_or_permission:super-admin|beneficiaryShifting-view']);
    });

});


