<?php

namespace App\Http\Services\Admin\Training;

use App\Helpers\Helper;
use App\Http\Services\Notification\SMSservice;
use App\Http\Traits\RoleTrait;
use App\Mail\UserCreateMail;
use App\Models\Trainer;
use App\Models\TrainingParticipant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;

class TrainerService
{
    use RoleTrait;

    /**
     * @param Request $request
     * @return User
     */
    public function storeUser($request)
    {
        $user = new User();
        $user->full_name = $request->name;
        $user->username = $request->username;
        $user->mobile = $request->mobile_no;
        $user->email = $request->email;
        $user->user_type = 2;
        $user->salt = Helper::generateSalt();
        $user->password = bcrypt($user->salt . Helper::GeneratePassword());

        $user->save();

        $user->user_id = $user->id;
        $user->save();

        $user->assignRole($this->trainer);

        return $user;
    }


    /**
     * @param Request $request
     * @param $userId
     * @return Trainer
     */
    public function storeTrainer($request, $userId)
    {
        $trainer = new Trainer();
        $trainer->user_id = $userId;
        $trainer->name = $request->name;
        $trainer->designation_id = $request->designation_id;
        $trainer->mobile_no = $request->mobile_no;
        $trainer->email = $request->email;
        $trainer->address = $request->address;
        $trainer->description = $request->description;

        if ($request->hasFile('image')) {
            $trainer->image = $request->file('image')->store('public');
        }

        if ($request->hasFile('signature')) {
            $trainer->signature = $request->file('signature')->store('public');
        }

        $trainer->is_external = $request->is_external == 1;

        $trainer->save();

        $trainer->user->assignRole($this->trainer);

        return $trainer;
    }



    public function updateTrainer($request, $trainer)
    {
        $trainer->name = $request->name;
        $trainer->designation_id = $request->designation_id;
        $trainer->mobile_no = $request->mobile_no;
        $trainer->email = $request->email;
        $trainer->address = $request->address;
        $trainer->description = $request->description;

        if ($request->hasFile('image')) {
            if ($trainer->image) {
                Storage::delete($trainer->image);
            }
            $trainer->image = $request->file('image')->store('public');
        }

        if ($request->hasFile('signature')) {
            if ($trainer->signature) {
                Storage::delete($trainer->signature);
            }
            $trainer->signature = $request->file('signature')->store('public');
        }

        $trainer->save();

        return $trainer;
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







}
