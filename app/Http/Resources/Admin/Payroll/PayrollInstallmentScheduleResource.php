<?php

namespace App\Http\Resources\Admin\Payroll;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PayrollInstallmentScheduleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            "id" => $this->id,
            "installment_number" => $this->installment_number,
            "installment_name" => $this->installment_name,
            "installment_name_bn" => $this->installment_name_bn,
            "payment_cycle" => $this->payment_cycle
        ];
    }
}
