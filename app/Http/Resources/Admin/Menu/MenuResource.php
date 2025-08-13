<?php

namespace App\Http\Resources\Admin\Menu;

use App\Http\Resources\Admin\PermissionResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MenuResource extends JsonResource
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
            "id" => $this->id,
            "label_name_en" => $this->label_name_en,
            "label_name_bn" => $this->label_name_bn,
            "order" => $this->order,
            "icon" => $this->icon,
            "page_link" =>PermissionResource::make($this->whenLoaded('pageLink')),
            "link_type" => $this->link_type,
            "link" => $this->link,
            "created_at" => $this->created_at,
            "updated_at" => $this->updated_at,
            "children" =>MenuResource::collection($this->whenLoaded('children')),
            // permission set by checking this menu has child or not if has child then check child has another child or not if not then check child has permission or not
            "permission" => $this->permissionName(),
        ];
    }

    public function permissionName()
    {
        // Ensure children are loaded
        if ($this->relationLoaded('children')) {
            if ($this->children->count() > 0) {
                foreach ($this->children as $child) {
                    if ($child->relationLoaded('children') && $child->children->count() > 0) {
                        foreach ($child->children as $child2) {
                            if ($child2->pageLink) {
                                return $child2->pageLink->module_name;
                            }
                            return $child->pageLink->sub_module_name;
                        }
                    } else if ($child->pageLink) {
                        return $child->pageLink->module_name;
                    }
                }
            }
        }

        return null;
    }
}
