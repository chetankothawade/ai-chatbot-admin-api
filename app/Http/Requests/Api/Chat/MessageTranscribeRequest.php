<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class MessageTranscribeRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'audio' => [
                'required',
                'file',
                'max:25600',
                'mimetypes:audio/webm,audio/wav,audio/x-wav,audio/mpeg,audio/mp4,audio/x-m4a,audio/aac,audio/ogg,video/webm',
            ],
            'language' => ['nullable', 'string', 'max:10'],
            'prompt' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
