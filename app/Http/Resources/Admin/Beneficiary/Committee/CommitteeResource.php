<?php

namespace App\Http\Resources\Admin\Beneficiary\Committee;

use App\Http\Resources\Admin\Location\LocationResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Office\OfficeResource;
use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;

class CommitteeResource extends JsonResource
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
            'code' => $this->code,
            'name' => $this->name,
            'details' => $this->details,
            'program' => AllowanceResource::make($this->whenLoaded('program')),
            'committeeType' => LookupResource::make($this->whenLoaded('committeeType')),
            'officeType' => LookupResource::make($this->whenLoaded('officeType')),
            'office' => OfficeResource::make($this->whenLoaded('office')),
            'division' => LocationResource::make($this->whenLoaded('division')),
            'district' => LocationResource::make($this->whenLoaded('district')),
            'locationType' => LookupResource::make($this->whenLoaded('locationType')),
            'cityCorp' => LocationResource::make($this->whenLoaded('cityCorp')),
            'districtPourashava' => LocationResource::make($this->whenLoaded('districtPourashava')),
            'upazila' => LocationResource::make($this->whenLoaded('upazila')),
            'sub_location_type_id' => $this->sub_location_type_id,
            'pourashava' => LocationResource::make($this->whenLoaded('pourashava')),
            'thana' => LocationResource::make($this->whenLoaded('thana')),
            'union' => LocationResource::make($this->whenLoaded('union')),
            'ward' => LocationResource::make($this->whenLoaded('ward')),
            'location' => LocationResource::make($this->whenLoaded('location')),
            'members' => MemberResource::collection($this->whenLoaded('members')),
        ];
    }
}
