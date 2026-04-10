<?php

declare(strict_types=1);

namespace App\Services\AI;

use GuzzleHttp\Client as GuzzleClient;
use Illuminate\Http\UploadedFile;
use OpenAI;
use OpenAI\Client;
use RuntimeException;

class OpenAIService
{
    protected Client $client;
    protected string $model;
    protected string $transcriptionModel;

    public function __construct()
    {
        $apiKey = config('services.openai.key');

        if (empty($apiKey)) {
            throw new RuntimeException('OPENAI_API_KEY is not configured.');
        }

        // Local environment only: disable SSL verification for current setup.
        $httpClient = new GuzzleClient([
            'verify' => false,
        ]);

        $this->client = OpenAI::factory()
            ->withApiKey($apiKey)
            ->withHttpClient($httpClient)
            ->make();

        $this->model = config('ai.model', 'gpt-4.1-mini');
        $this->transcriptionModel = config('ai.transcription_model', 'gpt-4o-mini-transcribe');
    }

    public function chat(array $messages): string
    {
        try {
            $response = retry(3, function () use ($messages) {
                return $this->client->chat()->create([
                    'model' => $this->model,
                    'messages' => $messages,
                ]);
            }, 1000);

            return $response->choices[0]->message->content ?? '';
        } catch (\Throwable $e) {
            logger()->error('OpenAI error', [
                'message' => $e->getMessage(),
            ]);

            return 'AI service temporarily unavailable. Please try again.';
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
            logger()->error('OpenAI error', [
                'message' => $e->getMessage(),
            ]);

            return 'AI service temporarily unavailable. Please try again.';
        }
    }

    public function transcribeAudio(
        UploadedFile $audio,
        ?string $language = null,
        ?string $prompt = null
    ): array {
        $stream = fopen($audio->getRealPath(), 'r');

        if ($stream === false) {
            throw new RuntimeException('Unable to read uploaded audio.');
        }

        try {
            $response = retry(3, function () use ($stream, $audio, $language, $prompt) {
                $payload = [
                    'model' => $this->transcriptionModel,
                    'file' => $stream,
                    'filename' => $audio->getClientOriginalName() ?: $audio->hashName(),
                    'response_format' => 'verbose_json',
                ];

                if ($language) {
                    $payload['language'] = $language;
                }

                if ($prompt) {
                    $payload['prompt'] = $prompt;
                }

                return $this->client->audio()->transcribe($payload);
            }, 1000);

            return [
                'text' => trim((string) ($response->text ?? '')),
                'language' => $response->language ?? null,
                'duration' => $response->duration ?? null,
            ];
        } catch (\Throwable $e) {
            logger()->error('OpenAI transcription error', [
                'message' => $e->getMessage(),
                'filename' => $audio->getClientOriginalName(),
                'mime_type' => $audio->getMimeType(),
            ]);

            throw new RuntimeException('Voice transcription failed. Please try again.');
        } finally {
            fclose($stream);
        }
    }
}
