<?php

namespace App\Http\Resources\Admin\Beneficiary;

use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class BeneficiaryIdCardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "program" => AllowanceResource::make($this->whenLoaded('program')),
            "beneficiary_id" => $this->beneficiary_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "beneficiary_address" => $this->permanent_address,
            "identification_mark" => $this->identification_mark,
            "age" => $this->age,
            "date_of_birth" => $this->date_of_birth,
            "nationality" => $this->nationality,
            "gender" => LookupResource::make($this->whenLoaded('gender')),
            "religion" => $this->religion,
            "marital_status" => $this->marital_status,
            "image" => asset('storage/' . $this->image),//Storage::disk('public')->url($this->image),
            "signature" => asset('storage/' . $this->signature),//Storage::disk('public')->url($this->signature),
            "mobile" => $this->mobile,
            "permanentDivision" => LocationResource::make($this->whenLoaded('permanentDivision')),
            "permanentDistrict" => LocationResource::make($this->whenLoaded('permanentDistrict')),
            "permanentUpazila" => LocationResource::make($this->whenLoaded('permanentUpazila')),
            "permanentCityCorporation" => LocationResource::make($this->whenLoaded('permanentCityCorporation')),
            "permanentDistrictPourashava" => LocationResource::make($this->whenLoaded('permanentDistrictPourashava')),
            "permanentThana" => LocationResource::make($this->whenLoaded('permanentThana')),
            "permanentPourashava" => LocationResource::make($this->whenLoaded('permanentPourashava')),
            "permanentUnion" => LocationResource::make($this->whenLoaded('permanentUnion')),
            "permanentWard" => LocationResource::make($this->whenLoaded('permanentWard')),
            "permanent_post_code" => $this->permanent_post_code,
            "permanent_address" => $this->permanent_address,
            "payment_start_date" => $this->payment_start_date,
            "last_payment_date" => $this->last_payment_date,
            "status" => $this->status
        ];
    }
}
