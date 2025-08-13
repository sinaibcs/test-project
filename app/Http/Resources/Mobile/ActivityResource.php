<?php

namespace App\Http\Resources\Mobile;

use Illuminate\Http\Resources\Json\JsonResource;

class ActivityResource extends JsonResource
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
            'id'                    => $this->id,
            'description'                 => $this->description,
            'log_name'            => $this->log_name,
            'subject'            => $this->subject,
            'subject_id'            => $this->subject_id,
            'subject_type'            => $this->subject_type,
            'event'            => $this->event,
            'causer_type'            => $this->causer_type,
            'causer'                  => CauserResource::make($this->whenLoaded('causer')),
            'properties'            => $this->properties,
            'created_at'            => $this->created_at->diffForHumans(),
            'database_created_at' => $this->created_at->format('d M Y, h:i:s A'),

        ];
    }

//    public function withResponse($request, $response)
//    {
//        // Add pagination metadata
//        $paginationData = [
//            'current_page' => $this->resource->currentPage(),
//            'per_page' => $this->resource->perPage(),
//            'total' => $this->resource->total(),
//            'last_page' => $this->resource->lastPage(),
//            // You can add more pagination metadata here if needed
//        ];
//
//        $response->setData(array_merge(
//            $response->getData(true),
//            ['meta' => $paginationData]
//        ));
//    }
}
