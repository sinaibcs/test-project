<?php


use App\Http\Controllers\Api\V1\Admin\Training\ParticipantController;
use App\Http\Controllers\Api\V1\Admin\Training\TimeSlotController;
use App\Http\Controllers\Api\V1\Admin\Training\TrainerController;
use App\Http\Controllers\Api\V1\Admin\Training\TrainingCircularController;
use App\Http\Controllers\Api\V1\Admin\Training\TrainingDashboardController;
use App\Http\Controllers\Api\V1\Admin\Training\TrainingParticipantController;
use App\Http\Controllers\Api\V1\Admin\Training\TrainingProgramController;
use App\Http\Controllers\Api\V1\Admin\Training\TrainingRatingController;

Route::middleware('auth:sanctum')->prefix('admin/training')->group(function () {
    Route::any('trainers/status/{trainer}', [TrainerController::class, 'updateStatus']);

    Route::put('programs/status/{program}', [TrainingProgramController::class, 'updateStatus']);
    Route::put('programs/accept-invite/{participant}', [TrainingProgramController::class, 'acceptInvitation']);
    Route::put('programs/exam-status/{program}', [TrainingProgramController::class, 'updateExamStatus']);
    Route::put('programs/rating-status/{program}', [TrainingProgramController::class, 'updateRatingStatus']);
    Route::get('program-circulars', [TrainingProgramController::class, 'circulars']);
    Route::get('program-trainers', [TrainingProgramController::class, 'trainers']);
    Route::get('program-time-slots', [TrainingProgramController::class, 'timeSlots']);
    Route::get('programs/sync-data/{program}', [TrainingProgramController::class, 'syncData']);
    Route::get('programs-test', [TrainingProgramController::class, 'testKobo']);
    Route::put('participants/update-status/{participant}', [ParticipantController::class, 'updateStatus']);
    Route::post('participants/external', [TrainingParticipantController::class, 'storeExternalParticipant']);
    Route::get('participants/users/{type}', [TrainingParticipantController::class, 'getUsers']);
    Route::get('participants/circulars', [TrainingParticipantController::class, 'trainingCirculars']);
    Route::resource('participants', ParticipantController::class);
    Route::post('token/update', [TimeSlotController::class, 'updateToken']);
    Route::get('kobo_token', [TimeSlotController::class, 'getToken']);

    Route::apiResource('trainers', TrainerController::class);
    Route::apiResource('circulars', TrainingCircularController::class);
    Route::apiResource('time-slots', TimeSlotController::class);
    Route::apiResource('programs', TrainingProgramController::class);
    Route::post('trainer-rating', [TrainingRatingController::class, 'store']);

});

Route::middleware('auth:sanctum')->prefix('admin/training/dashboard')->group(function () {
    Route::get('training-program-list',[TrainingDashboardController::class,'trainingProgramlist']);
    Route::get('training-module-list',[TrainingDashboardController::class,'trainingModulelist']);
    Route::get('calculation-cards',[TrainingDashboardController::class,'cardCalculation']);
    Route::get('training-status',[TrainingDashboardController::class,'trainingStatus']);
    Route::get('month-wise-participants',[TrainingDashboardController::class,'monthWiseParticipants']);
    Route::get('training-mode',[TrainingDashboardController::class,'trainingMode']);
    Route::get('top-participants',[TrainingDashboardController::class,'topParticipants']);
    Route::get('top-trainers',[TrainingDashboardController::class,'topTrainers']);
});

Route::get('circulars-details/{circular}', [TrainingCircularController::class, 'show']);
Route::get('training/program-details/{program}', [TrainingProgramController::class, 'show']);

