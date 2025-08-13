<?php

namespace App\Http\Resources\Mobile\PMTScore;

use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PMTScoreResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [

            'id'               =>      $this->id,
            'type'             =>      $this->type,
            // 'location_id'      =>      Location::find($this->location_id),
            'location_id'      =>      $this->location_id,
            'assign_location'      =>  Location::find($this->location_id),
            'score'          =>      number_format((float)$this->score, 2)
        ];
    }
}
