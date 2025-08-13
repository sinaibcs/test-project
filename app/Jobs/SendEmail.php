<?php

namespace App\Jobs;


use Illuminate\Bus\Queueable;
use App\Mail\BeneficiaryCreateMail;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Mail;

class SendEmail implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
     public $email,$name,$program;

    /**
     * Create a new job instance.
     */
    public function __construct($email,$name,$program)
    {
        $this->email = $email;
        $this->name = $name;
        $this->program = $program;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
         Mail::to($this->email)->send(new BeneficiaryCreateMail($this->email,$this->name,$this->program));
    }
}
