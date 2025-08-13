<?php

use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyAllotmentController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyBeneficiaryController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyPaymentCycleController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyPayrollApprovalController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyPayrollController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyPayrollSettingsController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyReconciliatioController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencySupplementaryController;


Route::middleware(['auth:sanctum', 'language'])->group(function () {
    Route::prefix('admin/emergency')->group(function () {
        //for payroll setting
        Route::get('/get-all-allowance', [EmergencyPayrollSettingsController::class, 'getAllAllowance'])->middleware(['role_or_permission:super-admin|payroll-setting-view']);
        Route::get('/get-financial-year', [EmergencyPayrollSettingsController::class, 'getFinancialYear'])->middleware(['role_or_permission:super-admin|payroll-setting-view']);
        Route::get('/get-all-installments', [EmergencyPayrollSettingsController::class, 'getAllInstallments'])->middleware(['role_or_permission:super-admin|payroll-setting-view']);
        Route::post('/setting-submit', [EmergencyPayrollSettingsController::class, 'payrollSettingSubmit']);
        Route::get('/get-setting-data', [EmergencyPayrollSettingsController::class, 'getSettingData']);
        /*----------------------------Emergency Allotment Start--------------------------------*/
        Route::get('/allotments', [EmergencyAllotmentController::class, 'getEmergencyAllotments'])->middleware(['role_or_permission:super-admin|emergency-allotment-view']);
        Route::post('/allotments', [EmergencyAllotmentController::class, 'store'])->middleware(['role_or_permission:super-admin|emergency-allotment-create']);
        Route::delete('/allotments/{id}', [EmergencyAllotmentController::class, 'destroy'])->middleware(['role_or_permission:super-admin|emergency-allotment-delete']);
        Route::get('/allotments/edit/{id}', [EmergencyAllotmentController::class, 'edit'])->middleware(['role_or_permission:super-admin|emergency-allotment-edit']);
        Route::put('/allotments/update/{id}', [EmergencyAllotmentController::class, 'update'])->middleware(['role_or_permission:super-admin|emergency-allotment-edit']);
        Route::get('/get-allotment-wise-program/{id}', [EmergencyAllotmentController::class, 'getAllotmentWiseProgram'])->middleware(['role_or_permission:super-admin|emergency-allotment-view']);
        Route::get('/get-all-allotment-programs', [EmergencyAllotmentController::class, 'getAllAllotmentPrograms'])->middleware(['role_or_permission:super-admin|emergency-allotment-view']);
        Route::get('/get-financial-year-id', [EmergencyAllotmentController::class, 'getFinancialId'])->middleware(['role_or_permission:super-admin|emergency-allotment-view']);
        Route::get('/get-user-wise-programs', [EmergencyAllotmentController::class, 'getUserWisePrograms'])->middleware(['role_or_permission:super-admin|emergency-allotment-view']);
        /* -----------------------------------Emergency Allotment End--------------------------------------- */

        /*----------------------------Emergency Beneficiary Start--------------------------------*/
        Route::get('/beneficiaries', [EmergencyBeneficiaryController::class, 'list'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-list']);
        Route::get('/get-selected-beneficiaries', [EmergencyBeneficiaryController::class, 'getSelectedBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-view']);
        Route::get('/beneficiaries-info/{id}', [EmergencyBeneficiaryController::class, 'beneficiariesInfo'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-view']);
        Route::post('/beneficiaries', [EmergencyBeneficiaryController::class, 'store'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-create']);
        Route::post('/store-multiple-beneficiaries', [EmergencyBeneficiaryController::class, 'storeMultipleData'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-create']);
        Route::get('/beneficiary/edit/{id}', [EmergencyBeneficiaryController::class, 'edit'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-edit']);
        Route::get('/beneficiary/details/{id}', [EmergencyBeneficiaryController::class, 'details'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-view']);
        Route::post('/beneficiary/update/{id}', [EmergencyBeneficiaryController::class, 'update'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-edit']);
        Route::post('/get-coverage-area-wise-bank-and-mfs', [EmergencyBeneficiaryController::class, 'getCoverageAreaWiseBankAndMfs'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-edit']);
        Route::get('/get-existing-beneficiaries-info', [EmergencyBeneficiaryController::class, 'getExistingBeneficiariesInfo'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-create']);
        Route::get('/get-new-beneficiaries-info', [EmergencyBeneficiaryController::class, 'getNewBeneficiariesInfo'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-create']);
        Route::delete('/beneficiary/{id}', [EmergencyBeneficiaryController::class, 'destroy'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-delete']);
        Route::get('/getBeneficiaryListPdf', [EmergencyBeneficiaryController::class, 'getBeneficiaryListPdf'])->middleware(['role_or_permission:super-admin|emergency-beneficiary-view']);
        /* -----------------------------------Emergency Beneficiary End--------------------------------------- */

        /*----------------------------Payment Cycle start--------------------------------*/
        Route::get('/payment-cycle', [EmergencyPaymentCycleController::class, 'getPaymentCycle'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::get('/program-wise-installment/{id}', [EmergencyPaymentCycleController::class, 'programWiseInstallment'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::get('/payment-cycle/view/{id}', [EmergencyPaymentCycleController::class, 'getPaymentCycleViewById'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-view']);
        Route::get('/payment-cycle/reject/{id}', [EmergencyPaymentCycleController::class, 'getPaymentCycleRejectById'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::get('/cycle/payrolls/{id}', [EmergencyPaymentCycleController::class, 'getPaymentCyclePayrolls'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::delete('/payroll/{id}/{cycle_id}', [EmergencyPaymentCycleController::class, 'emergencyPayrollDelete'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::delete('/payment-cycle-delete/{id}', [EmergencyPaymentCycleController::class, 'paymentCycleDelete'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::delete('/beneficiary/reject/{id}/{cycle_id}', [EmergencyPaymentCycleController::class, 'emergencyBeneficiaryDelete'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::post('/push-payroll-summary/{id}', [EmergencyPaymentCycleController::class, 'pushPayrollSummary'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        Route::get('/payment-cycle/payroll_wise-beneficiary/{cycle_id}/{payroll_id}', [EmergencyPaymentCycleController::class, 'getPayrollWiseBeneficiary'])->middleware(['role_or_permission:super-admin|emergency-payment-cycle-create']);
        /*----------------------------Payment Cycle End--------------------------------*/

        /*----------------------------Emergency Payroll Create Start--------------------------------*/
        // for payroll create
        Route::get('/get-program-info/{program_id}', [EmergencyPayrollController::class, 'getProgramInfo'])->middleware(['role_or_permission:super-admin|emergency-payroll-create|emergency-payroll-view']);
        Route::get('/get-active-installments/{allotment_id}', [EmergencyPayrollController::class, 'getActiveInstallments'])->middleware(['role_or_permission:super-admin|emergency-payroll-create|emergency-payroll-view']);
        Route::get('/get-allotment-area-statistics/{allotment_id}', [EmergencyPayrollController::class, 'getAllotmentAreaStatistics'])->middleware(['role_or_permission:super-admin|emergency-payroll-create|emergency-payroll-view']);
        Route::get('/get-allotment-area-list', [EmergencyPayrollController::class, 'getAllotmentAreaList'])->middleware(['role_or_permission:super-admin|emergency-payroll-create|emergency-payroll-view']);
        Route::get('/get-active-beneficiaries/{allotment_id}', [EmergencyPayrollController::class, 'getActiveBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-create|emergency-payroll-view']);
        Route::post('/get-selected-beneficiaries/{allotment_id}', [EmergencyPayrollController::class, 'getSelectedBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-create']);
        Route::post('/set-emergency-beneficiaries', [EmergencyPayrollController::class, 'setBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-create']);
        Route::get('/preview-beneficiaries', [EmergencyPayrollController::class, 'previewBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-create']);
        Route::post('/submit-emergency-payroll', [EmergencyPayrollController::class, 'submitPayroll'])->middleware(['role_or_permission:super-admin|emergency-payroll-create']);
        // for payroll approve
        Route::get('/get-payroll-active-beneficiaries/{payroll_id}', [EmergencyPayrollApprovalController::class, 'getActivePayrollBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        Route::get('/get-payroll-approve-beneficiaries/{payroll_id}', [EmergencyPayrollApprovalController::class, 'getPayrollApproveBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        Route::get('/get-payroll-reject-beneficiaries/{payroll_id}', [EmergencyPayrollApprovalController::class, 'getPayrollRejectedBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);

        Route::get('/get-payroll-approval-list', [EmergencyPayrollApprovalController::class, 'getPayrollList'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        Route::get('/view-beneficiaries/{payroll_id}', [EmergencyPayrollApprovalController::class, 'getActiveBeneficiaries'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        Route::post('/reject-beneficiary/{beneficiary_id}', [EmergencyPayrollApprovalController::class, 'rejectBeneficiary'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        Route::delete('/beneficiary/delete/{beneficiary_id}', [EmergencyPayrollController::class, 'beneficiaryDelete'])->middleware(['role_or_permission:super-admin|emergency-payroll-create|emergency-payroll-approval']);
        Route::post('/reject-payroll', [EmergencyPayrollApprovalController::class, 'rejectPayroll'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        Route::post('/approve-payroll/{id}', [EmergencyPayrollApprovalController::class, 'approvePayroll'])->middleware(['role_or_permission:super-admin|emergency-payroll-approval']);
        // For Reconciliation Data Pull
        /*----------------------------Emergency Payroll Create End--------------------------------*/


        /*----------------------------Emergency Payroll Reconciliation  start--------------------------------*/
        Route::get('/reconciliation/get', [EmergencyReconciliatioController::class, 'getEmergencyReconciliation'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);
        Route::get('/reconciliation/beneficiary/banks/{id}', [EmergencyReconciliatioController::class, 'getBanksByBeneficiaryLocation'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);
        Route::get('/reconciliation/beneficiary/mfses/{id}', [EmergencyReconciliatioController::class, 'getMfsesByBeneficiaryLocation'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);
        Route::get('/reconciliation/beneficiary/edit/{id}', [EmergencyReconciliatioController::class, 'edit'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);
        Route::put('/reconciliation/beneficiary/update/{id}', [EmergencyReconciliatioController::class, 'update'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);
        Route::post('/reconciliation/update/{id}', [EmergencyReconciliatioController::class, 'emergencyReconciliationUpdate'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);
        Route::delete('/reconciliation/delete/{id}', [EmergencyReconciliatioController::class, 'emergencyReconciliationDelete'])->middleware(['role_or_permission:super-admin|emergency-payment-reconciliation-create']);

        /*----------------------------Emergency Payroll Reconciliation Cycle End--------------------------------*/
        /*----------------------------Emergency Supplementary payroll--------------------------------*/
        Route::get('/get-emergency-cycle-installments', [EmergencySupplementaryController::class, 'getEmergencyCycleInstallments']);
        Route::get('/get-emergency-cycle-programs', [EmergencySupplementaryController::class, 'getEmergencyCyclePrograms']);
        Route::get('/get-cycle-financial-year', [EmergencySupplementaryController::class, 'getCycleFinancialYear']);
        Route::get('/get-payroll-payment-status', [EmergencySupplementaryController::class, 'getPayrollPaymentStatus']);
        /*---------------------------- Payroll Reconciliation Data Pull  start--------------------------------*/
        Route::get('/reconciliation-data-pull/get', [EmergencyReconciliatioController::class, 'reconciliationDataPullGetData'])->middleware(['role_or_permission:super-admin|emergency-payroll-reconciliation-data-pull-view']);
        Route::get('/reconciliation-data-pull/{id}', [EmergencyReconciliatioController::class, 'reconciliationDataPull'])->middleware(['role_or_permission:super-admin|emergency-payroll-reconciliation-data-pull-view']);
    });
});
