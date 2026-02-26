<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class MessageSendRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'chat_id' => 'required|integer|exists:chat_sessions,id',
            'message' => 'required|string',
        ];
    }
}
