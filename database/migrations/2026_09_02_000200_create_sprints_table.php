<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sprints', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->text('goal')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'ends_at']);
        });

        Schema::table('issues', function (Blueprint $table) {
            $table->foreignId('sprint_id')->nullable()->after('parent_id')->constrained()->nullOnDelete();
            $table->index(['project_id', 'sprint_id']);
        });
    }

    public function down(): void
    {
        Schema::table('issues', function (Blueprint $table) {
            $table->dropForeign(['sprint_id']);
            $table->dropColumn('sprint_id');
            $table->dropIndex(['project_id', 'sprint_id']);
        });

        Schema::dropIfExists('sprints');
    }
};
