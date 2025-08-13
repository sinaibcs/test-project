<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class CauserResource extends JsonResource
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
            'Id'                    => $this->id,
            'User Name'                 => $this->username,
            'Full Name'                 => $this->full_name,
            'User Type'                  => $this->user_type,
            'Email'                  => $this->email,
            'Mobile'                  => $this->mobile,
            'Office Name'            => $this->office->name_en ?? '',
            'Office Address'            => $this->office->office_address ?? '',
            'Office Id'                  => $this->office_id,
            'User Id'                  => $this->user_id,
            'Office Type'                  => $this->office_type,
            'Assign Location Id'                  => $this->assign_location_id,
            'Committee Id'                  => $this->committee_id,
            'Committee Type Id'                  => $this->committee_type_id,
            'Created At'            => $this->created_at
        ];
    }
}
