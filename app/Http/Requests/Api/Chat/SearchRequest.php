<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class SearchRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'q' => 'required|string',
            'limit' => 'nullable|integer|min:1|max:200',
        ];
    }
}
