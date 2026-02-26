<?php

namespace Database\Factories;

use App\Models\ChatParticipant;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ChatParticipant>
 */
class ChatParticipantFactory extends Factory
{
    protected $model = ChatParticipant::class;

    public function definition(): array
    {
        return [
            'chat_id' => ChatSession::factory(),
            'user_id' => User::factory(),
            'role' => fake()->randomElement(['owner', 'member']),
        ];
    }
}
