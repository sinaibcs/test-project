<?php

namespace App\Http\Resources\Admin\Allotment;

use App\Http\Resources\Admin\Location\LocationResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Http\Resources\AllowanceProgramResource;
use App\Http\Services\Admin\BudgetAllotment\AllotmentService;
use App\Http\Services\Admin\BudgetAllotment\BudgetService;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllotmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "program" => AllowanceProgramResource::make($this->whenLoaded('program')),
            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
            "division" => LocationResource::make($this->whenLoaded('division')),
            "district" => LocationResource::make($this->whenLoaded('district')),
            "office_area" => $this->officeArea(),
            "allotment_area" => LocationResource::make($this->whenLoaded('location')),
            "office" => $this->office,
            "regular_beneficiaries" => $this->regular_beneficiaries,
            "additional_beneficiaries" => $this->additional_beneficiaries,
            "total_beneficiaries" => $this->total_beneficiaries,
            "per_beneficiary_amount" => $this->getProgramAllowanceAmount($this),//$this->per_beneficiary_amount,
            "total_amount" => $this->total_amount,
            "type" => $this->type
        ];
    }

    /**
     * @return LocationResource|null
     */
    public function officeArea()
    {
        $office_area = null;
        if ($this->upazila)
            $office_area = LocationResource::make($this->whenLoaded('upazila'));
        if ($this->cityCorporation)
            $office_area = LocationResource::make($this->whenLoaded('cityCorporation'));
        if ($this->districtPourosova)
            $office_area = LocationResource::make($this->whenLoaded('districtPourosova'));
        return $office_area;
    }

    private function getProgramAllowanceAmount($allotment){
        $allotmentService = new AllotmentService(new BudgetService);
        return $allotmentService->getProgramAllowanceAmount($allotment);
    }
}
