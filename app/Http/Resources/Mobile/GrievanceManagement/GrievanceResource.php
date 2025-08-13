<?php

namespace App\Http\Resources\Mobile\GrievanceManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrievanceResource extends JsonResource
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
        'name' => $this->name,
        'date_of_birth' => $this->date_of_birth	,
        'verification_number' => $this->verification_number	,
        'gender' => $this->gender->value_en ?? null,
        'program' => $this->program->name_en ?? null,
        'tracking_no' => $this->tracking_no,
        'email' => $this->email,
        'mobile' => $this->mobile,
        'details' => $this->details,
        'documents' => $this->documents,
        'address' => $this->address,
        'status' => $this->status,
        'created_at' => $this->created_at,
        'grievanceTypeEn' => $this->grievanceType->title_en ?? null,
        'grievanceSubjectEn' => $this->grievanceSubject->title_en ?? null,
        // location
         'district' => $this->district->name_en ?? null,
         'districtPouroshova' => $this->districtPouroshova->name_en ?? null,
         'cityCorporation' => $this->cityCorporation->name_en ?? null,
         'upazila' => $this->upazila->name_en ?? null,
         'thana' => $this->thana->name_en ?? null,
         'union' => $this->union->name_en ?? null,
         'pourashava' => $this->pourashava->name_en ?? null,
         'ward' => $this->ward->name_en ?? null,




];

    }
}
