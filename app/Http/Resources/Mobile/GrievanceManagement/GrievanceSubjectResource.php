<?php

namespace App\Http\Resources\Mobile\GrievanceManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrievanceSubjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
       'id' => $this->id,
       'title_en' => $this->title_en,
       'title_bn' => $this->title_bn,
       'status' => $this->status,
       'grievance_type_id' => $this->grievance_type_id,
       'grievanceTypeEn'=>$this->grievanceType->title_en ?? '',
       'grievanceTypeBn'=>$this->grievanceType->title_bn ?? '',

];

    }
}
