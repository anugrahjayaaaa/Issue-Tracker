<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('status_transitions', function (Blueprint $table) {
            $table->string('required_role')->nullable()->after('to_status_id');
            $table->string('resolution')->nullable()->after('required_role');
        });
    }

    public function down(): void
    {
        Schema::table('status_transitions', function (Blueprint $table) {
            $table->dropColumn(['required_role', 'resolution']);
        });
    }
};
