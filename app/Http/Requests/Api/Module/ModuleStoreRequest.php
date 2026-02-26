<?php

namespace App\Http\Requests\Api\Module;

use App\Enums\ActiveInactiveStatus;
use App\Enums\YesNoFlag;
use App\Http\Requests\Api\BaseApiRequest;

class ModuleStoreRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => 'nullable|integer|min:0',
            'name' => 'required|string|max:255|unique:modules,name',
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'seq_no' => 'nullable|integer',
            'is_sub_module' => 'required|in:' . implode(',', YesNoFlag::values()),
            'is_permission' => 'required|in:' . implode(',', YesNoFlag::values()),
            'status' => 'required|in:' . implode(',', ActiveInactiveStatus::values()),
        ];
    }
}
