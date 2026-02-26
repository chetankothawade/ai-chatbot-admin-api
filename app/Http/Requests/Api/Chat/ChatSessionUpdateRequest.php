<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class ChatSessionUpdateRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:chat_sessions,id',
            'title' => 'sometimes|nullable|string|max:255',
            'is_pinned' => 'sometimes|boolean',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id') ?? $this->route('session'),
        ]);
    }
}
