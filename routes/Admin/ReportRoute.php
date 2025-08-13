<?php


use App\Http\Controllers\Api\V1\Admin\Report\PowerBI\PowerBiController;
use App\Http\Controllers\Api\V1\Admin\ReportController;


Route::middleware('auth:sanctum')->group(function () {

    Route::prefix('admin')->group(function () {
        Route::any('/generate-pdf', [ReportController::class, 'commonReport']);
        Route::get('/report/otp-dashboard', [ReportController::class, 'otpDashboard']);
    });
    Route::prefix('admin/report')->group(function () {
        Route::apiResource('/power-bi-report', PowerBiController::class);
        Route::get('/beneficiary-summery', [ReportController::class, 'beneficiarySummery']);
    });
});
