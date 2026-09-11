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
        $isSqlite = Schema::getConnection()->getDriverName() === 'sqlite';

        Schema::table('users', function (Blueprint $table) use ($isSqlite) {
            $table->string('phone')->unique()->after('name');
            $table->enum('role', ['student', 'therapist', 'admin'])->default('student')->after('phone');

            // SQLite requires removing this index before rebuilding the table.
            if ($isSqlite) {
                $table->dropUnique('users_email_unique');
            }

            $table->dropColumn(['email', 'email_verified_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role']);
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
        });
    }
};
