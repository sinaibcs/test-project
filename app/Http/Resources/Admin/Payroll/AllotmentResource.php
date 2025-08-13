<?php

namespace App\Http\Resources\Admin\Payroll;

use App\Http\Resources\Admin\Location\LocationResource;
use App\Models\Payroll;
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
//            "program" => AllowanceProgramResource::make($this->whenLoaded('program')),
//            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
//            "division" => LocationResource::make($this->whenLoaded('division')),
//            "district" => LocationResource::make($this->whenLoaded('district')),
            "office_area" => $this->officeArea(),
            "office" => $this->office,
            "type" => $this->type,
            "allotment_area" => LocationResource::make($this->whenLoaded('location')),
            "allotted_beneficiaries" => $this->total_beneficiaries,
            "active_beneficiaries" => $this->active_beneficiaries,
            "saved_beneficiaries" => $this->saved_beneficiaries,
            "total_sent" => $this->total_sent,
            "total_approved" => $this->total_approved,
            "total_rollback" => $this->total_rollback,
            "status" => $this->status($this->allotment_id, $this->installment_id) ? "Saved" : "Not Saved",
        ];
    }

    /**
     * @return LocationResource|null
     */
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
        return Payroll::where('allotment_id', $allotment_id)
            ->where('payrolls.installment_schedule_id', $installment_id)->exists();

    }
}
