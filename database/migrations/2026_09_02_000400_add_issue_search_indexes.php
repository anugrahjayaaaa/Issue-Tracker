<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->index(['project_id', 'code']);
            $table->index(['project_id', 'title']);
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropIndex(['project_id', 'code']);
            $table->dropIndex(['project_id', 'title']);
        });
    }
};
