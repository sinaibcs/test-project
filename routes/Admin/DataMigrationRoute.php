<?php

use App\Http\Controllers\Api\V1\Admin\Datamigration\BeneficiaryCsvData\CsvUploadController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('admin/beneficiary')->group(function () {
        Route::post('/upload-csv', [CsvUploadController::class, 'upload'])->middleware(['role_or_permission:super-admin|beneficiaryMigrration-create']);
        Route::post('/upload-csv/store', [CsvUploadController::class, 'store'])->middleware(['role_or_permission:super-admin|beneficiaryMigrration-create']);
    });
});
