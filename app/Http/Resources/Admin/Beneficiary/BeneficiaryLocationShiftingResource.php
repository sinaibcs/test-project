<?php

namespace App\Http\Resources\Admin\Beneficiary;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryLocationShiftingResource extends JsonResource
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
            'program_name_en' => $this->program_name_en,
            'program_name_bn' => $this->program_name_bn,
            "beneficiary_id" => $this->beneficiary_id,
            "beneficiary_id_system" => $this->beneficiary_id_system,
            "application_id" => $this->application_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "from_division_name_en" => $this->from_division_name_en,
            "from_division_name_bn" => $this->from_division_name_bn,
            "from_district_name_en" => $this->from_district_name_en,
            "from_district_name_bn" => $this->from_district_name_bn,
            "from_city_corporation_name_en" => $this->from_city_corporation_name_en,
            "from_city_corporation_name_bn" => $this->from_city_corporation_name_bn,
            "from_district_pourashava_name_en" => $this->from_district_pourashava_name_en,
            "from_district_pourashava_name_bn" => $this->from_district_pourashava_name_bn,
            "from_upazila_name_en" => $this->from_upazila_name_en,
            "from_upazila_name_bn" => $this->from_upazila_name_bn,
            "from_pourashava_name_en" => $this->from_pourashava_name_en,
            "from_pourashava_name_bn" => $this->from_pourashava_name_bn,
            "from_thana_en" => $this->from_thana_en,
            "from_thana_bn" => $this->from_thana_bn,
            "from_union_en" => $this->from_union_en,
            "from_union_bn" => $this->from_union_bn,
            "from_ward_en" => $this->from_ward_en,
            "from_ward_bn" => $this->from_ward_bn,
            "to_division_name_en" => $this->to_division_name_en,
            "to_division_name_bn" => $this->to_division_name_bn,
            "to_district_name_en" => $this->to_district_name_en,
            "to_district_name_bn" => $this->to_district_name_bn,
            "to_city_corporation_name_en" => $this->to_city_corporation_name_en,
            "to_city_corporation_name_bn" => $this->to_city_corporation_name_bn,
            "to_district_pourashava_name_en" => $this->to_district_pourashava_name_en,
            "to_district_pourashava_name_bn" => $this->to_district_pourashava_name_bn,
            "to_upazila_name_en" => $this->to_upazila_name_en,
            "to_upazila_name_bn" => $this->to_upazila_name_bn,
            "to_pourashava_name_en" => $this->to_pourashava_name_en,
            "to_pourashava_name_bn" => $this->to_pourashava_name_bn,
            "to_thana_en" => $this->to_thana_en,
            "to_thana_bn" => $this->to_thana_bn,
            "to_union_en" => $this->to_union_en,
            "to_union_bn" => $this->to_union_bn,
            "to_ward_en" => $this->to_ward_en,
            "to_ward_bn" => $this->to_ward_bn,
            "shifting_cause" => $this->shifting_cause,
            "effective_date" => Carbon::parse($this->effective_date)->format('d/m/Y'),
        ];
    }
}
