<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Account lockout: set when failed logins exceed the limit; null = not locked.
        // Auto-unlocks when locked_until passes (compared at login time).
        Schema::table('users', function (Blueprint $table) {
            $table->timestamp('locked_until')->nullable()->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locked_until');
        });
    }
};
