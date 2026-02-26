<?php

namespace App\Http\Requests\Api\ActivityLog;

use App\Http\Requests\Api\BaseApiRequest;

class ActivityLogIndexRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'page' => 'nullable|integer|min:1',
            'search' => 'nullable|string|max:255',
            'sortedField' => 'nullable|in:id,created_at,event,log_name,subject_type,description',
            'sortedBy' => 'nullable|in:asc,desc',
            'perPage' => 'nullable|integer|min:1|max:100',
        ];
    }

    public function filters(): array
    {
        return [
            'search' => $this->input('search'),
            'sortedField' => $this->input('sortedField', 'id'),
            'sortedBy' => $this->input('sortedBy', 'desc'),
            'perPage' => (int) $this->input('perPage', 10),
        ];
    }
}
