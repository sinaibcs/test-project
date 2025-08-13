<?php

namespace App\Http\Resources\Admin\Emergency;

use App\Http\Resources\Admin\Location\LocationResource;
use App\Http\Resources\Admin\Office\OfficeResource;
use App\Http\Resources\Admin\Payroll\PayrollInstallmentScheduleResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Http\Resources\AllowanceProgramResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyPayrollApprovalResource extends JsonResource
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
            "installmentSchedule" => PayrollInstallmentScheduleResource::make($this->whenLoaded('installmentSchedule')),
            "division" => LocationResource::make($this->division),
            "district" => LocationResource::make($this->district),
            "office_area" => OfficeResource::make($this->whenLoaded('office')),
            "upazilaCityDistrict" => $this->officeArea(),
            "allotment_area" => LocationResource::make($this->location),
            "total_beneficiaries" => $this->total_beneficiaries,
            "sub_total_amount" => $this->sub_total_amount,
            "total_charge" => $this->total_charge,
            "total_amount" => $this->total_amount,
            "is_submitted" => $this->is_submitted,
            "is_verification_required" => $this->is_verification_required,
            "is_verified" => $this->is_verified,
            "is_approved" => $this->is_approved,
            "is_rejected" => $this->is_rejected,
            "approve_count" => $this->approve_count,
            "waiting_count" => $this->waiting_count,
            "rollback_count" => $this->rollback_count,
        ];
    }

    public function officeArea(): ?LocationResource
    {
        $office_area = null;
        if ($this->upazila)
            $office_area = LocationResource::make($this->upazila);
        if ($this->cityCorporation)
            $office_area = LocationResource::make($this->cityCorporation);
        if ($this->districtPourosova)
            $office_area = LocationResource::make($this->districtPourosova);
        return $office_area;
    }
}
