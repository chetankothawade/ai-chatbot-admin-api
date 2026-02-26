<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('user_permissions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('module_permission_id');
            $table->timestamps();

            $table->unique(['user_id', 'module_permission_id'], 'unique_user_permission');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('module_permission_id')->references('id')->on('module_permissions')->cascadeOnDelete();
        });
    }

    public function down(): void {
        Schema::dropIfExists('user_permissions');
    }
};
