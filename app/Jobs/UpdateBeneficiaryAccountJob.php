<?php

namespace App\Jobs;

use App\Http\Services\Admin\Beneficiary\BeneficiaryService;
use App\Models\Beneficiary;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Log;

class UpdateBeneficiaryAccountJob implements ShouldQueue
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
//                $existing = Beneficiary::where('account_number', $this->data['account_number'])
//                    ->where('beneficiary_id', '!=', $this->data['beneficiary_id'])
//                    ->first();
//                if (empty($existing)) {
                    $updateData = $this->data;

                    $accountType = (int)$this->data['account_type'];
                    if ($accountType === 1) {
                        $updateData['mfs_id'] = null;
                    } elseif ($accountType === 2) {
                        $updateData['bank_id'] = null;
                        $updateData['bank_branch_id'] = null;

                        if (!in_array(strlen($updateData['account_number']), [11, 12])) {
                            $updateData['mfs_id'] = null;
                        }
                    }
                    $beneficiaryService->updateAccountFromExcelRow($beneficiary, $updateData);
//                }
            }
        } catch (\Exception $e) {
            Log::info($e->getMessage());
        }
    }
}
