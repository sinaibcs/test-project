<?php

namespace App\Jobs;

use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use App\Models\Beneficiary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class UpdateBeneficiaryLocationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $data;

    public $tries = 3;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function handle(BeneficiaryService $beneficiaryService): void
    {
        try{
            $beneficiary = Beneficiary::where('beneficiary_id', $this->data['beneficiary_id'])->first();
            if(!empty($beneficiary)) {
                    $updateData = $this->data;
                    $beneficiaryService->updateLocationFromExcelRow($beneficiary, $updateData);
            }
        } catch (\Exception $e) {

        }
    }
}
