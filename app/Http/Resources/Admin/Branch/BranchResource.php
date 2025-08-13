<?php

namespace App\Http\Resources\Admin\Branch;

use App\Http\Resources\Admin\Employee\EmployeeResource;
use Illuminate\Http\Resources\Json\JsonResource;

class BranchResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array|\Illuminate\Contracts\Support\Arrayable|\JsonSerializable
     */
    public function toArray($request)
    {
        return [
            'id'                    => $this->id,
            'branch_name'                 => $this->branch_name,
            'address'                 => $this->address,
            'open_hour'                 => $this->open_hour,
            'branch_phone'                 => $this->branch_phone,
            'status'                 => $this->status,
            'zone'                  => BranchZoneRrsource::collection($this->whenLoaded('branchZones')),
            'admin'                  => EmployeeResource::make($this->whenLoaded('branchAdmin')),
            'created_at'            => $this->created_at
        ];
    }
}
