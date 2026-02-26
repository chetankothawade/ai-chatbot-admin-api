<?php

namespace Database\Factories;

use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<ChatSession>
 */
class ChatSessionFactory extends Factory
{
    protected $model = ChatSession::class;

    public function definition(): array
    {
        return [
            'uuid' => (string) Str::uuid(),
            'user_id' => User::factory(),
            'title' => fake()->sentence(4),
            'model' => fake()->randomElement(['gpt-4.1-mini', 'gpt-4o-mini']),
            'context' => [
                'topic' => fake()->word(),
                'locale' => fake()->randomElement(['en', 'en-US']),
            ],
            'is_pinned' => fake()->boolean(20),
            'last_message_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
