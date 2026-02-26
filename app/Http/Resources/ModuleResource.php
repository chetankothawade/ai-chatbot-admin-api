<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ModuleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'uuid'           => $this->uuid,
            'parent_id'      => $this->parent_id,
            'name'           => $this->name,
            'url'            => $this->url,
            'icon'           => $this->icon,
            'seq_no'         => $this->seq_no,
            'is_sub_module'  => $this->is_sub_module,
            'is_permission'  => $this->is_permission,
            'status'         => $this->status,
            'created_at'     => optional($this->created_at)->format('d/m/Y h:i A'),
            'updated_at'     => optional($this->updated_at)->format('d/m/Y h:i A'),

            // Sub-modules (if needed)
            'children'       => ModuleResource::collection($this->whenLoaded('subModules')),
        ];
    }
}
