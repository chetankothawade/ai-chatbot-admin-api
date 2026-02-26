<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class ParticipantAddRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:chat_sessions,id',
            'user_id' => 'required|integer|exists:users,id',
            'role' => 'nullable|string|max:50',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }
}
