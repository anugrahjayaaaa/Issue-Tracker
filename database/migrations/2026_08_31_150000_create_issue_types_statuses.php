<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issue_types', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#6c757d'); // hex, used for badge
            $table->string('icon')->nullable();          // bootstrap icon class, e.g. bi-bug
            $table->text('description')->nullable();
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });

        Schema::create('statuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('color')->default('#6c757d');
            $table->boolean('is_closed')->default(false); // terminal state (done/closed)
            $table->unsignedInteger('order')->default(0);
            $table->timestamps();

            $table->unique(['project_id', 'name']);
        });

        // Workflow scheme: allowed from -> to. Empty for a project = free transitions.
        Schema::create('status_transitions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->foreignId('from_status_id')->constrained('statuses')->cascadeOnDelete();
            $table->foreignId('to_status_id')->constrained('statuses')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['project_id', 'from_status_id', 'to_status_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('status_transitions');
        Schema::dropIfExists('statuses');
        Schema::dropIfExists('issue_types');
    }
};
