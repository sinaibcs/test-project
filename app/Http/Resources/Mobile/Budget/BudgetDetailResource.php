<?php

namespace App\Http\Resources\Mobile\Budget;

use App\Http\Resources\Admin\Location\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetDetailResource extends JsonResource
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
//            "budget_id" => $this->budget_id,
//            "division_id" => $this->division_id,
//            "district_id" => $this->district_id,
//            "location_type" => $this->location_type,
            "location_id" => $this->location_id,
            "total_beneficiaries" => $this->total_beneficiaries,
            "per_beneficiary_amount" => $this->per_beneficiary_amount,
            "total_amount" => $this->total_amount,
            "office_area" => $this->officeArea(),
            "allotment_area" => LocationResource::make($this->whenLoaded('location')),
        ];

    }

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
}
