<?php

namespace App\Http\Resources\Mobile\Lookup;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LookupResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                  =>      $this->id,
            'type'                =>      $this->type,
            'value_en'            =>      $this->value_en,
            'value_bn'            =>      $this->value_bn,
            'keyword'             =>      $this->keyword,
            'default'             =>      $this->default,

        ];
    }
}
