<?php

namespace App\Http\Resources\Admin\PMTScore;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VariableResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
                return parent::toArray($request);

        return [
            'id'               =>      $this->id,
            'name_en'           =>      $this->name_en,
            'field_type'        =>      $this->field_type,
            'parent_id'         =>      $this->parent_id,
            'children'          =>  VariableResource::collection($this->whenLoaded('children'))
        ];
    }
}
