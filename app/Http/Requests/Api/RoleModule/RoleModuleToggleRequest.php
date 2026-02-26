<?php

namespace App\Http\Requests\Api\RoleModule;

use App\Enums\UserRole;
use App\Http\Requests\Api\BaseApiRequest;

class RoleModuleToggleRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'role' => 'required|in:' . implode(',', UserRole::values()),
            'module_id' => 'required|exists:modules,id',
            'enabled' => 'required|boolean',
        ];
    }
}
