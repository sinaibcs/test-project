<?php

namespace App\Http\Resources\Admin\Systemconfig\Allowance;

use App\Http\Resources\Admin\Lookup\LookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AllowanceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,
            'name_en' => $this->name_en,
            'name_bn' => $this->name_bn,
            'payment_cycle' => $this->payment_cycle,
            'is_marital' => $this->is_marital,
            'marital_status' => array_map('intval', array_filter(explode(",", $this->marital_status??''), fn($value) => $value !== '' && $value != null)),
            'is_active' => $this->is_active,
            'has_children' => $this->has_children,
            'sub_programs' => $this->subPrograms,
            'is_nominee_optional' => $this->is_nominee_optional,
            'pmt_status' => $this->pmt_status,
            'system_status' => $this->system_status,
            'genders' => array_map('intval', array_filter(explode(",", $this->gender??''), fn($value) => $value !== '')),
            'is_age_limit' => $this->is_age_limit,
            'is_disable_class' => $this->is_disable_class,
            'dead_option_enable_disable' => $this->dead_option_enable_disable,
            'additional_field' => AdditionalFieldsResource::collection($this->whenLoaded('addtionalfield')),
            'lookup' => LookupResource::collection($this->classAmounts),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'is_bank_mfs_mandatory' => $this->is_bank_mfs_mandatory,
            'is_nid_id_registration_enabled' => $this->is_nid_id_registration_enabled,
            'is_birth_id_registration_enabled' => $this->is_birth_id_registration_enabled,
            'sub_programs' =>  AllowanceResource::collection($this->whenLoaded('subPrograms')),

        ];
    }

    public function getLookup(LookupResource $resource)
    {

        return $resource;

    }
}
