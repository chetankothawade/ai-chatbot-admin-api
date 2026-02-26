<?php

declare(strict_types=1);

namespace App\Http\Requests\Api\Chat;

use App\Http\Requests\Api\BaseApiRequest;

class MetadataIndexRequest extends BaseApiRequest
{
    public function rules(): array
    {
        return [
            'id' => 'required|integer|exists:messages,id',
        ];
    }

    public function validationData(): array
    {
        return array_merge($this->all(), [
            'id' => $this->route('id'),
        ]);
    }
}
