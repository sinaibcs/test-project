<?php

namespace App\Http\Services\Mobile\Training;

use App\Helpers\Helper;
use App\Models\TrainingParticipant;
use App\Models\User;

class ParticipantService
{

    public function saveExternalUser($request)
    {
        $user = new User();
        $user->full_name = $request->full_name;
        $user->username = $request->username;
        $user->mobile = $request->mobile;
        $user->email = $request->email;
        $user->user_type = 2;
        $user->salt = Helper::generateSalt();
        $user->password = bcrypt($user->salt . Helper::GeneratePassword());
        $user->save();

        $user->user_id = $user->id;
        $user->save();

        return $user;

    }


    public function saveExternalParticipant($request)
    {
        $participant = new TrainingParticipant(
            [
                'email' => $request->email,
                'full_name' => $request->full_name,
                'training_circular_id' => $request->training_circular_id,
                'training_program_id' => $request->training_program_id,
                'status' => 0,
                'is_by_poll' => 1,
            ]
        );

        $participant->save();

        return $participant;
    }

}
