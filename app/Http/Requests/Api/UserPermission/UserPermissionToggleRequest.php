<?php

namespace App\Http\Requests\Api\UserPermission;

use App\Http\Requests\Api\BaseApiRequest;

class UserPermissionToggleRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'userUuid' => 'required|string|exists:users,uuid',
            'modulePermissionId' => 'required|integer|exists:module_permissions,id',
            'isChecked' => 'required|boolean',
        ];
    }
}
