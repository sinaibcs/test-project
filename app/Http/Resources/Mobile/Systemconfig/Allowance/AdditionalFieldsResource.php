<?php

namespace App\Http\Resources\Mobile\Systemconfig\Allowance;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AdditionalFieldsResource extends JsonResource
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
            'type'                => $this->type,
            'verified'            => $this->verified,

            'additional_field_value'              => $this->whenLoaded('additional_field_value'),
            'created_at'              => $this->created_at,
            'updated_at'              => $this->updated_at,

        ];
    }
}
