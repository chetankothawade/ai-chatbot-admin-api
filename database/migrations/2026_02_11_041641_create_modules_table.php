<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('modules', function (Blueprint $table) {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->unsignedBigInteger('parent_id')->nullable();
            $table->string('name', 50)->unique();
            $table->string('url', 100)->nullable();
            $table->string('icon', 100)->nullable();
            $table->integer('seq_no')->nullable();
            $table->enum('is_sub_module', ['Y', 'N'])->default('N');
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->enum('is_permission', ['Y', 'N'])->default('N');
            $table->timestamps();

            // self-referencing parent id
            $table->foreign('parent_id')->references('id')->on('modules')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modules');
    }
};
