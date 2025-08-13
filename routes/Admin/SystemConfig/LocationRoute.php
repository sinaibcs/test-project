<?php

use App\Http\Controllers\Api\V1\Admin\LocationController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\UserLocationController;

Route::middleware('auth:sanctum')->group(function () {


    /* -------------------------------------------------------------------------- */
    /*                               Division Routes                              */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/division')->group(function () {

        Route::post('/insert', [LocationController::class, 'insertDivision'])->middleware(['role_or_permission:super-admin|division-create']);
        Route::get('/get',[LocationController::class, 'getAllDivisionPaginated']);
        Route::post('/update', [LocationController::class, 'divisionUpdate'])->middleware(['role_or_permission:super-admin|division-edit']);
        Route::get('/destroy/{id}', [LocationController::class, 'destroyDivision'])->middleware(['role_or_permission:super-admin|division-delete']);
        Route::get('/generate-pdf', [ReportController::class, 'divisionReport']);
    });



    Route::get('get-divisions', [UserLocationController::class, 'getDivisions']);
    Route::get('get-districts/{divisionId}', [UserLocationController::class, 'getDistricts']);
    Route::get('get-upazilas/{districtId}', [UserLocationController::class, 'getUpazilas']);
    Route::get('get-wards/{parentId}/{locationTypeId}', [UserLocationController::class, 'getWards']);

    Route::get('get-union-or-thana/{upazilaId}/{locationTypeId}', [UserLocationController::class, 'getUnionOrThana']);

    Route::get('get-cities-pouroshavas/{districtId}/{locationType}', [UserLocationController::class, 'getCityPouroshavaList']);
    Route::any('get-offices', [UserLocationController::class, 'getOfficeList']);
    Route::get('get-office-wards/{officeId}', [UserLocationController::class, 'getOfficeWardList']);
    Route::get('get-office-unions/{officeId}', [UserLocationController::class, 'getOfficeUnionList']);


    /* -------------------------------------------------------------------------- */
    /*                               District Routes                              */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/district')->group(function () {

        Route::post('/insert', [LocationController::class, 'insertDistrict'])->middleware(['role_or_permission:super-admin|district-create']);
        Route::get('/get',[LocationController::class, 'getAllDistrictPaginated']);
        Route::get('/get/{division_id}',[LocationController::class, 'getAllDistrictByDivisionId']);
        Route::post('/update', [LocationController::class, 'districtUpdate'])->middleware(['role_or_permission:super-admin|district-edit']);
        Route::get('/destroy/{id}', [LocationController::class, 'destroyDistrict'])->middleware(['role_or_permission:super-admin|district-delete']);
        Route::get('/generate-pdf', [\App\Http\Controllers\PDFController::class, 'index']);
        Route::get('/generate-pdf', [ReportController::class, 'districtReport']);
    });

    /* -------------------------------------------------------------------------- */
    /*                               City Routes                                  */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/city')->group(function () {

        Route::post('/insert', [LocationController::class, 'insertCity'])->middleware(['role_or_permission:super-admin|city-create']);
        Route::get('/get',[LocationController::class, 'getAllCityPaginated']);
        Route::post('/update', [LocationController::class, 'cityUpdate'])->middleware(['role_or_permission:super-admin|city-edit']);
        Route::get('/destroy/{id}', [LocationController::class, 'destroyCity'])->middleware(['role_or_permission:super-admin|city-delete']);
        Route::get('/get/{district_id}/{location_type}',[LocationController::class, 'getAllCityByDistrictId']);
        Route::get('/generate-pdf', [ReportController::class, 'cityReport']);
    });

    /* -------------------------------------------------------------------------- */
    /*                               Thana Routes                                  */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/thana')->group(function () {

        Route::post('/insert', [LocationController::class, 'insertThana'])->middleware(['role_or_permission:super-admin|thana-create']);
        Route::get('/get',[LocationController::class, 'getAllThanaPaginated']);
        Route::get('/get/{district_id}',[LocationController::class, 'getAllThanaByDistrictId']);
        Route::get('/get/city/{city_id}',[LocationController::class, 'getAllThanaByCityId']);
        Route::post('/update', [LocationController::class, 'thanaUpdate'])->middleware(['role_or_permission:super-admin|thana-edit']);
        Route::get('/destroy/{id}', [LocationController::class, 'destroyThana'])->middleware(['role_or_permission:super-admin|thana-delete']);
        Route::get('/generate-pdf', [\App\Http\Controllers\PDFController::class, 'index']);

    });


    /* -------------------------------------------------------------------------- */
    /*                               Union Routes                                  */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/union')->group(function () {

        Route::post('/insert', [LocationController::class, 'insertUnion'])->middleware(['role_or_permission:super-admin|union-create']);
        Route::get('/get',[LocationController::class, 'getAllUnionPaginated']);
        Route::get('/get/{thana_id}',[LocationController::class, 'getAllUnionByThanaId']);
        Route::get('/pouro/get/{thana_id}',[LocationController::class, 'getAllPouroByThanaId']);
        Route::post('/update', [LocationController::class, 'unionUpdate'])->middleware(['role_or_permission:super-admin|union-edit']);
        Route::get('/destroy/{id}', [LocationController::class, 'destroyUnion'])->middleware(['role_or_permission:super-admin|union-delete']);
        Route::get('/generate-pdf', [ReportController::class, 'unionReport']);
        Route::get('/generate-excel', [ReportController::class, 'unionReportExcel']);
    });


    /* -------------------------------------------------------------------------- */
    /*                               Ward Routes                                  */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/ward')->group(function () {

        Route::post('/insert', [LocationController::class, 'insertWard'])->middleware(['role_or_permission:super-admin|ward-create']);
        Route::get('/get',[LocationController::class, 'getAllWardPaginated']);
        Route::get('/get/thana/{thana_id}',[LocationController::class, 'getAllWardByThanaId']);
         Route::get('/get/district_pouro/{district_pouro_id}',[LocationController::class, 'getAllWardByDistPouroId']);
        Route::get('/get/pouro/{pouro_id}',[LocationController::class, 'getAllWardByPouroId']);
        Route::get('/get/{union_id}',[LocationController::class, 'getAllWardByUnionId']);
        Route::post('/update', [LocationController::class, 'wardUpdate'])->middleware(['role_or_permission:super-admin|ward-edit']);
        Route::get('/destroy/{id}', [LocationController::class, 'destroyWard'])->middleware(['role_or_permission:super-admin|ward-delete']);
        Route::get('/generate-pdf', [ReportController::class, 'wardReport']);

    });

    Route::get('admin/get-rootlocations', [LocationController::class, 'getRootLocations']);
    Route::get('admin/get-sublocation/{location_id}', [LocationController::class, 'getSublocation']);

});
