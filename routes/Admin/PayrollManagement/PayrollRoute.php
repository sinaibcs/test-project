<?php

use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencyPaymentDashboardController;
use App\Http\Controllers\Api\V1\Admin\Emergency\EmergencySupplementaryController;
use App\Http\Controllers\Api\V1\Admin\PaymentProcessorController;
use App\Http\Controllers\Api\V1\Admin\Payroll\PaymentCycleController;
use App\Http\Controllers\Api\V1\Admin\Payroll\PayrollController;
use App\Http\Controllers\Api\V1\Admin\Payroll\ReconciliatioController;
use App\Http\Controllers\Api\V1\Admin\Payroll\SupplementaryController;
use App\Http\Controllers\Api\V1\Admin\PayrollDashboardController;
use App\Http\Controllers\Api\V1\Admin\PayrollSettingController;

Route::middleware('auth:sanctum')->group(function () {
    /* -------------------------------------------------------------------------- */
    /*                       Payroll setting Routes                         */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/payroll')->group(function () {
        //for payroll setting
        Route::get('/get-all-allowance', [PayrollSettingController::class, 'getAllAllowance'])->middleware(['role_or_permission:super-admin|payroll-setting-view']);
        Route::get('/get-financial-year', [PayrollSettingController::class, 'getFinancialYear']);
        Route::get('/get-all-installments', [PayrollSettingController::class, 'getAllInstallments'])->middleware(['role_or_permission:super-admin|payroll-setting-view']);
        Route::get('/get-installments', [PayrollSettingController::class, 'getInstallments']);
        Route::post('/setting-submit', [PayrollSettingController::class, 'payrollSettingSubmit']);
        Route::get('/get-setting-data', [PayrollSettingController::class, 'getSettingData']);
        //for payroll verification
        Route::post('/verification-setting-submit', [PayrollSettingController::class, 'payrollVerification'])->middleware(['role_or_permission:super-admin|payroll-verification-view']);
        Route::get('/get-verification-setting', [PayrollSettingController::class, 'getVerificationSetting'])->middleware(['role_or_permission:super-admin|payroll-verification-view']);
        //for payment processor
        Route::get('/get-banks', [PaymentProcessorController::class, 'getBanks']);
        Route::get('/get-branches/{id}', [PaymentProcessorController::class, 'getBranches']);
        Route::get('/get-mfs', [PaymentProcessorController::class, 'getMfs']);
        Route::apiResource('/payment-processor', PaymentProcessorController::class)->middleware(['role_or_permission:super-admin|payment-processor-create']);
        // beneficiary tracking information
        // Route::post('/payment-tracking-info', [PaymentProcessorController::class, 'getPaymentTrackingInfo'])->middleware(['role_or_permission:super-admin|payroll-payment-tracking']);
        // dashboard
        Route::get('/payroll-status-data', [PayrollDashboardController::class, 'payrollData']);
        Route::get('/payment-cycle-status-data', [PayrollDashboardController::class, 'paymentCycleStatusData']);
        Route::get('/monthly-approved-payroll', [PayrollDashboardController::class, 'monthlyApprovedPayroll']);
        Route::get('/program-wise-payroll', [PayrollDashboardController::class, 'programWisePayroll']);
        Route::get('/total-payment-processor', [PayrollDashboardController::class, 'totalPaymentProcessor']);
        Route::get('/program-wise-payment-cycle', [PayrollDashboardController::class, 'programWisePaymentCycle']);
        Route::get('/total-amount-disbursed', [PayrollDashboardController::class, 'totalAmountDisbursed']);
        Route::get('/program-balance', [PayrollDashboardController::class, 'programBalance']);
        //emergency payment dashboard
        Route::get('/emergency-payment-cycle-disbursement-status', [EmergencyPaymentDashboardController::class, 'paymentCycleDisbursementStatus']);
        Route::get('/emergency-dashboard-data', [EmergencyPaymentDashboardController::class, 'emergencyDashboardData']);
        Route::get('/emergency-program-wise-payroll', [EmergencyPaymentDashboardController::class, 'EmergencyProgramWisePayrollBeneficiary']);
        Route::get('/program-wise-payment-cycle-beneficiary', [EmergencyPaymentDashboardController::class, 'programWisePaymentCycleBeneficiaries']);
        Route::get('/emergency-program-balance', [EmergencyPaymentDashboardController::class, 'programBalance']);

        //emergency supplementary payroll
        Route::get('/emergency-supplementary-payroll', [EmergencySupplementaryController::class, 'emergencySupplementaryPayrollData'])->middleware(['role_or_permission:super-admin|emergency-supplementary-payroll-show']);
        Route::get('/emergency-supplementary-payroll-show/{id}', [EmergencySupplementaryController::class, 'emergencySupplementaryPayrollShow'])->middleware(['role_or_permission:super-admin|emergency-supplementary-cycle-details']);
        Route::get('/emergency-supplementary-beneficiary/{id}', [EmergencySupplementaryController::class, 'beneficiaryDetails'])->middleware(['role_or_permission:super-admin|emergency-supplementary-beneficiary-show']);
        Route::post('/send-emergency-supplementary-beneficiary', [EmergencySupplementaryController::class, 'emergencySupplementaryPayrollUpdate'])->middleware(['role_or_permission:super-admin|emergency-supplementary-cycle-details']);
        //supplementary payroll
        Route::get('/supplementary-payroll', [SupplementaryController::class, 'supplementaryPayrollData'])->middleware(['role_or_permission:super-admin|supplementary-payroll-show']);
        Route::get('/supplementary-payroll-show/{id}', [SupplementaryController::class, 'supplementaryPayrollShow'])->middleware(['role_or_permission:super-admin|supplementary-cycle-details']);
        Route::get('/supplementary-beneficiary/{id}', [SupplementaryController::class, 'beneficiaryDetails'])->middleware(['role_or_permission:super-admin|supplementary-beneficiary-show']);
        Route::post('/send-supplementary-beneficiary', [SupplementaryController::class, 'supplementaryPayrollUpdate'])->middleware(['role_or_permission:super-admin|supplementary-cycle-details']);
        Route::get('/get-cycle-installments', [SupplementaryController::class, 'getCycleInstallments']);
        Route::get('/get-cycle-programs', [SupplementaryController::class, 'getCyclePrograms']);
        Route::get('/get-cycle-financial-year', [SupplementaryController::class, 'getCycleFinancialYear']);
        Route::get('/get-payroll-payment-status', [SupplementaryController::class, 'getPayrollPaymentStatus']);
        // for payroll create
        Route::get('/get-user-wise-programs', [PayrollController::class, 'getUserWisePrograms'])->middleware(['role_or_permission:super-admin|payroll-create']);
        Route::get('/get-program-info/{program_id}', [PayrollController::class, 'getProgramInfo'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-active-installments/{program_id}/{financial_year_id}', [PayrollController::class, 'getActiveInstallments'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-installments/{program_id}', [PayrollController::class, 'getInstallments'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-allotment-area-list', [PayrollController::class, 'getAllotmentAreaList'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-allotment-class-list', [PayrollController::class, 'getAllotmentClassList'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-allotment-area-statistics/{allotment_id}', [PayrollController::class, 'getAllotmentAreaStatistics'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-active-beneficiaries/{allotment_id}', [PayrollController::class, 'getActiveBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/search-active-beneficiaries/{allotment_id}', [PayrollController::class, 'searchActiveBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/get-selected-beneficiaries', [PayrollController::class, 'getSelectedBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create']);
        Route::post('/set-beneficiaries', [PayrollController::class, 'setBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create']);
        Route::get('/preview-beneficiaries', [PayrollController::class, 'previewBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create']);
        Route::post('/submit-payroll', [PayrollController::class, 'submitPayroll'])->middleware(['role_or_permission:super-admin|payroll-create']);
        // for payroll approve
        Route::get('/get-payroll-approval-list', [PayrollController::class, 'getApprovalList'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-approval-view']);
        Route::put('/get-payroll-detail/{id}', [PayrollController::class, 'submitPayroll'])->middleware(['role_or_permission:super-admin|payroll-approval-create']);
        Route::get('/view-beneficiaries/{payroll_id}', [PayrollController::class, 'getActiveBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-approval-view']);
        Route::delete('/beneficiary/delete/{beneficiary_id}', [PayrollController::class, 'beneficiaryDelete'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::delete('/beneficiary/delete-all', [PayrollController::class, 'beneficiaryDeleteAll'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
        Route::get('/beneficiary/rollback/{allotment_id}', [PayrollController::class, 'rollback'])->middleware(['role_or_permission:super-admin|payroll-create|payroll-view']);
//        Route::put('/reject-beneficiary/{beneficiary_id}', [PayrollController::class, 'beneficiaryDelete'])->middleware(['role_or_permission:super-admin|payroll-approval-create']);
        Route::post('/reject-payroll', [PayrollController::class, 'rejectPayroll'])->middleware(['role_or_permission:super-admin|payroll-approval-create']);
        Route::post('/approve-payroll', [PayrollController::class, 'approvePayroll'])->middleware(['role_or_permission:super-admin|payroll-approval-view']);
        Route::post('/approve-all-payroll-beneficiaries', [PayrollController::class, 'approveAllPayrollBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-approval-view']);
        Route::post('/verify-payroll', [PayrollController::class, 'verifyPayroll'])->middleware(['role_or_permission:super-admin|payroll-button-verify']);
        Route::get('/get-payroll-beneficiaries', [PayrollController::class, 'getPayrollBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-button-view']);
        Route::get('/get-payroll-active-beneficiaries', [PayrollController::class, 'getActivePayrollBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-button-view']);
        Route::get('/get-payroll-approve-beneficiaries', [PayrollController::class, 'getPayrollApproveBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-button-view']);
        Route::get('/get-payroll-reject-beneficiaries', [PayrollController::class, 'getPayrollRejectedBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-button-view']);

        /*----------------------------Payment Cycle start--------------------------------*/
        Route::get('/payment-cycle', [PaymentCycleController::class, 'getPaymentCycle'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-show']);
        Route::get('/program-wise-installment/{id}', [PaymentCycleController::class, 'programWiseInstallment'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-show']);
        Route::get('/payment-cycle/view/{id}', [PaymentCycleController::class, 'getPaymentCycleViewById'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-show']);
        Route::get('/payment-cycle/payroll_wise-beneficiary/{cycle_id}/{payroll_id}', [PaymentCycleController::class, 'getPayrollWiseBeneficiary'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-show']);
        Route::get('/payment-cycle/reject/{id}', [PaymentCycleController::class, 'getPaymentCycleRejectById'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-reject']);
        Route::get('/cycle/payrolls/{id}', [PaymentCycleController::class, 'getPaymentCyclePayrolls'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-show']);
        Route::delete('/payroll/{id}/{cycle_id}', [PaymentCycleController::class, 'payrollDelete'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-reject']);
        Route::delete('/beneficiary/reject/{id}/{cycle_id}', [PaymentCycleController::class, 'payrollBeneficiaryDelete'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-reject']);
        Route::post('/beneficiary/reject-multiple/{cycle_id}', [PaymentCycleController::class, 'rejectMultipleBeneficiaries'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-reject']);
        Route::delete('/payment-cycle-delete/{id}/{payroll_id}', [PaymentCycleController::class, 'paymentCycleDelete'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-reject']);
        Route::post('/push-payroll-summary/{id}', [PaymentCycleController::class, 'pushPayrollSummary'])->middleware(['role_or_permission:super-admin|payroll-payment-cycle-send']);
        /*----------------------------Payment Cycle End--------------------------------*/

        /*---------------------------- Payroll Reconciliation  start--------------------------------*/
        Route::get('/reconciliation/get', [ReconciliatioController::class, 'getReconciliation'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-cordination-create']);
        Route::get('/reconciliation/beneficiary/edit/{id}', [ReconciliatioController::class, 'edit'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-cordination-edit']);
        Route::put('/reconciliation/beneficiary/update/{id}', [ReconciliatioController::class, 'update'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-cordination-edit']);
        Route::post('/reconciliation/update/{id}/{payroll_payment_cycle_id}', [ReconciliatioController::class, 'reconciliationUpdate'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-cordination-approve']);
        Route::delete('/reconciliation/delete/{id}/{payroll_payment_cycle_id}', [ReconciliatioController::class, 'reconciliationDelete'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-cordination-delete']);
        /*---------------------------- Payroll Reconciliation Cycle End--------------------------------*/

        /*---------------------------- Payroll Reconciliation Data Pull  start--------------------------------*/
        Route::get('/reconciliation-data-pull/get', [ReconciliatioController::class, 'reconciliationDataPullGetData'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-data-pull-view']);
        Route::get('/reconciliation-data-pull/{id}', [ReconciliatioController::class, 'reconciliationDataPull'])->middleware(['role_or_permission:super-admin|payroll-reconciliation-data-pull-view']);

    });

});
Route::post('admin/payroll/payment-tracking-info', [PaymentProcessorController::class, 'getPaymentTrackingInfo']);
