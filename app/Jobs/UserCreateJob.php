<?php

namespace App\Jobs;

use App\Mail\UserCreateMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class UserCreateJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $email,$name,$password,$fullName;

    /**
     * Create a new job instance.
     */
    public function __construct($email,$name,$password,$fullName)
    {
        $this->email = $email;
        $this->name = $name;
        $this->password = $password;
        $this->fullName = $fullName;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Mail::to($this->email)->send(new UserCreateMail($this->email,$this->name,$this->password,$this->fullName));
    }
}
