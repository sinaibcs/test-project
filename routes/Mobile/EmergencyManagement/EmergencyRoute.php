<?php

use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyAllotmentController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyBeneficiaryController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyPayrollApprovalController;

Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('mobile/emergency')->group(function () {
        Route::get('/allotments', [EmergencyAllotmentController::class, 'getEmergencyAllotments'])->middleware(['role_or_permission:super-admin|emergency-allotment-view']);
        Route::get('/beneficiaries', [EmergencyBeneficiaryController::class, 'list'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-view']);
        Route::get('/get-payroll-approval-list', [EmergencyPayrollApprovalController::class, 'getPayrollList'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
    });
});
