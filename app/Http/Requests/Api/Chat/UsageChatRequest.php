<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class UsageChatRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'chat_id' => 'required|integer|exists:chat_sessions,id',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'chat_id' => $this->route('chat_id') ?? $this->route('chatId'),
        ]);
    }
}
