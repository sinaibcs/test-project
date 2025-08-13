<?php

namespace App\Http\Resources\Mobile\Office;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\Admin\Geographic\CityResource;
use App\Http\Resources\Admin\Geographic\DistrictResource;
use App\Http\Resources\Admin\Geographic\DivisionResource;
use App\Http\Resources\Admin\Lookup\LookupResource;

class OfficeDropDownResource extends JsonResource
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
            'name_en' => $this->name_en,
            'name_bn' => $this->name_bn,
        ];
    }
}
