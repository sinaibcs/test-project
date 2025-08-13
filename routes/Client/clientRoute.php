<?php


use App\Http\Controllers\Client\ApplicationController;
use App\Http\Controllers\Client\BeneficiaryController;
use App\Http\Controllers\Client\GlobalController;

Route::prefix('client')->group(function () {
    Route::get('application/get', [ApplicationController::class, 'getAllApplicationPaginated']);
    Route::get('application/get/{id}', [ApplicationController::class, 'getApplicationById']);

    Route::get('beneficiary/list', [BeneficiaryController::class, 'getBeneficiariesList']);
    Route::get('getByBeneficiaryId/{beneficiary_id}', [BeneficiaryController::class, 'getBeneficiaryById']);

    Route::post('update-beneficiary/nominee-info/{beneficiary_id}', [BeneficiaryController::class, 'updateNomineeInfo']);
    Route::post('update-beneficiary/account-info/{beneficiary_id}', [BeneficiaryController::class, 'updateAccountInfo']);



    Route::get('/program',[GlobalController::class, 'getAllProgram']);
    Route::get('/division/get',[GlobalController::class, 'getAllDivision']);
    Route::get('/district/get/{division_id}',[GlobalController::class, 'getAllDistrictByDivisionId']);
    Route::get('/city/get/{district_id}',[GlobalController::class, 'getAllCityByDistrictId']);
    Route::get('/upazila/get/{district_id}',[GlobalController::class, 'getAllThanaByDistrictId']);
    Route::get('/district-pourashava/get/{district_id}',[GlobalController::class, 'getAllDistrictPourashavaByDistrictId']);
    Route::get('/thana/get/city/{city_id}',[GlobalController::class, 'getAllThanaByCityId']);
    Route::get('/union/get/{upazila_id}',[GlobalController::class, 'getAllUnionByThanaId']);
    Route::get('/union/pouro/get/{upazila_id}',[GlobalController::class, 'getAllPouroByThanaId']);
    Route::get('/ward/get/{location_id}',[GlobalController::class, 'getAllWardByUnionId']);

});

