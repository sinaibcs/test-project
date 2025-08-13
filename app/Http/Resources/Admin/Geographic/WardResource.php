<?php

namespace App\Http\Resources\Admin\Geographic;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WardResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return [
        //     'id'                   => $this->id,
        //     'name_en'              => $this->name_en,
        //     'name_bn'              => $this->name_bn,
        //     'code'                 => $this->code,
        //     'union'  => UnionResource::make($this->whenLoaded('parent')),
        // ];
        return parent::toArray($request);

    }
}
