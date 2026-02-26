<?php

namespace Database\Seeders;

use App\Models\AIUsageLog;
use App\Models\ChatParticipant;
use App\Models\ChatSession;
use App\Models\Message;
use App\Models\MessageMetadata;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Schema;

class ChatSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::query()->inRandomOrder()->limit(10)->get();

        if ($users->isEmpty()) {
            $users = User::factory()->count(10)->create();
        }

        $hasMetadataColumns = Schema::hasColumns('message_metadata', ['message_id', 'type', 'meta']);

        foreach (range(1, 12) as $i) {
            $owner = $users->random();

            $session = ChatSession::factory()->create([
                'user_id' => $owner->id,
                'title' => "Sample Chat {$i}",
            ]);

            ChatParticipant::query()->firstOrCreate([
                'chat_id' => $session->id,
                'user_id' => $owner->id,
            ], [
                'role' => 'owner',
            ]);

            $extraParticipantCount = random_int(0, 2);
            $extraUsers = $users->where('id', '!=', $owner->id)->shuffle()->take($extraParticipantCount);
            foreach ($extraUsers as $extraUser) {
                ChatParticipant::query()->firstOrCreate([
                    'chat_id' => $session->id,
                    'user_id' => $extraUser->id,
                ], [
                    'role' => 'member',
                ]);
            }

            $turns = random_int(2, 6);
            $lastMessageAt = null;

            for ($t = 0; $t < $turns; $t++) {
                $userMessage = Message::factory()->create([
                    'chat_id' => $session->id,
                    'role' => 'user',
                    'response_time_ms' => null,
                    'parent_id' => null,
                ]);

                $assistantMessage = Message::factory()->create([
                    'chat_id' => $session->id,
                    'role' => 'assistant',
                    'parent_id' => $userMessage->id,
                ]);

                $lastMessageAt = $assistantMessage->created_at ?? $userMessage->created_at;

                if ($hasMetadataColumns && random_int(0, 1) === 1) {
                    MessageMetadata::factory()->create([
                        'message_id' => $assistantMessage->id,
                    ]);
                }

                AIUsageLog::factory()->create([
                    'user_id' => $owner->id,
                    'chat_id' => $session->id,
                    'model' => $session->model,
                    'created_at' => $assistantMessage->created_at ?? now(),
                ]);
            }

            $session->update([
                'last_message_at' => $lastMessageAt ?? now(),
            ]);
        }
    }
}
