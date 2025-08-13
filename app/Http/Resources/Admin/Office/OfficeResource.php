<?php

namespace App\Http\Resources\Admin\Office;

use App\Http\Resources\Admin\Location\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Lookup\LookupResource;

class OfficeResource extends JsonResource
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
            'assignLocation' => LocationResource::make($this->whenLoaded('assignLocation')),
            'officeType' => LookupResource::make($this->whenLoaded('officeType')),
            'name_en' => $this->name_en,
            'name_bn' => $this->name_bn,
            'office_type' => $this->office_type,
            'office_address' => $this->office_address,
            'comment' => $this->comment,
            'status' => $this->status,
        ];
    }
}
