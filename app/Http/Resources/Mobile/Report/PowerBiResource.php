<?php

namespace App\Http\Resources\Mobile\Report;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PowerBiResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return parent::toArray($request);
    }
}
