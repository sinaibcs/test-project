<?php

namespace App\Http\Services\Admin\Training;

use App\Helpers\Helper;
use App\Http\Services\Notification\SMSservice;
use App\Http\Traits\RoleTrait;
use App\Mail\UserCreateMail;
use App\Models\TrainingParticipant;
use App\Models\TrainingProgramParticipant;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\ValidationException;

class ParticipantService
{
    use RoleTrait;

    public function storeUser($request)
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


    public function storeParticipant($request, $userId)
    {
        $programExists = TrainingProgramParticipant::where('user_id', $userId)
            ->where('training_program_id', $request->training_program_id)
            ->exists();

        if ($programExists) {
            throw ValidationException::withMessages(['training_program_id' => 'Participant already exists']);
        }

        $participant = new TrainingProgramParticipant(
            [
                'user_id' => $userId,
                'training_circular_id' => $request->training_circular_id,
                'training_program_id' => $request->training_program_id,
                'passcode' => rand(1e7, 1e10)
            ]
        );

        $participant->save();
        $participant->user->assignRole($this->participant);

        return $participant;
    }


    public function sendPasscode($participant)
    {
        $programName = $participant->trainingProgram->program_name;

        $message = "Congratulations! You have been registered as a participant for $programName. Your Employee Id is {$participant->user_id} and Passcode is {$participant->passcode}."
            ."\nPlease keep this information secure and ensure you have it available when you start your exam.\n-MIS, DSS";

//        (new SMSservice())->sendSms($participant->user->mobile, $message);

    }



    public function approveUser($user)
    {
        $password = Helper::GeneratePassword();

        $user->status = 1;
        $user->password = bcrypt($user->salt . $password);
        $user->save();

        $tokenLink = env('APP_FRONTEND_URL') . '/browser-token';

        $message = "Welcome to the CTM application." .
            "\nTo register your device please visit {$tokenLink} then copy the browser fingerprint code and provide it to your authority."
            .
            "\nOnce your device is registered you can access the CTM Application using following credentials:
        \nUsername: " . $user->username
            . "\nPassword: " . $password .
            "\nLogin URL: " . env('APP_FRONTEND_URL') . '/login'
            . "\n-MIS, DSS";


        (new SMSservice())->sendSms($user->mobile, $message);

        //        $this->dispatch(new UserCreateJob($user->email,$user->username, $password));

        Mail::to($user->email)->send(new UserCreateMail($user->email, $user->username, $password, $user->full_name));
    }

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

        $user->assignRole('participant');

        return $user;

    }


    public function saveExternalParticipant($request, $user)
    {
        $participant = new TrainingParticipant(
            [
                'user_id' => $user->id,
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
