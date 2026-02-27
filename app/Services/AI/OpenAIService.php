<?php

declare(strict_types=1);

namespace App\Services\AI;

use OpenAI\Client;
use OpenAI;
use GuzzleHttp\Client as GuzzleClient;
use RuntimeException;

class OpenAIService
{
    protected Client $client;
    protected string $model;

    public function __construct()
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        // 👇 Disable SSL verification (LOCAL ONLY)
        $httpClient = new GuzzleClient([
            'verify' => false,
        ]);

        $this->client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($httpClient)
            ->make();

        $this->model = config('ai.model', 'gpt-4.1-mini');
    }

    public function chat(array $messages): string
    {
        try {
            $response = retry(3, function () use ($messages) {
                return $this->client->chat()->create([
                    'model' => $this->model,
                    'messages' => $messages,
                ]);
            }, 1000); // 1 second delay

            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            logger()->error('OpenAI Error', [
                'message' => $e->getMessage()
            ]);

            return '⚠️ AI service temporarily unavailable. Please try again.';
        }
    }

    public function chat_1(array $messages): string
    {
        try {
            $response = $this->client->chat()->create([
                'model' => $this->model,
                'messages' => $messages,
            ]);

            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            logger()->error('OpenAI Error', [
                'message' => $e->getMessage()
            ]);

            return '⚠️ AI service temporarily unavailable. Please try again.';
        }
    }
}
