<?php

namespace App\Jobs;

use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use App\Models\Application;
use App\Models\Beneficiary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldBeUniqueUntilProcessing;

class CreateBeneficiary implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $tries = 3;

    protected int $applicationId;
    protected string $status;
    protected string $verification_number;

    public function __construct(int $applicationId, string $status, string $verification_number)
    {
        $this->applicationId = $applicationId;
        $this->status = $status;
        $this->verification_number = $verification_number;
    }

    public function uniqueId(): string
    {
        return (string) $this->applicationId;
    }

    public function handle(BeneficiaryService $beneficiaryService): void
    {
        $application = Application::find($this->applicationId);

        if (!$application) {
            throw new \Exception("Application not found with ID: {$this->applicationId}");
        }

        $beneficiary_exist = Beneficiary::where('verification_number', $this->verification_number)->first();

        if (empty($beneficiary_exist)) {
            $beneficiaryService->createBeneficiary($application, $this->status);
        }
    }
}
