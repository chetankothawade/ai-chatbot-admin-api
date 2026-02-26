<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class ChatSessionRouteIdRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:chat_sessions,id',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id') ?? $this->route('session'),
        ]);
    }
}
