<?php

namespace App\Http\Resources\Admin\Beneficiary;

use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class BeneficiaryPaymentHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'financial_year' => $this->financial_year,
            'installment_name_en' => $this->installment_name,
            'installment_name_bn' => $this->installment_name_bn,
            'transaction_id' => $this->transaction_id,
            'payment_disbursed_at' => Carbon::parse($this->payment_disbursed_at)->format('d/m/Y h:i:s A'),
            'amount' => $this->amount,
            'total_amount' => $this->total_amount,
            'account_number' => $this->account_number,
            'bank_name' => $this->bank_name,
            'mfs_name' => $this->mfs_name,
         ];
    }
}
