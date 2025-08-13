<?php

namespace App\Http\Resources\Mobile\Systemconfig\Allowance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Lookup\LookupResource;

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

            'id'                  => $this->id,
            'name_en'             => $this->name_en,
            'name_bn'             => $this->name_bn,
            'payment_cycle'       => $this->payment_cycle,
            'is_marital'          => $this->is_marital,
            'marital_status'      => $this->marital_status,
            'is_active'           => $this->is_active,
            'pmt_status'          => $this->pmt_status,
            'system_status'       => $this->system_status,
            'is_age_limit'        => $this->is_age_limit,
            'is_disable_class'    => $this->is_disable_class,
            'additional_field'    => AdditionalFieldsResource::collection($this->whenLoaded('addtionalfield')),
            'created_at'          => $this->created_at,
            'updated_at'          => $this->updated_at,

        ];
    }
}
