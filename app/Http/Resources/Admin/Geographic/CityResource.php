<?php

namespace App\Http\Resources\Admin\Geographic;

use App\Http\Resources\Admin\Lookup\LookupResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CityResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'name_en'              => $this->name_en,
            'name_bn'              => $this->name_bn,
            'code'                 => $this->code,
            'type'                 => $this->type,
            // 'district'  => DistrictResource::make($this->whenLoaded('parent')),
            'parent'  => DistrictResource::make($this->whenLoaded('parent')),
            'locationType'  => LookupResource::make($this->whenLoaded('locationType')),
        ];
    }
}
