<?php

namespace App\Http\Resources\Admin\Employee;

use App\Http\Services\Admin\Employee\DepartmentService;
use Illuminate\Http\Resources\Json\JsonResource;

class EmployeeResource extends JsonResource
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
            'full_name'                 => $this->full_name,
            'email'                 => $this->email,
            'phone'                 => $this->phone,
            'status'                 => $this->status,
            'profile'                 => $this->profile,
            'payment_type'                 => $this->payment_type,
            'wallet_type'                 => $this->wallet_type,
            'wallet_number'                 => $this->wallet_number,
            'bank_name'                 => $this->bank_name,
            'bank_branch_name'                 => $this->bank_branch_name,
            'bank_account_type'                 => $this->bank_account_type,
            'bank_routing_num'                 => $this->bank_routing_num,
            'bank_account_holder_name'                 => $this->bank_account_holder_name,
            'bank_account_num'                 => $this->bank_account_num,
            'department'                  => DepartmentRrsource::make($this->whenLoaded('department')),
            'branch'                  => $this->whenLoaded('branch'),
            'employee_id'                  => $this->employee_id,
            'date_of_birth'                  => $this->date_of_birth,
            'join_date'                  => $this->join_date,
            'address'                  => $this->address,
            'employee_shift_id'                  => $this->whenLoaded('WorkingShift'),
            'gender'                  => $this->gender,
            'gurdian_contact'                  => $this->gurdian_contact,
            'father_name'                  => $this->father_name,
            'mother_name'                  => $this->mother_name,
            'present_address'                  => $this->present_address,
            'permanent_address'                  => $this->permanent_address,
            'salary'                  => $this->salary,
            'created_at'            => $this->created_at
        ];
    }
}
