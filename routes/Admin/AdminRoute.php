<?php

use App\Http\Controllers\Api\V1\Admin\NotificationController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Setting\ActivityLogController;
use App\Http\Controllers\DeviceRegistrationController;

Route::middleware('auth:sanctum')->group(function () {


    //    Route::post('admin/activity-log/all/filtered',[ActivityLogController::class, 'getAllActivityLogsPaginated'])->middleware(['role_or_permission:super-admin|main-setting-activity-log']);



    /* -------------------------------------------------------------------------- */
    /*                                 Role Routes                                */
    /* -------------------------------------------------------------------------- */

    Route::prefix('admin/role')->group(function () {

        Route::get('/get', [RoleController::class, 'getAllRolePaginated'])->middleware(['role_or_permission:super-admin|role-view']);
        Route::post('/insert', [RoleController::class, 'insert'])->middleware(['role_or_permission:super-admin|role-create']);
        Route::get('/edit/{id}', [RoleController::class, 'editRole'])->middleware(['role_or_permission:super-admin|role-edit']);
        Route::post('/update', [RoleController::class, 'updateRole'])->middleware(['role_or_permission:super-admin|role-update']);
        Route::delete('/destroy/{id}', [RoleController::class, 'destroyRole'])->middleware(['role_or_permission:super-admin|role-delete']);

        /* -------------------------------------------------------------------------- */
        /*                              Permission Routes                             */
        /* -------------------------------------------------------------------------- */
        Route::prefix('permission')->group(function () {

            Route::get('roles/unassign', [RoleController::class, 'getUnAssignPermissionRole'])->middleware(['role_or_permission:super-admin|rolePermission-create|rolePermission-edit']);
            Route::get('roles/all', [RoleController::class, 'getAllRole'])->middleware(['role_or_permission:super-admin|rolePermission-view']);
            Route::get('modules', [RoleController::class, 'getAllPermission'])->middleware(['role_or_permission:super-admin|rolePermission-view']);

            Route::post('/assign', [RoleController::class, 'AssignPermissionRole'])->middleware(['role_or_permission:super-admin|rolePermission-edit']);
            Route::get('/role_permission_edit/{id}', [RoleController::class, 'rolePermissionEdit'])->middleware(['role_or_permission:super-admin|rolePermission-edit']);
        });
    });
    Route::prefix('admin')->group(function(){
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notification/{id}/markAsSeen', [NotificationController::class, 'markAsSeen']);
    });
});

Route::controller(DeviceRegistrationController::class)->group(function () {
    Route::post('device-registration', 'deviceRegistration');
    Route::get('/get-ip', 'getIpAddress');
});
