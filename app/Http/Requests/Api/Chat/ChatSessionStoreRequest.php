<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class ChatSessionStoreRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'title' => 'nullable|string|max:255',
            'model' => 'nullable|string|max:100',
        ];
    }
}
