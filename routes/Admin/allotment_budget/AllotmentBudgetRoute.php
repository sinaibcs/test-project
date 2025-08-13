<?php

use App\Http\Controllers\Api\V1\Admin\Budget\AllotmentController;
use App\Http\Controllers\Api\V1\Admin\Budget\BudgetController;
use App\Http\Controllers\Api\V1\Admin\Budget\DashboardController;

// Route::get('/getFormatExcel', [BudgetController::class, 'getFormatExcel']);

Route::middleware('auth:sanctum')->group(function () {
    /* -------------------------------------------------------------------------- */
    /*                               Budget Management  Routes                    */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/budget')->group(function () {
        Route::get('/getCurrentFinancialYear', [BudgetController::class, 'getCurrentFinancialYear']);
        Route::get('/getBudgetFinancialYear', [BudgetController::class, 'getBudgetFinancialYear']);
        Route::get('/getUserLocation', [BudgetController::class, 'getUserLocation']);
        Route::get('/list', [BudgetController::class, 'list'])->middleware(['role_or_permission:super-admin|budget-view']);
        Route::post('/add', [BudgetController::class, 'add'])->middleware(['role_or_permission:super-admin|budget-create']);
        Route::get('/show/{id}', [BudgetController::class, 'show'])->middleware(['role_or_permission:super-admin|budget-view']);
        Route::get('/getDetailBudget/{budget_id}/{location_id?}', [BudgetController::class, 'getDetailBudget'])->middleware(['role_or_permission:super-admin|budget-view']);
        Route::put('/update/{id}', [BudgetController::class, 'update'])->middleware(['role_or_permission:super-admin|budget-edit']);
        Route::put('/approve/{id}', [BudgetController::class, 'approve'])->middleware(['role_or_permission:super-admin|budget-edit']);
        Route::delete('/delete/{id}', [BudgetController::class, 'delete'])->middleware(['role_or_permission:super-admin|budget-delete']);
        Route::get('/getProjection', [BudgetController::class, 'getProjection'])->middleware(['role_or_permission:super-admin|budget-create|budget-view']);
        Route::get('/getFormatExcel', [BudgetController::class, 'getFormatExcel'])->middleware(['role_or_permission:super-admin|budget-create']);
        Route::post('/uploadExcel', [BudgetController::class, 'uploadExcel'])->middleware(['role_or_permission:super-admin|budget-create|budget-view']);
        // budget detail
        Route::get('/detail/list/{budget_id}', [BudgetController::class, 'detailList'])->middleware(['role_or_permission:super-admin|budget-view']);
        Route::put('/detail/update/{budget_id}', [BudgetController::class, 'detailUpdate'])->middleware(['role_or_permission:super-admin|budget-view']);
        // report
        Route::get('/detail/report/{budget_id}', [BudgetController::class, 'getBudgetDetailListPdf'])->middleware(['role_or_permission:super-admin|budget-view']);
        // dashboard
        Route::get('/dashboard/getBudgetAndAllotmentSummary', [DashboardController::class, 'getBudgetAndAllotmentSummary'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view|budgetDashboard-view']);
        Route::get('/dashboard/getTotalBudget', [DashboardController::class, 'getTotalBudget'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view|budgetDashboard-view']);
        Route::get('/dashboard/getTotalAllotment', [DashboardController::class, 'getTotalAllotment'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view|budgetDashboard-view']);
        Route::get('/dashboard/getYearlyBeneficiaries', [DashboardController::class, 'getYearlyBeneficiaries'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view|budgetDashboard-view']);
    });

    /* -------------------------------------------------------------------------- */
    /*                               Allotment Management  Routes                 */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/allotment')->group(function () {
        Route::get('/summary', [AllotmentController::class, 'summary'])->middleware(['role_or_permission:super-admin|allotment-view']);
        Route::get('/list', [AllotmentController::class, 'list'])->middleware(['role_or_permission:super-admin|allotment-view']);
        Route::get('/getList/{program_id}/{financial_year_id}/{location_id?}', [AllotmentController::class, 'getList'])->middleware(['role_or_permission:super-admin|allotment-view']);
        Route::get('/download-excel-format/{program_id}/{financial_year_id}', [AllotmentController::class, 'downloadFormat'])->middleware(['role_or_permission:super-admin|allotment-edit']);
        Route::post('/uploadExcel', [AllotmentController::class, 'uploadExcel'])->middleware(['role_or_permission:super-admin|allotment-edit']);
        Route::get('/navigate', [AllotmentController::class, 'navigate'])->middleware(['role_or_permission:super-admin|allotment-view']);
        Route::get('/show/{id}', [AllotmentController::class, 'show'])->middleware(['role_or_permission:super-admin|allotment-view']);
        Route::put('/update', [AllotmentController::class, 'updateMany'])->middleware(['role_or_permission:super-admin|allotment-edit']);
        Route::put('/update/{id}', [AllotmentController::class, 'update'])->middleware(['role_or_permission:super-admin|allotment-edit']);
        Route::delete('/delete/{id}', [AllotmentController::class, 'delete'])->middleware(['role_or_permission:super-admin|allotment-delete']);
        // report
        Route::get('/report', [AllotmentController::class, 'report'])->middleware(['role_or_permission:super-admin|allotment-view']);
        Route::get('/getReport/{program_id}/{financial_year_id}/{location_id?}', [AllotmentController::class, 'getReport'])->middleware(['role_or_permission:super-admin|allotment-view']);
        // dashboard
        Route::get('/dashboard/totalAllotmentAmount', [DashboardController::class, 'totalAllotmentAmount'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view']);
        Route::get('/dashboard/currentAllotmentAmount', [DashboardController::class, 'currentAllotmentAmount'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view']);
        Route::get('/dashboard/getProgramWiseAllotmentList', [DashboardController::class, 'getProgramWiseAllotmentList'])->middleware(['role_or_permission:super-admin|budget-view|allotment-view']);
    });


});
