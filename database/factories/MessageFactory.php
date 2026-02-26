<?php

namespace Database\Factories;

use App\Models\ChatSession;
use App\Models\Message;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Message>
 */
class MessageFactory extends Factory
{
    protected $model = Message::class;

    public function definition(): array
    {
        return [
            'chat_id' => ChatSession::factory(),
            'role' => fake()->randomElement(['user', 'assistant']),
            'content' => fake()->paragraph(),
            'tokens' => fake()->numberBetween(20, 600),
            'response_time_ms' => fake()->numberBetween(100, 3000),
            'parent_id' => null,
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
