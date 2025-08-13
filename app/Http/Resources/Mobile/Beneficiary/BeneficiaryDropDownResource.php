<?php

namespace App\Http\Resources\Mobile\Beneficiary;

use App\Http\Resources\Admin\Lookup\LookupResource;
use App\Http\Resources\Admin\Systemconfig\Allowance\AllowanceResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BeneficiaryDropDownResource extends JsonResource
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
            "application_id" => $this->application_id,
            "name_en" => $this->name_en,
            "name_bn" => $this->name_bn
        ];
    }
}
