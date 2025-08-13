<?php

namespace App\Jobs;

use App\Models\Beneficiary;
use App\Services\MfsValidationServices\MfsValidationService;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ValidateBeneficiaryMfsAccountNumber implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Create a new job instance.
     */
    public function __construct(private Beneficiary $beneficiary)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(MfsValidationService $mfsValidationService): void
    {
        $mfsAccountVerification = $this->beneficiary->mfsAccountVarification;
        if($mfsAccountVerification == null){
            $mfsAccountVerification = $this->beneficiary->mfsAccountVarification()->create();
        }
        if($this->beneficiary->mfs_id == 2){
            $isValid = $mfsValidationService->validate($this->beneficiary->mfs_id, $this->beneficiary->account_number);
            
            $mfsAccountVerification->mfs_last_verification_attempt_at = now();
            if($isValid){
                $mfsAccountVerification->mfs_last_verified_at = now();
                $mfsAccountVerification->mfs_last_verification_status = 1;
            }else{
                $mfsAccountVerification->mfs_last_verification_status = 0;
            }
            $mfsAccountVerification->save();

        }elseif($this->beneficiary->mfs_id == 1){
            $mfsValidationService->validate($this->beneficiary->mfs_id, $this->beneficiary->account_number, 'Beneficiary');
            $mfsAccountVerification->mfs_last_verification_attempt_at = now();
            $mfsAccountVerification->save();
        }
    }
}
