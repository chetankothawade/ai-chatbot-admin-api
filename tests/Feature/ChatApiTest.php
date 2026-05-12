<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\MessageAttachment;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use App\Services\AI\OpenAIService;
use Mockery\MockInterface;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_session_and_send_message(): void
    {
        $user = User::factory()->create();

        $this->mock(OpenAIService::class, function (MockInterface $mock) {
            $mock->shouldReceive('chat')->andReturnUsing(function ($history) {
                $last = $history ? end($history)['content'] : '';
                return 'AI reply to: ' . $last;
            });
        });

        Sanctum::actingAs($user, ['*']);

        // Create a chat session
        $resp = $this->postJson('/api/chat/sessions', []);
        $resp->assertStatus(201)
            ->assertJson(['status' => true]);

        $chatId = $resp->json('data.id');

        // Send a message
        $send = $this->postJson('/api/chat/messages/send', [
            'chat_id' => $chatId,
            'message' => 'Hello world',
        ]);

        $send->assertStatus(200)
            ->assertJsonPath('data.user_message.content', 'Hello world')
            ->assertJsonPath('data.assistant_message.content', 'AI reply to: Hello world');
    }

    public function test_can_fetch_messages_for_session(): void
    {
        $user = User::factory()->create();

        $this->mock(OpenAIService::class, function (MockInterface $mock) {
            $mock->shouldReceive('chat')->andReturn('AI reply');
        });

        Sanctum::actingAs($user, ['*']);

        $resp = $this->postJson('/api/chat/sessions', []);
        $chatId = $resp->json('data.id');

        $this->postJson('/api/chat/messages/send', ['chat_id' => $chatId, 'message' => 'Msg1']);
        $this->postJson('/api/chat/messages/send', ['chat_id' => $chatId, 'message' => 'Msg2']);

        $index = $this->getJson('/api/chat/messages?chat_id=' . $chatId . '&limit=10');
        $index->assertStatus(200)
            ->assertJson(['status' => true, 'message' => 'Messages fetched']);

        $data = $index->json('data.data');
        $this->assertIsArray($data);
        $this->assertCount(4, $data);
    }

    public function test_can_send_message_with_file_attachment(): void
    {
        Storage::fake('public');

        $user = User::factory()->create();

        $this->mock(OpenAIService::class, function (MockInterface $mock) {
            $mock->shouldReceive('chat')->andReturnUsing(function ($history) {
                $last = $history ? end($history)['content'] : '';

                $this->assertStringContainsString('notes.txt', $last);
                $this->assertStringContainsString('Important note', $last);

                return 'Got attachment';
            });
        });

        Sanctum::actingAs($user, ['*']);

        $session = $this->postJson('/api/chat/sessions', []);
        $chatId = $session->json('data.id');

        $response = $this->post('/api/chat/messages/send', [
            'chat_id' => $chatId,
            'message' => 'Please review this file',
            'attachments' => [
                UploadedFile::fake()->createWithContent('notes.txt', 'Important note'),
            ],
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user_message.content', 'Please review this file')
            ->assertJsonPath('data.user_message.attachments.0.name', 'notes.txt')
            ->assertJsonPath('data.assistant_message.content', 'Got attachment');

        $attachment = MessageAttachment::query()->first();

        $this->assertNotNull($attachment);
        Storage::disk('public')->assertExists($attachment->path);
    }
}
