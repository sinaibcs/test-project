<?php

use App\Http\Controllers\Api\V1\Admin\OfficeController;

Route::middleware('auth:sanctum')->group(function () {
    Route::prefix('mobile/office')->group(function () {
        Route::post('/insert', [OfficeController::class, 'insertOffice'])->middleware(['role_or_permission:super-admin|office-create']);
        Route::get('/get', [OfficeController::class, 'getAllOfficePaginated'])->middleware(['role_or_permission:super-admin|office-view']);
        Route::get('/getAll', [OfficeController::class, 'getAllOffice']);
        Route::post('/update', [OfficeController::class, 'OfficeUpdate'])->middleware(['role_or_permission:super-admin|office-update']);
        Route::get('/destroy/{id}', [OfficeController::class, 'destroyOffice'])->middleware(['role_or_permission:super-admin|office-destroy']);
        Route::get('/get/{district_id}', [OfficeController::class, 'getAllOfficeByDistrictId'])->middleware(['role_or_permission:super-admin|office-view']);
        Route::get('/generate-pdf', [OfficeController::class, 'generatePdf']);
        // For multiple ward under office
        Route::get('/get-ward-under-office', [OfficeController::class, 'getAllWardUnderOffice']);
        Route::get('/wards/{officeId}', [OfficeController::class, 'getWardList']);
        // ->middleware(['role_or_permission:super-admin|office-create']);
        Route::post('/destroy/ward-under-office/', [OfficeController::class, 'destroyWardUnderOffice'])->middleware(['role_or_permission:super-admin|office-view']);
        // END For multiple ward under office
    });
});
