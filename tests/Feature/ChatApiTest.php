<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Services\AI\OpenAIService;

class ChatApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_session_and_send_message(): void
    {
        $user = User::factory()->create();

        // Bind a simple OpenAIService mock to return deterministic text
        $this->app->instance(OpenAIService::class, new class {
            public function chat($history)
            {
                $last = $history ? end($history)['content'] : '';
                return 'AI reply to: ' . $last;
            }
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

        $this->app->instance(OpenAIService::class, new class {
            public function chat($history) { return 'AI reply'; }
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
        $this->assertCount(2, $data);
    }
}
