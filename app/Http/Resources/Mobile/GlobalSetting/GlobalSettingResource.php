<?php

namespace App\Http\Resources\Mobile\GlobalSetting;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Lookup\LookupResource;

class GlobalSettingResource extends JsonResource
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
            'value'                => $this->value,
            'area_type'  => LookupResource::make($this->whenLoaded('areaType')),
            'default'                => $this->default,
        ];
    }
}
