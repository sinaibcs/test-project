<?php

namespace App\Http\Resources\Admin\Emergency;

use App\Http\Resources\Admin\Beneficiary\LocationResource;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use App\Models\Bank;
use App\Models\Mfs;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ActiveBeneficiaryResource extends JsonResource
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
            "program_id" => $this->program_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "spouse_name_en" => $this->spouse_name_en,
            "spouse_name_bn" => $this->spouse_name_bn,
            "beneficiary_address" => $this->beneficiary_address(),
            "permanentDistrict" => LocationResource::make($this->whenLoaded('permanentDistrict')),
            "program" => AllowanceResource::make($this->whenLoaded('program')),
            "upazilaCityDistPourosova" => $this->upazilaCityDistPourosova(),
            "union" => LocationResource::make($this->whenLoaded('permanentUnion')),
            "unionWardPourosova" => $this->unionWardPourosova(),
            "ward" => LocationResource::make($this->whenLoaded('permanentWard')),
            "payment_start_date" => $this->payment_start_date,
            "age" => $this->age,
            "date_of_birth" => $this->date_of_birth,
            "nationality" => $this->nationality,
            "gender" => LookupResource::make($this->whenLoaded('gender')),
            "profession" => $this->profession,
            "religion" => $this->religion,
            "marital_status" => $this->marital_status,
            "email" => $this->email,
            "mobile" => $this->mobile,
            "account_name" => $this->account_name,
            "account_number" => $this->account_number,
            "account_owner" => $this->account_owner,
            "account_type" => $this->account_type, //1=Bank;2=Mobile
            "bank_name" => $this->getBankName($this->bank_id),
            "mfs_name" => $this->getMfsName($this->mfs_id),
            "branch_name" => $this->branch_name,
            "total_allowance_amount" => $this->total_allowance_amount,
            "status" => $this->status,
            "isRejected" => $this->isRejected,
            "charge" => $this->charge,
            "amount" => $this->amount_per_person,
            "isAccountChanged" => $this->change_status,
            "isNew" => $this->isNew,
            "isRegular" => $this->isRegular,
            "isReplaced" => $this->is_replaced,
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

    /**
     * @return \App\Http\Resources\Admin\Location\LocationResource|null
     */
    public function upazilaCityDistPourosova(): ?LocationResource
    {
        $location = null;
        if ($this->permanentUpazila)
            $location = LocationResource::make($this->whenLoaded('permanentUpazila'));
        if ($this->permanentCityCorporation)
            $location = LocationResource::make($this->whenLoaded('permanentCityCorporation'));
        if ($this->permanentDistrictPourashava)
            $location = LocationResource::make($this->whenLoaded('permanentDistrictPourashava'));
        return $location;
    }

    public function unionWardPourosova(): ?LocationResource
    {
        $location = null;
        if ($this->permanentUnion)
            $location = LocationResource::make($this->whenLoaded('permanentUnion'));
        if ($this->permanentPourashava)
            $location = LocationResource::make($this->whenLoaded('permanentPourashava'));
        if ($this->permanentWard)
            $location = LocationResource::make($this->whenLoaded('permanentWard'));
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
