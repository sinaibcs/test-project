<?php

namespace App\Http\Resources\Mobile\Beneficiary;

use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Http\Resources\Admin\Systemconfig\Finanacial\FinancialResource;
use App\Models\Bank;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Storage;

class BeneficiaryResource extends JsonResource
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
            "program_id" => $this->program_id,
            'program' => AllowanceResource::make($this->whenLoaded('program')),
            "beneficiary_id" => $this->beneficiary_id,
            "application_id" => $this->application_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "spouse_name_en" => $this->spouse_name_en,
            "spouse_name_bn" => $this->spouse_name_bn,
            "beneficiary_address" => $this->beneficiary_address(),
            "identification_mark" => $this->identification_mark,
            "age" => $this->age,
            "date_of_birth" => $this->date_of_birth,
            "nationality" => $this->nationality,
            "type_id" => $this->type_id,
            "allowance_class" => LookupResource::make($this->whenLoaded('allowance_class')),
            "nominee_date_of_birth" => $this->nominee_date_of_birth,
            "get_nationality" => LookupResource::make($this->whenLoaded('getNationality')),
            "gender_id"=>$this->gender_id,
            "gender" => LookupResource::make($this->whenLoaded('gender')),
            "education_status" => $this->education_status,
            "get_education" => LookupResource::make($this->whenLoaded('getEducation')),
            "profession" => $this->profession,
            "get_professtion" => LookupResource::make($this->whenLoaded('getProfession')),
            "religion" => $this->religion,
            "get_religion" => LookupResource::make($this->whenLoaded('getReligion')),
            "marital_status" => $this->marital_status,
            "get_maritial_status" => LookupResource::make($this->whenLoaded('getMaritialStatus')),
            "email" => $this->email,
            "verification_type" => $this->verification_type,
            "verification_number" => $this->verification_number,
            "image" => asset('storage/' . $this->image),//Storage::disk('public')->url($this->image),
            "signature" => asset('storage/' . $this->signature),//Storage::disk('public')->url($this->signature),
            "current_division_id" => $this->current_division_id,
            "currentDivision" => LocationResource::make($this->whenLoaded('currentDivision')),
            "current_district_id" => $this->current_district_id,
            "currentDistrict" => LocationResource::make($this->whenLoaded('currentDistrict')),
            "current_upazila_id" => $this->current_upazila_id,
            "currentUpazila" => LocationResource::make($this->whenLoaded('currentUpazila')),
            "current_city_corp_id" => $this->current_city_corp_id,
            "currentCityCorporation" => LocationResource::make($this->whenLoaded('currentCityCorporation')),
            "current_district_pourashava_id" => $this->current_district_pourashava_id,
            "currentDistrictPourashava" => LocationResource::make($this->whenLoaded('currentDistrictPourashava')),
            "current_thana_id" => $this->current_thana_id,
            "currentThana" => LocationResource::make($this->whenLoaded('currentThana')),
            "current_pourashava_id" => $this->current_pourashava_id,
            "currentPourashava" => LocationResource::make($this->whenLoaded('currentPourashava')),
            "current_union_id" => $this->current_union_id,
            "currentUnion" => LocationResource::make($this->whenLoaded('currentUnion')),
            "current_ward_id" => $this->current_ward_id,
            "currentWard" => LocationResource::make($this->whenLoaded('currentWard')),
            "current_post_code" => $this->current_post_code,
            "current_address" => $this->current_address,
            "mobile" => $this->mobile,
            "permanent_division_id" => $this->permanent_division_id,
            "permanent_location_type_id" => $this->permanent_location_type_id,
            "permanentDivision" => LocationResource::make($this->whenLoaded('permanentDivision')),
            "permanent_district_id" => $this->permanent_district_id,
            "permanentDistrict" => LocationResource::make($this->whenLoaded('permanentDistrict')),
            "permanent_upazila_id" => $this->permanent_upazila_id,
            "permanentUpazila" => LocationResource::make($this->whenLoaded('permanentUpazila')),
            "permanent_city_corp_id" => $this->permanent_city_corp_id,
            "permanentCityCorporation" => LocationResource::make($this->whenLoaded('permanentCityCorporation')),
            "permanent_district_pourashava_id" => $this->permanent_district_pourashava_id,
            "permanentDistrictPourashava" => LocationResource::make($this->whenLoaded('permanentDistrictPourashava')),
            "permanent_thana_id" => $this->permanent_thana_id,
            "permanentThana" => LocationResource::make($this->whenLoaded('permanentThana')),
            "permanent_pourashava_id" => $this->permanent_pourashava_id,
            "permanentPourashava" => LocationResource::make($this->whenLoaded('permanentPourashava')),
            "permanent_union_id" => $this->permanent_union_id,
            "permanentUnion" => LocationResource::make($this->whenLoaded('permanentUnion')),
            "permanent_ward_id" => $this->permanent_ward_id,
            "permanentWard" => LocationResource::make($this->whenLoaded('permanentWard')),
            "permanent_post_code" => $this->permanent_post_code,
            "permanent_address" => $this->permanent_address,
            "permanent_mobile" => $this->permanent_mobile,
            "union_or_pourashava" => ($this->permanentUnion?->name_en ?: $this->permanentPourashava?->name_en),
            "nominee_en" => $this->nominee_en,
            "nominee_bn" => $this->nominee_bn,
            "nominee_verification_number" => $this->nominee_verification_number,
            "nominee_address" => $this->nominee_address,
            "nominee_image" => asset('storage/' . $this->nominee_image),//Storage::disk('public')->url($this->nominee_image),
            "nominee_signature" => asset('storage/' . $this->nominee_signature),//Storage::disk('public')->url($this->nominee_signature),
            "nominee_relation_with_beneficiary" => $this->nominee_relation_with_beneficiary,
            "nominee_nationality" => $this->nominee_nationality,
            "get_Nominee_nationality" => LookupResource::make($this->whenLoaded('getNomineeNationality')),
            "account_name" => $this->account_name,
            "account_number" => $this->account_number,
            "account_owner" => $this->account_owner,
            "financial_year_id" => $this->financial_year_id,
            "financialYear" => FinancialResource::make($this->whenLoaded('financialYear')),
            "account_type" => $this->account_type,
            "bank_id" => $this->bank_id,
            "bank" =>$this->whenLoaded('bank'),
        
            "bank_name" => $this->bank_name,
            "mfs_id" => $this->mfs_id,
            "mfs" =>$this->whenLoaded('mfs'),
            "mfs_name" => $this->mfs_name,
            "bank_branch_id" => $this->bank_branch_id,
            "branch" =>$this->whenLoaded('branch'),
            "monthly_allowance" => $this->monthly_allowance,
            "status" => $this->status,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "score" => $this->score,
            "delete_cause" => $this->delete_cause,
            "deleted_at" => $this->deleted_at,
            "application_date" => $this->application_date,
            "approve_date" => $this->approve_date
        ];
    }

    private function beneficiary_address()
    {
        $beneficiary_address = $this->permanent_address;
        if ($this->permanentUnion)
            $beneficiary_address .= ', ' . $this->permanentUnion?->name_en;
        elseif ($this->permanentPourashava)
            $beneficiary_address .= ', ' . $this->permanentPourashava?->name_en;
        elseif ($this->permanentThana)
            $beneficiary_address .= ', ' . $this->permanentThana?->name_en;

        if ($this->permanentUpazila)
            $beneficiary_address .= ', ' . $this->permanentUpazila?->name_en;
        elseif ($this->permanentCityCorporation)
            $beneficiary_address .= ', ' . $this->permanentCityCorporation?->name_en;
        elseif ($this->permanentDistrictPourashava)
            $beneficiary_address .= ', ' . $this->permanentDistrictPourashava?->name_en;

        if ($this->permanentDistrict)
            $beneficiary_address .= ', ' . $this->permanentDistrict?->name_en;

        return $beneficiary_address;
    }
}