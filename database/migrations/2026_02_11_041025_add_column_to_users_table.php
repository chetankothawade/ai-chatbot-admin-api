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
        Schema::table('users', function (Blueprint $table) {
            $table->uuid('uuid')->unique()->after('id')->nullable();
            $table->string('phone')->after('email')->nullable();
            $table->enum('status', ['active', 'inactive', 'deleted'])->default('active')->after('remember_token');
            $table->enum('role', ['super_admin', 'admin', 'user'])->default('admin')->after('status');
            $table->timestamp('last_login_at')->after('role')->nullable();
            $table->timestamp('last_logout_at')->nullable()->after('last_login_at');
            $table->string('last_login_ip')->after('last_login_at')->nullable();
            $table->text('last_login_ua')->nullable()->after('last_login_ip');
            $table->softDeletes(); // adds deleted_at nullable timestamp
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', callback: function (Blueprint $table) {
            $table->dropColumn('uuid');
            $table->dropColumn('role');
            $table->dropColumn('phone');
            $table->dropColumn('last_login_ip');
            $table->dropColumn('status');
            $table->dropColumn('last_login_at');
            $table->dropColumn('last_logout_at');
            $table->dropColumn('last_login_ua');
            $table->dropSoftDeletes();
        });
    }
};
