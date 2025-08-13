<?php

namespace App\Http\Resources\Admin\Beneficiary;

use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class BeneficiaryGrivanceHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'grievance_type' => $this->grievanceType,
            'grievance_subject' => $this->grievanceSubject,
            'grievance_details' => $this->details,
            'tracking_no' => $this->tracking_no,
            'grievance_date' => $this->created_at,
            'resolve_by' => $this?->resolveDetail?->role?->name,
            'resolve_date' => $this?->resolveDetail?->created_at,
            'resolve_comment' => $this?->resolveDetail?->remarks,
        ];
    }
}