<?php

namespace App\Jobs;

use App\Mail\EmployeeWelcomeMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;

class EmployeeWelcomeJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;
    public $EmployeeEmail,$EmployeeName,$EmployeePassword,$GlobalSettings;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($EmployeeEmail,$EmployeeName,$EmployeePassword,$GlobalSettings)
    {
        $this->EmployeeEmail = $EmployeeEmail;
        $this->EmployeeName = $EmployeeName;
        $this->EmployeePassword = $EmployeePassword;
        $this->GlobalSettings = $GlobalSettings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        Mail::to($this->EmployeeEmail)->send(new EmployeeWelcomeMail($this->EmployeeEmail,$this->EmployeeName,$this->EmployeePassword,$this->GlobalSettings));
    }
}
