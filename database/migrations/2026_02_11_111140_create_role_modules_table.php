<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('role_modules', function (Blueprint $table) {
            $table->id();

            $table->enum('role', [
                'super_admin',
                'admin',
                'user'
            ])->index();

            $table->foreignId('module_id')
                ->constrained('modules')
                ->cascadeOnDelete();

            $table->timestamps();

            $table->unique(['role', 'module_id'], 'role_module_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_modules');
    }
};
