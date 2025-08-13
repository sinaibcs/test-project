<?php

namespace App\Http\Resources\Admin\Application;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MobileOperatorResource extends JsonResource
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
            'operator'            =>      $this->operator,
          

        ];
    }
}
