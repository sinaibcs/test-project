<?php

namespace App\Http\Resources\Admin\GrievanceManagement;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GrievanceSettingResource extends JsonResource
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
    'grievance_type_id' => $this->grievance_type_id,
    'grievanceTypeEn' => $this->grievanceType->title_en ?? null,
    'grievanceTypeBn' => $this->grievanceType->title_bn ?? null,
    'grievance_subject_id' => $this->grievance_subject_id,
    'grievanceSubjectEn' => $this->grievanceSubject->title_en ?? null,
    'grievanceSubjectBn' => $this->grievanceSubject->title_bn ?? null,
    'first_tire_officer' => $this->firstOfficer->name ?? null,
    'first_tire_officer_id' => $this->first_tire_officer,
    'first_tire_solution_time' => $this->first_tire_solution_time,
    'secound_tire_officer' => $this->secoundOfficer->name ?? null,
    'secound_tire_officer_id' => $this->secound_tire_officer,
    'secound_tire_solution_time' => $this->secound_tire_solution_time,
    'third_tire_officer' => $this->thirdOfficer->name ?? null,
    'third_tire_officer_id' => $this->third_tire_officer,
    'third_tire_solution_time' => $this->third_tire_solution_time,

];

    }
}