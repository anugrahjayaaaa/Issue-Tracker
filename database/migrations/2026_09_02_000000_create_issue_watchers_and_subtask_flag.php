<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_watchers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('issue_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestamps();
            $table->unique(['issue_id', 'user_id']);
        });

        // Opt-in sub-task rollup (parent auto-flips to a closed status at 100% done).
        Schema::table('projects', function (Blueprint $table) {
            $table->boolean('subtask_rollup')->default(false);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issue_watchers');
        Schema::table('projects', function (Blueprint $table) {
            $table->dropColumn('subtask_rollup');
        });
    }
};
