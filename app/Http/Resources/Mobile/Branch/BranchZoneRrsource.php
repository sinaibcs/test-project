<?php

namespace App\Http\Resources\Mobile\Branch;

use Illuminate\Http\Resources\Json\JsonResource;

class BranchZoneRrsource extends JsonResource
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
            'id'                    => $this->zone->id,
            'name'                 => $this->zone->name,
            'home_delivery'                 => $this->zone->home_delivery,
            'charge_one_kg'                 => $this->zone->charge_one_kg,
            'charge_two_kg'                 => $this->zone->charge_two_kg,
            'charge_three_kg'                 => $this->zone->charge_three_kg,
            'charge_extra_per_kg'                 => $this->zone->charge_extra_per_kg,
            'cod_charge'                 => $this->zone->cod_charge,
            'status'                 => $this->zone->status,
            'created_at'            => $this->zone->created_at
        ];
    }
}
