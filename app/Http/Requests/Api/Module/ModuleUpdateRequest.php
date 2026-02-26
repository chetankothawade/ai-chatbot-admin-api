<?php

namespace App\Http\Requests\Api\Module;

use App\Enums\ActiveInactiveStatus;
use App\Enums\YesNoFlag;
use App\Http\Requests\Api\BaseApiRequest;
use Illuminate\Validation\Rule;

class ModuleUpdateRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'parent_id' => 'sometimes|nullable|integer|exists:modules,id',
            'name' => [
                'sometimes',
                'required',
                'string',
                'max:255',
                Rule::unique('modules', 'name')
                    ->ignore($this->route('uuid'), 'uuid'),
            ],
            'url' => 'nullable|string|max:255',
            'icon' => 'nullable|string|max:255',
            'seq_no' => 'nullable|integer',
            'is_sub_module' => 'sometimes|required|in:' . implode(',', YesNoFlag::values()),
            'is_permission' => 'sometimes|required|in:' . implode(',', YesNoFlag::values()),
            'status' => 'sometimes|required|in:' . implode(',', ActiveInactiveStatus::values()),
        ];
    }
}
