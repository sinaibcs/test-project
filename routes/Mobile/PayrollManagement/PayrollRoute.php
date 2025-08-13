<?php

use App\Http\Controllers\Api\V1\Admin\PaymentProcessorController;
use App\Http\Controllers\Api\V1\Admin\PayrollSettingController;
use App\Http\Controllers\Mobile\V1\PaymentTrackingController;
use App\Http\Controllers\Mobile\V1\PayrollController;

Route::middleware('auth:sanctum')->group(function () {
    /* -------------------------------------------------------------------------- */
    /*                       Payroll setting Routes                         */
    /* -------------------------------------------------------------------------- */
    Route::prefix('mobile/payroll')->group(function () {
        //for payroll setting
        Route::get('/get-all-allowance', [PayrollSettingController::class, 'getAllAllowance'])->middleware(['role_or_permission:super-admin|payroll-setting-view']);
        Route::get('/get-financial-year', [PayrollSettingController::class, 'getFinancialYear']);
        Route::get('/get-all-financial-year', [PayrollSettingController::class, 'getAllFinancialYear']);
        Route::get('/get-program-wise-installment/{id}', [PayrollSettingController::class, 'getProgramWiseInstallment']);
        Route::get('/get-all-installments', [PayrollSettingController::class, 'getAllInstallments']);
        Route::post('/setting-submit', [PayrollSettingController::class, 'payrollSettingSubmit']);
        Route::get('/get-setting-data', [PayrollSettingController::class, 'getSettingData']);
        //for payroll verification
        Route::post('/verification-setting-submit', [PayrollSettingController::class, 'payrollVerification']);
        Route::get('/get-verification-setting', [PayrollSettingController::class, 'getVerificationSetting']);
        // for payroll approve
        Route::get('/get-payroll-approval-list', [PayrollController::class, 'getApprovalList'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/view-beneficiaries/{payroll_id}', [PayrollController::class, 'getActiveBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::put('/reject-beneficiary/{beneficiary_id}', [PayrollController::class, 'submitPayroll'])->middleware(['role_or_permission:super-admin|payroll-create']);
        Route::put('/reject-payroll/{payroll_id}', [PayrollController::class, 'submitPayroll'])->middleware(['role_or_permission:super-admin|payroll-create']);
        Route::put('/approve-payroll/{payroll_id}', [PayrollController::class, 'submitPayroll'])->middleware(['role_or_permission:super-admin|payroll-create']);
    });

});
// beneficiary tracking information
Route::post('mobile/payroll/payment-tracking-info', [PaymentTrackingController::class, 'getPaymentTrackingInfoMobile']);
