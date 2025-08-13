<?php

namespace App\Jobs;

use App\Http\Traits\MessageTrait;
use App\Mail\EmployeeToBranchAdminAssignMail;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Mail;

class EmployeeToBranchAdminAssignJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels,MessageTrait;

    public $EmployeeEmail,$EmployeeName,$BranchName,$GlobalSettings;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($EmployeeEmail,$EmployeeName,$BranchName,$GlobalSettings)
    {
        $this->EmployeeName = $EmployeeName;
        $this->EmployeeEmail = $EmployeeEmail;
        $this->BranchName = $BranchName;
        $this->GlobalSettings = $GlobalSettings;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        Mail::to($this->EmployeeEmail)->send(new EmployeeToBranchAdminAssignMail($this->EmployeeEmail,$this->EmployeeName,$this->BranchName,$this->GlobalSettings));
    }
}
