<?php

namespace App\Http\Resources\Admin;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id'                    => $this->id,
            'name'                    => $this->name,
            'module_name'                         => $this->module_name,
            'sub_module_name'                         => $this->sub_module_name,
            'page_url'                         => $this->page_url,
            'parent_page'                         => $this->parent_page,
        ];
    }
}
