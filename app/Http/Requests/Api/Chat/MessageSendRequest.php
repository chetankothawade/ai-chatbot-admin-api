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
            'message' => 'required_without:attachments|string|nullable',
            'attachments' => 'nullable|array|max:5',
            'attachments.*' => [
                'file',
                'max:10240',
                'mimetypes:text/plain,text/csv,application/json,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document,image/jpeg,image/png,image/webp,image/gif',
            ],
        ];
    }
}
