<?php

namespace App\Http\Requests\Api\User;

use App\Enums\UserStatus;
use App\Http\Requests\Api\BaseApiRequest;

class UserIndexRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'page'        => 'nullable|integer|min:1',
            'search'      => 'nullable|string|max:255',
            'status'      => 'nullable|in:' . implode(',', UserStatus::creatableValues()),
            'sortedField' => 'nullable|in:id,name,email,created_at,status,phone,role',
            'sortedBy'    => 'nullable|in:asc,desc',
            'perPage'     => 'nullable|integer|min:1|max:100',
        ];
    }

    public function filters(): array
    {
        return [
            'search'      => $this->input('search'),
            'status'      => $this->input('status'),
            'sortedField' => $this->input('sortedField', 'id'),
            'sortedBy'    => $this->input('sortedBy', 'asc'),
            'perPage'     => $this->input('perPage', 10),
        ];
    }
}
