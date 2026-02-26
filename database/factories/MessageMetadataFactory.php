<?php

namespace Database\Factories;

use App\Models\Message;
use App\Models\MessageMetadata;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<MessageMetadata>
 */
class MessageMetadataFactory extends Factory
{
    protected $model = MessageMetadata::class;

    public function definition(): array
    {
        return [
            'message_id' => Message::factory(),
            'type' => fake()->randomElement(['feedback', 'source', 'trace']),
            'meta' => [
                'rating' => fake()->numberBetween(1, 5),
                'note' => fake()->sentence(),
            ],
        ];
    }
}
