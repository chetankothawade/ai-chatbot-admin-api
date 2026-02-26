<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'uuid'           => $this->when(isset($this->uuid), $this->uuid),
            'name'           => $this->when(isset($this->name), $this->name),
            'email'          => $this->when(isset($this->email), $this->email),
            'phone'          => $this->when(isset($this->phone), $this->phone),
            'status'         => $this->when(isset($this->status), $this->status),
            'role'           => $this->when(isset($this->role), $this->role),
            'last_login_ip'  => $this->when(isset($this->last_login_ip), $this->last_login_ip),
            'last_login_at'  => $this->when(
                isset($this->last_login_at),
                optional($this->last_login_at)->format('d/m/Y h:i A')
            ),
            'created_at'     => $this->when(
                isset($this->created_at),
                optional($this->created_at)->format('d/m/Y h:i A')
            ),
            'updated_at'     => $this->when(
                isset($this->updated_at),
                optional($this->updated_at)->format('d/m/Y h:i A')
            ),
        ];
    }
}
