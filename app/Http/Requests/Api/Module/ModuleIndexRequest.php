<?php

namespace App\Http\Requests\Api\Module;

use App\Enums\ActiveInactiveStatus;
use App\Http\Requests\Api\BaseApiRequest;

class ModuleIndexRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:255',
            'status' => 'nullable|in:' . implode(',', ActiveInactiveStatus::values()),
            'sortedField' => 'nullable|in:id,name,created_at,status,icon,url,seq_no',
            'sortedBy' => 'nullable|in:asc,desc',
            'perPage' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function filters(): array
    {
        return [
            'search' => $this->input('search'),
            'status' => $this->input('status'),
            'sortedField' => $this->input('sortedField', 'id'),
            'sortedBy' => $this->input('sortedBy', 'desc'),
            'perPage' => (int) $this->input('perPage', 10),
        ];
    }
}
