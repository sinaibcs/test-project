<?php

namespace App\Http\Resources\Mobile\Geographic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class DistrictResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request)
    {
        // echo $this->districtParent;
        // if load districtParent then return this array otherwise return another array
        if ($this->districtParent) {
            return [
                'id'                   => $this->id,
                'name_en'              => $this->name_en,
                'name_bn'              => $this->name_bn,
                'code'                 => $this->code,
                'type'                 => $this->type,
                'location_type'                 => $this->location_type,
                'parent_id'                 => $this->parent_id,
                'parent'  => DivisionResource::make($this->whenLoaded('districtParent')),
            ];
        }else{



        return [
                'id'                   => $this->id,
            'name_en'              => $this->name_en,
            'name_bn'              => $this->name_bn,
            'code'                 => $this->code,
            'type'                 => $this->type,
            'parent'  => DivisionResource::make($this->whenLoaded('districtParent')),
            'division'  => DivisionResource::make($this->whenLoaded('parent')),
        ];
    }

    }


}
