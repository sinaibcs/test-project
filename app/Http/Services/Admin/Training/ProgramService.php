<?php

namespace App\Http\Services\Admin\Training;

use App\Helpers\Helper;
use App\Models\TrainingParticipant;
use App\Models\TrainingProgram;
use App\Models\User;

class ProgramService
{

    public function storeProgram($request)
    {
        $program = new TrainingProgram();
        $program->program_name = $request->program_name;
        $program->training_circular_id = $request->training_circular_id;
        $program->start_date = $request->start_date;
        $program->end_date = $request->end_date;
        $program->description = $request->description;
        $program->on_days = $request->on_days;
        $program->form_id = $request->form_id;
        $program->training_form_id = $request->training_form_id;
        $program->question_link = $request->question_link;
        $program->trainer_ratings_link = $request->trainer_ratings_link;
        $program->status = $request->status;

        $program->save();

        $program->modules()->sync($request->circular_modules);
        $program->trainers()->sync($request->trainers);
        $this->syncUserData($request, $program);

        return $program;
    }


    public function syncUserData($request, $program)
    {
        $data = [];
        foreach ((array)$request->users as $userId) {
            $passcode = $program->participants()->where('user_id', $userId)->value('passcode');
            $data[$userId] = [
                'training_circular_id' => $program->training_circular_id,
                'passcode' => $passcode ?: rand(1e7, 1e10)
            ];
        }
        $program->users()->sync($data);

        $program->users->map(function ($user) {
            return $user->assignRole('participant');
        });
    }




    public function updateProgram($request, $program)
    {
        $program->program_name = $request->program_name;
        $program->training_circular_id = $request->training_circular_id;
        $program->start_date = $request->start_date;
        $program->end_date = $request->end_date;
        $program->description = $request->description;
        $program->on_days = $request->on_days;
        $program->form_id = $request->form_id;
        $program->training_form_id = $request->training_form_id;
        $program->question_link = $request->question_link;
        $program->trainer_ratings_link = $request->trainer_ratings_link;
        $program->status = $request->status;

        $program->exam_status = $request->exam_status;
        $program->rating_status = $request->rating_status;

        $program->save();

        $program->modules()->sync($request->circular_modules);
        $program->trainers()->sync($request->trainers);
        $this->syncUserData($request, $program);

        $program->users->map(function ($user) {
            return $user->assignRole('participant');
        });

        return $program;
    }

}
