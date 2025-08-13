<?php

namespace App\Http\Resources\Admin\Emergency;

use App\Http\Resources\Admin\Beneficiary\LocationResource;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Models\Bank;
use App\Models\Mfs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EmergencyBeneficiaryResource extends JsonResource
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
            "beneficiary_id" => $this->beneficiary_id,
            "emergency_payroll_detail_id" => $this->emergency_payroll_detail_id,
            "emergency_payroll_id" => $this->emergency_payroll_id,
            "allotment_id" => $this->allotment_id,
            "program_id" => $this->program_id,
            "imageUrl" => $this->image,
            "signUrl" => $this->signature,
            "nomineeImageUrl" => $this->nominee_image,
            "nomineeSignUrl" => $this->nominee_signature,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "spouse_name_en" => $this->spouse_name_en,
            "spouse_name_bn" => $this->spouse_name_bn,
            "identification_mark" => $this->identification_mark,
            "beneficiary_address" => $this->beneficiary_address(),
            "permanentDivision" => LocationResource::make($this->whenLoaded('permanentDivision')),
            "permanentDistrict" => LocationResource::make($this->whenLoaded('permanentDistrict')),
            "permanentWard" => LocationResource::make($this->whenLoaded('permanentWard')),
            "permanentThana" => LocationResource::make($this->whenLoaded('permanentThana')),
            "permanentUnion" => LocationResource::make($this->whenLoaded('permanentUnion')),
            "permanentUpazila" => LocationResource::make($this->whenLoaded('permanentUpazila')),
            "emergency_allotment" => $this->whenLoaded('emergencyAllotment'),
            "program" => AllowanceResource::make($this->whenLoaded('program')),
            "upazilaCityDistPourosova" => $this->upazilaCityDistPourosova(),
            "unionWardPourosova" => $this->unionWardPourosova(),
            "upazilaCityDistPourosova" => $this->upazilaCityDistPourosova(),
            "union" => LocationResource::make($this->whenLoaded('permanentUnion')),
            "ward" => LocationResource::make($this->whenLoaded('permanentWard')),
            "payment_start_date" => $this->payment_start_date,
            "age" => $this->age,
            "gender_id" => $this->gender_id,
            "date_of_birth" => $this->date_of_birth,
            "nationality" => $this->nationality,
            "education_status" => $this->education_status,
            "gender" => LookupResource::make($this->whenLoaded('gender')),
            "profession" => $this->profession,
            "religion" => $this->religion,
            "marital_status" => $this->marital_status,
            "email" => $this->email,
            "mobile" => $this->mobile,
            "verification_type" => $this->verification_type,
            "verification_number" => $this->verification_number,
            "image" => $this->image,
            "signature" => $this->signature,
            "currentDivision" => LocationResource::make($this->whenLoaded('currentDivision')),
            "currentUpazila" => LocationResource::make($this->whenLoaded('currentUpazila')),
            "currentThana" => LocationResource::make($this->whenLoaded('currentThana')),
            "currentDistrict" => LocationResource::make($this->whenLoaded('currentDistrict')),
            "currentWard" => LocationResource::make($this->whenLoaded('currentWard')),
            "currentUnion" => LocationResource::make($this->whenLoaded('currentUnion')),
            "current_division_id" => $this->current_division_id,
            "current_district_id" => $this->current_district_id,
            "current_location_type" => $this->current_location_type,
            "current_location_type_id" => $this->current_location_type_id,
            "current_city_corp_id" => $this->current_city_corp_id,
            "current_district_pourashava_id" => $this->current_district_pourashava_id,
            "current_upazila_id" => $this->current_upazila_id,
            "current_pourashava_id" => $this->current_pourashava_id,
            "current_thana_id" => $this->current_thana_id,
            "current_union_id" => $this->current_union_id,
            "current_ward_id" => $this->current_ward_id,
            "current_location_id" => $this->current_location_id,
            "current_post_code" => $this->current_post_code,
            "current_address" => $this->current_address,
            "permanent_division_id" => $this->permanent_division_id,
            "permanent_district_id" => $this->permanent_district_id,
            "permanent_location_type" => $this->permanent_location_type,
            "permanent_location_type_id" => $this->permanent_location_type_id,
            "permanent_city_corp_id" => $this->permanent_city_corp_id,
            "permanent_district_pourashava_id" => $this->permanent_district_pourashava_id,
            "permanent_upazila_id" => $this->permanent_upazila_id,
            "permanent_pourashava_id" => $this->permanent_pourashava_id,
            "permanent_thana_id" => $this->permanent_thana_id,
            "permanent_union_id" => $this->permanent_union_id,
            "permanent_ward_id" => $this->permanent_ward_id,
            "permanent_post_code" => $this->permanent_post_code,
            "permanent_location_id" => $this->permanent_location_id,
            "permanent_address" => $this->permanent_address,
            "permanent_mobile" => $this->permanent_mobile,
            "nominee_en" => $this->nominee_en,
            "nominee_bn" => $this->nominee_bn,
            "nominee_verification_number" => $this->nominee_verification_number,
            "nominee_address" => $this->nominee_address,
            "nominee_image" => $this->nominee_image,
            "nominee_signature" => $this->nominee_signature,
            "nominee_relation_with_beneficiary" => $this->nominee_relation_with_beneficiary,
            "nominee_nationality" => $this->nominee_nationality,
            "nominee_date_of_birth" => $this->nominee_date_of_birth,
            "account_name" => $this->account_name,
            "account_number" => $this->account_number,
            "account_owner" => $this->account_owner,
            "account_type" => $this->account_type, //1=Bank;2=Mobile
            "bank_id" => $this->bank_id,
            "bank_branch_id" => $this->bank_branch_id,
            "mfs_id" => $this->mfs_id,
            "bank_name" => $this->getBankName($this->bank_id),
            "mfs_name" => $this->getMfsName($this->mfs_id),
            "branch_name" => $this->branch_name,
            "gender_wise_amount" => $this->gender_wise_amount,
            "total_allowance_amount" => $this->total_allowance_amount,
            "status" => $this->status,
            "charge" => $this->charge,
            "amount" => $this->amount,
            "total_amount" => $this->total_amount,
            "monthly_allowance" => $this->monthly_allowance,
            "isExisting" => $this->isExisting,
            "isSelected" => $this->isSelected,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "deleted_at" => $this->deleted_at,
        ];
    }

    private function beneficiary_address()
    {
        $beneficiary_address = $this->permanent_address;
        if ($this->permanentUnion) {
            $beneficiary_address .= ', ' . $this->permanentUnion?->name_en;
        } elseif ($this->permanentPourashava) {
            $beneficiary_address .= ', ' . $this->permanentPourashava?->name_en;
        } elseif ($this->permanentThana) {
            $beneficiary_address .= ', ' . $this->permanentThana?->name_en;
        }

        if ($this->permanentUpazila) {
            $beneficiary_address .= ', ' . $this->permanentUpazila?->name_en;
        } elseif ($this->permanentCityCorporation) {
            $beneficiary_address .= ', ' . $this->permanentCityCorporation?->name_en;
        } elseif ($this->permanentDistrictPourashava) {
            $beneficiary_address .= ', ' . $this->permanentDistrictPourashava?->name_en;
        }

        if ($this->permanentDistrict) {
            $beneficiary_address .= ', ' . $this->permanentDistrict?->name_en;
        }

        return $beneficiary_address;
    }

    /**
     * @return \App\Http\Resources\Admin\Location\LocationResource|null
     */
    public function upazilaCityDistPourosova(): ?LocationResource
    {
        $location = null;
        if ($this->permanentUpazila) {
            $location = LocationResource::make($this->whenLoaded('permanentUpazila'));
        }
        if ($this->permanentCityCorporation) {
            $location = LocationResource::make($this->whenLoaded('permanentCityCorporation'));
        }
        if ($this->permanentDistrictPourashava) {
            $location = LocationResource::make($this->whenLoaded('permanentDistrictPourashava'));
        }
        return $location;
    }

    public function unionWardPourosova(): ?LocationResource
    {
        $location = null;
        if ($this->permanentUnion) {
            $location = LocationResource::make($this->whenLoaded('permanentUnion'));
        }
        if ($this->permanentPourashava) {
            $location = LocationResource::make($this->whenLoaded('permanentPourashava'));
        }
        if ($this->permanentWard) {
            $location = LocationResource::make($this->whenLoaded('permanentWard'));
        }
        return $location;
    }
    public function getBankName($id)
    {
        if ($id != null) {
            return Bank::where('id', $id)->first();
        } else {
            return '';
        }
    }
    public function getMfsName($id)
    {
        if ($id != null) {
            return Mfs::where('id', $id)->first();
        } else {
            return '';
        }
    }
}
