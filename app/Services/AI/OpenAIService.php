<?php

declare(strict_types=1);

namespace App\Services\AI;

use OpenAI\Laravel\Facades\OpenAI;



class OpenAIService
{
    public function chat(array $messages): string
    {
        $response = OpenAI::chat()->create([
            'model' => config('ai.model', 'gpt-4.1-mini'),
            'messages' => $messages,
        ]);

        return $response['choices'][0]['message']['content'] ?? '';
    }
}