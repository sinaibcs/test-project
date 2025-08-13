<?php

use App\Http\Controllers\IconController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\Api\V1\Admin\MenuController;
use App\Http\Controllers\Api\V1\Admin\AdminController;
use App\Http\Controllers\Api\V1\Admin\DeviceController;
use App\Http\Controllers\Api\V1\Admin\OfficeController;
use App\Http\Controllers\Api\V1\Admin\SystemconfigController;
use App\Http\Controllers\Api\V1\Admin\financialYearController;
use App\Http\Controllers\Api\V1\Admin\SystemconfigDashboardController;

Route::middleware('auth:sanctum')->group(function () {


    /* -------------------------------------------------------------------------- */
    /*                               Lookup Management Routes                     */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/system-configuration/dashboard')->group(function () {
        Route::get('/get-all-location-application-count', [SystemconfigDashboardController::class, 'getAllLocationApplicationCount'])/*->middleware(['role_or_permission:super-admin'])*/
        ;
        Route::get('/get-program-wise-beneficiary-count', [SystemconfigDashboardController::class, 'programWiseBeneficiaryCount'])/*->middleware(['role_or_permission:super-admin'])*/
        ;
        Route::get('/office-wise-total-user-count', [SystemconfigDashboardController::class, 'officeWiseTotalUserCount'])/*->middleware(['role_or_permission:super-admin'])*/
        ;
    });

    /* -------------------------------------------------------------------------- */
    /*                               Office Management Routes                              */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/office')->group(function () {

        Route::post('/insert', [OfficeController::class, 'insertOffice'])->middleware(['role_or_permission:super-admin|office-create']);
        Route::get('/get', [OfficeController::class, 'getAllOfficePaginated'])->middleware(['role_or_permission:super-admin|office-view']);
        Route::post('/update', [OfficeController::class, 'OfficeUpdate'])->middleware(['role_or_permission:super-admin|office-edit']);
        Route::get('/destroy/{id}', [OfficeController::class, 'destroyOffice'])->middleware(['role_or_permission:super-admin|office-destroy']);
        Route::get('/get/{district_id}', [OfficeController::class, 'getAllOfficeByDistrictId'])->middleware(['role_or_permission:super-admin|office-view']);
        Route::get('/generate-pdf', [OfficeController::class, 'generatePdf']);

        Route::get('/get-locations-under-office-area/{office_id}', [OfficeController::class, 'getLocationsUnderOfficeArea'])->middleware(['role_or_permission:super-admin|office-edit']);
        Route::post('/assign-wards-to-office', [OfficeController::class, 'assignWardsToOffice'])->middleware(['role_or_permission:super-admin|office-edit']);
        // For multiple ward under office
        Route::get('/get-ward-under-office', [OfficeController::class, 'getAllWardUnderOffice']);
        Route::get('/wards/{officeId}', [OfficeController::class, 'getWardList']);
        // ->middleware(['role_or_permission:super-admin|office-create']);
        Route::post('/destroy/ward-under-office/', [OfficeController::class, 'destroyWardUnderOffice'])->middleware(['role_or_permission:super-admin|office-view']);
        // END For multiple ward under office

        Route::get('/get-report', [OfficeController::class, 'getAllOfficeReport'])->middleware(['role_or_permission:super-admin|office-view']);

    });

    /* -------------------------------------------------------------------------- */
    /*                               Lookup Management Routes                     */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/lookup')->group(function () {

        Route::post('/insert', [AdminController::class, 'insertlookup'])/*->middleware(['role_or_permission:super-admin|demo-graphic-create'])*/
        ;
        Route::get('/get', [AdminController::class, 'getAllLookupPaginated'])/*->middleware(['role_or_permission:super-admin|demo-graphic-view'])*/
        ;
        Route::post('/update', [AdminController::class, 'LookupUpdate'])/*->middleware(['role_or_permission:super-admin|demo-graphic-update'])*/
        ;
        Route::post('/reorder', [AdminController::class, 'lookupReorder']);
        Route::get('/get/{type}', [AdminController::class, 'getAllLookupByType']);
        Route::get('/destroy/{id}', [AdminController::class, 'destroyLookup'])/*->middleware(['role_or_permission:super-admin|demo-graphic-destroy'])*/
        ;
    });

    /* -------------------------------------------------------------------------- */
    /*                               Allowance program Management  Routes         */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/allowance')->group(function () {

        Route::post('/insert', [SystemconfigController::class, 'insertallowance'])->middleware(['role_or_permission:super-admin|allowance-create']);
        Route::get('/get', [SystemconfigController::class, 'getAllallowancePaginated'])->middleware(['role_or_permission:super-admin|allowance-view|emergency-allotment-view']);
        Route::get('/edit/{id}', [SystemconfigController::class, 'edit'])->middleware(['role_or_permission:super-admin|allowance-edit']);
        Route::put('/update/{id}', [SystemconfigController::class, 'AllowanceUpdate'])->middleware(['role_or_permission:super-admin|allowance-edit']);
        Route::delete('/destroy/{id}', [SystemconfigController::class, 'destroyAllowance'])->middleware(['role_or_permission:super-admin|allowance-delete']);
        Route::post('/gender_delete', [SystemconfigController::class, 'destroyGender'])->middleware(['role_or_permission:super-admin|allowance-delete']);
        Route::post('/disable_delete/{id}', [SystemconfigController::class, 'destroyDisable'])->middleware(['role_or_permission:super-admin|allowance-delete']);
        Route::get('/status/{id}', [SystemconfigController::class, 'AllowanceStatusUpdate'])->middleware(['role_or_permission:super-admin|allowance-edit']);
        Route::get('/application-status/{id}', [SystemconfigController::class, 'AllowanceApplicationStatusUpdate'])->middleware(['role_or_permission:super-admin|allowance-edit']);
        // Route::get('/allowance-additional-field/get', [SystemconfigController::class, 'getAllowanceAdditionalFieldPaginated'])->middleware(['role_or_permission:super-admin|demo-graphic-create|allowance-creat']);

        Route::get('/get_additional_field', [SystemconfigController::class, 'getAdditionalField'])->middleware(['role_or_permission:super-admin|allowanceField-view']);
        Route::post('/allowance-additional-field/insert', [SystemconfigController::class, 'insertAllowanceAdditionalField'])->middleware(['role_or_permission:super-admin|allowanceField-create']);
        Route::post('/allowance-additional-field/update', [SystemconfigController::class, 'updateAllowanceAdditionalField'])->middleware(['role_or_permission:super-admin|allowanceField-delete']);
        Route::delete('field/destroy/{id}', [SystemconfigController::class, 'destroyField'])->middleware(['role_or_permission:super-admin|allowanceField-delete']);
        Route::post('/set-online-application-area', [SystemconfigController::class, 'setAllowanceProgramApplicationAreas'])->middleware(['role_or_permission:super-admin|allowance-edit']);
        Route::post('/enable-application-areas', [SystemconfigController::class, 'enableAllApplicationAreas'])->middleware(['role_or_permission:super-admin|allowance-edit']);
    });

    /* -------------------------------------------------------------------------- */
    /*                              Financial Year Management  Routes             */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/financial-year')->group(function () {

        Route::post('/insert', [financialYearController::class, 'insertFinancialYear'])->middleware(['role_or_permission:super-admin|financial-create']);
        Route::get('/get', [financialYearController::class, 'getFinancialPaginated'])/*->middleware(['role_or_permission:super-admin|financial-view'])*/
        ;
        Route::get('/list', [financialYearController::class, 'getFinancialYears'])/*->middleware(['role_or_permission:super-admin|financial-view'])*/
        ;
        Route::get('/destroy/{id}', [financialYearController::class, 'destroyFinancial'])->middleware(['role_or_permission:super-admin|financial-delete']);
    });

    /* -------------------------------------------------------------------------- */
    /*                          Device management Routes                          */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/device')->group(function () {
        Route::get('/show/{device}', [DeviceController::class, 'show']);
        Route::post('/status/{id}', [DeviceController::class, 'deviceStatusUpdate'])->middleware(['role_or_permission:super-admin|device-registration-edit']);
        Route::post('/insert', [DeviceController::class, 'insertDevice'])->middleware(['role_or_permission:super-admin|device-registration-create']);
        Route::get('/get', [DeviceController::class, 'getAllDevicePaginated'])->middleware(['role_or_permission:super-admin|device-registration-view']);
        Route::get('/get_users', [DeviceController::class, 'getUsers'])->middleware(['role_or_permission:super-admin|device-registration-view']);
        Route::get('/edit/{id}', [DeviceController::class, 'deviceEdit'])->middleware(['role_or_permission:super-admin|device-registration-view']);
        Route::post('/update', [DeviceController::class, 'deviceUpdate'])->middleware(['role_or_permission:super-admin|device-registration-edit']);
        Route::delete('/destroy/{id}', [DeviceController::class, 'destroyDevice'])->middleware(['role_or_permission:super-admin|device-registration-delete']);

        Route::get('/generate-excel', [DeviceController::class, 'deviceReportExcel']);
        Route::get('/generate-pdf', [DeviceController::class, 'deviceReportPdf']);
    });


    /* -------------------------------------------------------------------------- */
    /*                           Menu Management Routes                           */
    /* -------------------------------------------------------------------------- */
    Route::prefix('admin/menu')->group(function () {

        Route::post('/insert', [MenuController::class, 'insertMenu'])->middleware(['role_or_permission:super-admin|menu-create']);
        Route::get('/get', [MenuController::class, 'getAllMenu']);
        Route::get('/get-all', [MenuController::class, 'getMenus']);
        Route::get('/get_page_url', [MenuController::class, 'getPageUrl']);
        Route::get('/get_parent', [MenuController::class, 'getParent']);
        Route::get('/edit/{id}', [MenuController::class, 'menuEdit']);
        Route::put('/update/{id}', [MenuController::class, 'updateMenu']);
        Route::delete('/destroy/{id}', [MenuController::class, 'destroyMenu'])->middleware(['role_or_permission:super-admin|menu-delete']);
    });
    Route::post('/save-language-data-bn', [LanguageController::class, 'LangStoreBn'])->middleware(['role_or_permission:super-admin|language-bn']);
    Route::post('/save-language-data-en', [LanguageController::class, 'LangStoreEn'])->middleware(['role_or_permission:super-admin|language-en']);
    Route::get('/get-language-data-bn', [LanguageController::class, 'getLanguageDataBn'])->middleware(['role_or_permission:super-admin|language-bn']);
    Route::get('/get-language-data-en', [LanguageController::class, 'getLanguageDataEn'])->middleware(['role_or_permission:super-admin|language-en']);



    Route::get('/icons', [IconController::class, 'index']);
});

Route::get('/translations/{locale}', function ($locale) {
$path = resource_path("lang/{$locale}.json");
if (file_exists($path)) {
    return response()->json(json_decode(file_get_contents($path)), 200);
}

return response()->json(['error' => 'Translation file not found'], 404);
});


Route::get('/admin/allowance/{programId}/get-online-application-disabled-areas', [SystemconfigController::class, 'getAllowanceProgramApplicationDisabledAreas']);
