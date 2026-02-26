<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class ParticipantRemoveRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:chat_sessions,id',
            'user_id' => 'required|integer|exists:users,id',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
            'user_id' => $this->route('user_id'),
        ]);
    }
}
