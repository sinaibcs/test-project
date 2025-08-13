<?php

namespace App\Http\Resources\Mobile\Beneficiary\Committee;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Office\OfficeResource;
use App\Http\Resources\Admin\Geographic\DistrictResource;
use App\Http\Resources\Admin\Geographic\DivisionResource;

use App\Http\Resources\Admin\Beneficiary\Committee\MemberResource;
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
            'location' => $this->location,
            'members' => MemberResource::collection($this->whenLoaded('members'))
        ];
    }
}
