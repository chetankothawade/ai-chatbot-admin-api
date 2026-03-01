<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('message_metadata', function (Blueprint $table) {
            if (!Schema::hasColumn('message_metadata', 'message_id')) {
                $table->foreignId('message_id')->constrained('messages')->cascadeOnDelete();
            }

            if (!Schema::hasColumn('message_metadata', 'type')) {
                $table->string('type', 50)->default('manual');
            }

            if (!Schema::hasColumn('message_metadata', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('message_metadata', function (Blueprint $table) {
            if (Schema::hasColumn('message_metadata', 'message_id')) {
                $table->dropForeign(['message_id']);
                $table->dropColumn('message_id');
            }

            if (Schema::hasColumn('message_metadata', 'type')) {
                $table->dropColumn('type');
            }

            if (Schema::hasColumn('message_metadata', 'meta')) {
                $table->dropColumn('meta');
            }
        });
    }
};
