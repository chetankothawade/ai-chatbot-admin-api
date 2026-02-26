<?php

namespace App\Http\Requests\Api\UserPermission;

use App\Http\Requests\Api\BaseApiRequest;

class UserPermissionUserRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'uuid' => 'required|string|exists:users,uuid',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'uuid' => $this->route('uuid'),
        ]);
    }
}
