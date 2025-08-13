<?php

namespace App\Http\Resources\Admin\Beneficiary;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryReplaceResource extends JsonResource
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
            'main_program_name_en' => $this->main_program_name_en,
            'main_program_name_bn' => $this->main_program_name_bn,
            'program_name_en' => $this->program_name_en,
            'program_name_bn' => $this->program_name_bn,
            "beneficiary_id" => $this->beneficiary_id,
            "application_id" => $this->application_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn,
            "mother_name_en" => $this->mother_name_en,
            "mother_name_bn" => $this->mother_name_bn,
            "father_name_en" => $this->father_name_en,
            "father_name_bn" => $this->father_name_bn,
            "district_name_en" => $this->district_name_en,
            "district_name_bn" => $this->district_name_bn,
            "replace_with_beneficiary_id" => $this->replace_with_beneficiary_id,
            "replace_with_name_en" => $this->replace_with_name_en,
            "replace_with_name_bn" => $this->replace_with_name_bn,
            "replace_with_mother_name_en" => $this->replace_with_mother_name_en,
            "replace_with_mother_name_bn" => $this->replace_with_mother_name_bn,
            "replace_with_father_name_en" => $this->replace_with_father_name_en,
            "replace_with_father_name_bn" => $this->replace_with_father_name_bn,
            "replace_with_district_name_en" => $this->replace_with_district_name_en,
            "replace_with_district_name_bn" => $this->replace_with_district_name_bn,
            "replace_with_union_pouroshava_thana_name_en" => $this->replace_with_union_name_en ?: $this->replace_with_pourashava_name_en ?: $this->replace_with_thana_name_en,
            "replace_with_union_pouroshava_thana_name_bn" => $this->replace_with_union_name_bn ?: $this->replace_with_pourashava_name_bn ?: $this->replace_with_thana_name_bn,
            "replace_with_ward_name_en" => $this->replace_with_ward_name_en,
            "replace_with_ward_name_bn" => $this->replace_with_ward_name_bn,
            "replace_with_address" => $this->replace_with_address,
            "replace_cause_en" => $this->replace_cause_en,
            "replace_cause_bn" => $this->replace_cause_bn,
            "cause_detail" => $this->cause_detail,
            "cause_date" => Carbon::parse($this->cause_date)->format('d/m/Y'),
        ];
    }
}
