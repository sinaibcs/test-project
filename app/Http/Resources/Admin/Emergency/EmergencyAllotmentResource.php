<?php

namespace App\Http\Resources\Admin\Emergency;

use App\Http\Resources\Admin\Location\LocationResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Http\Resources\AllowanceProgramResource;
use App\Models\EmergencyPayroll;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyAllotmentResource extends JsonResource
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
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "no_of_existing_beneficiary" => $this->no_of_existing_benificiariy,
            "no_of_new_beneficiary" => $this->no_of_new_benificiariy,
            "amount_per_person" => $this->amount_per_person,
            "total_beneficiaries" => $this->total_beneficiaries,
            "total_amount" => $this->total_amount,
            "starting_period" => $this->starting_period,
            "closing_period" => $this->closing_period,
            "payment_cycle" => $this->payment_cycle,
            "programs" => AllowanceProgramResource::make($this->whenLoaded('programs')),
            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
            "division" => LocationResource::make($this->whenLoaded('division')),
            "district" => LocationResource::make($this->whenLoaded('district')),
            'division_id' => $this->division_id,
            'district_id' => $this->district_id,
            'location_type' => $this->location_type,
            'sub_location_type' => $this->sub_location_type,
            'city_corp_id' => $this->city_corp_id,
            'district_pourashava_id' => $this->district_pourashava_id,
            'upazila_id' => $this->upazila_id,
            'pourashava_id' => $this->pourashava_id,
            'thana_id' => $this->thana_id,
            'union_id' => $this->union_id,
            'ward_id' => $this->ward_id,
            'location_id' => $this->location_id,
            "office_area" => $this->officeArea(),
            "allotment_area" => LocationResource::make($this->whenLoaded('location')),
            "allotted_beneficiaries" => $this->no_of_existing_benificiariy + $this->no_of_new_benificiariy,
            "active_beneficiaries" => $this->active_beneficiaries,
            "saved_beneficiaries" => $this->saved_beneficiaries,
            "status" => $this->status($this->emergency_allotment_id, $this->installment_id) ? "Saved" : "Not Saved",
        ];
    }

    public function officeArea(): ?LocationResource
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

    public function status($allotment_id, $installment_id)
    {
        return EmergencyPayroll::where('emergency_allotment_id', $allotment_id)
            ->where('installment_schedule_id', $installment_id)->exists();
    }
}
