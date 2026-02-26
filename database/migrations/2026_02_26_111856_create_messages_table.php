<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();

            $table->foreignId('chat_id')
                ->constrained('chat_sessions')
                ->cascadeOnDelete();

            $table->enum('role', ['user', 'assistant', 'system']);

            $table->longText('content');

            $table->integer('tokens')->nullable();
            $table->integer('response_time_ms')->nullable();

            $table->unsignedBigInteger('parent_id')->nullable();

            $table->timestamp('created_at')->useCurrent();

            // Indexes
            $table->index('chat_id');
            $table->index('created_at');

            // Optional FULLTEXT (MySQL 8)
            $table->fullText('content');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};