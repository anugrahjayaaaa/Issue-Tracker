<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Permanent (admin) lock flag — DB-agnostic; avoids a far-future timestamp
        // sentinel that overflows the MySQL `timestamp` range (max 2038-01-19).
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('locked_permanently')->default(false)->after('locked_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('locked_permanently');
        });
    }
};
