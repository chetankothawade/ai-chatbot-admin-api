<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('role_modules')) {
            return;
        }

        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("UPDATE role_modules SET role = 'user' WHERE role = 'editor'");
        DB::statement("ALTER TABLE role_modules MODIFY role ENUM('super_admin','admin','user') NOT NULL");
    }

    public function down(): void
    {
        if (! Schema::hasTable('role_modules')) {
            return;
        }

        $driver = DB::getDriverName();
        if (! in_array($driver, ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("UPDATE role_modules SET role = 'editor' WHERE role = 'user'");
        DB::statement("ALTER TABLE role_modules MODIFY role ENUM('super_admin','admin','editor') NOT NULL");
    }
};
