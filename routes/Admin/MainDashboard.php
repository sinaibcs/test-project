<?php

use App\Http\Controllers\Api\V1\Admin\ReportController;

Route::middleware('auth:sanctum')->group(function () {

        Route::prefix('admin/dashboard')->group(function () {
            Route::any('/office-count', [ReportController::class, 'officeCount']);
        });
});