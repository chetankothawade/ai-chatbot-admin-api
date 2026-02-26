<?php

namespace Database\Factories;

use App\Models\AIUsageLog;
use App\Models\ChatSession;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<AIUsageLog>
 */
class AIUsageLogFactory extends Factory
{
    protected $model = AIUsageLog::class;

    public function definition(): array
    {
        $promptTokens = fake()->numberBetween(50, 600);
        $completionTokens = fake()->numberBetween(30, 900);
        $total = $promptTokens + $completionTokens;

        return [
            'user_id' => User::factory(),
            'chat_id' => ChatSession::factory(),
            'model' => fake()->randomElement(['gpt-4.1-mini', 'gpt-4o-mini']),
            'prompt_tokens' => $promptTokens,
            'completion_tokens' => $completionTokens,
            'total_tokens' => $total,
            'cost' => fake()->randomFloat(5, 0.00010, 0.15000),
            'created_at' => fake()->dateTimeBetween('-30 days', 'now'),
        ];
    }
}
