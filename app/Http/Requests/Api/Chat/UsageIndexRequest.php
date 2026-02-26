<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class UsageIndexRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'from' => 'nullable|date',
            'to' => 'nullable|date|after_or_equal:from',
        ];
    }
}
