<?php

namespace App\Http\Resources\Admin\Sytemconfig;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LocationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $data= [
            'id'               => $this->id,
            'parent_id'        => $this->parent_id,
            'code'             => $this->code,
            'name_en'          => $this->name_en,
            'name_bn'          => $this->name_bn,
            'type'             => $this->type,
            'location_type'    => $this->location_type,
            'version'          => $this->version,
            'created_by'       => $this->created_by,
            'created_at'       => $this->created_at,
            'updated_at'       => $this->updated_at,
            'deleted_at'       => $this->deleted_at,
           
        ];
        if ($this->parent_id) {
            $data['parent_location']=$this->when($this->parent_id, new LocationResource(Location::find($this->parent_id)));

    } 
    return $data;
}
}