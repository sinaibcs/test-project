<?php

namespace App\Http\Resources\Admin\Allotment;

use App\Http\Resources\Admin\Location\LocationResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Http\Resources\AllowanceProgramResource;
use App\Http\Services\Admin\BudgetAllotment\AllotmentService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllotmentSummaryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "program_id" => $this->program_id,
            "program" => AllowanceProgramResource::make($this->whenLoaded('program')),
            "financial_year_id" => $this->financial_year_id,
            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
            "total_beneficiaries" => $this->total_beneficiaries,
            "total_amount" => $this->total_amount
        ];
    }
}
