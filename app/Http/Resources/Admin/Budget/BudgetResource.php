<?php

namespace App\Http\Resources\Admin\Budget;

use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Http\Resources\AllowanceProgramResource;
use App\Http\Resources\Mobile\Systemconfig\Allowance\AllowanceResource;
use App\Models\FinancialYear;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'budget_id' => $this->budget_id,
            'program' => AllowanceResource::make($this->whenLoaded('program')),
            'financialYear' => FinancialResource::make($this->whenLoaded('financialYear')),
            'calculationType' => LookupResource::make($this->whenLoaded('calculationType')),
            'calculation_value' => $this->calculation_value,
            'remarks' => $this->remarks,
            'deleted_at' => $this->deleted_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_approved' => $this->is_approved,
            'approval_status' => $this->approval_status,
            'approved_at' => $this->approved_at,
            'approved_document' => $this->approved_document,
            'approved_remarks' => $this->approved_remarks,
            'process_flag' => $this->process_flag,
            'allotment_create_flag' => $this->allotment_create_flag,
            'created_by_id' => $this->created_by_id,
            'updated_by_id' => $this->updated_by_id,
            'approved_by_id' => $this->approved_by_id,
            'prev_financial_years' => $this->prev_financial_years()
        ];
    }

    private function prev_financial_years()
    {
        $financial_years = FinancialYear::query()->whereIn('id', explode(',', $this->prev_financial_year_ids))->pluck('financial_year')->toArray();
        return \Arr::join($financial_years, ', ');
    }
}
