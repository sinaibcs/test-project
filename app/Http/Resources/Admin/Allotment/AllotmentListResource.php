<?php

namespace App\Http\Resources\Admin\Allotment;

use App\Http\Resources\Admin\Location\LocationResource;
use App\Http\Resources\Admin\Lookup\LookupResource;
//use App\Http\Resources\Admin\Office\OfficeResource;
//use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
//use App\Http\Resources\AllowanceProgramResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllotmentListResource extends JsonResource
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
//            "program" => AllowanceProgramResource::make($this->whenLoaded('program')),
//            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "location_type" => LookupResource::make($this->whenLoaded('locationType')),
            "parent_location" => LocationResource::make($this->whenLoaded('parent')),
//            "office" => OfficeResource::make($this->whenLoaded('office')),
            "regular_beneficiaries" => $this->regular_beneficiaries,
            "additional_beneficiaries" => $this->additional_beneficiaries,
            "total_beneficiaries" => $this->total_beneficiaries,
//            "per_beneficiary_amount" => $this->getProgramAllowanceAmount($this->program_id),//$this->per_beneficiary_amount,
            "total_amount" => $this->total_amount
        ];
    }
}
