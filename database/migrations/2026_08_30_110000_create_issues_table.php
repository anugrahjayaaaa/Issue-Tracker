<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->string('code');                       // HEL-12 (unique per project)
            $table->string('title');
            $table->text('description')->nullable();      // rich text HTML (sanitized)
            $table->string('type', 16)->default('task');   // bug|feature|task|epic
            $table->string('status', 16)->default('open'); // open|in_progress|blocked|done
            $table->string('priority', 16)->default('medium'); // low|medium|high|urgent
            $table->foreignId('reporter_id')->constrained('users')->cascadeOnDelete();
            $table->foreignId('assignee_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('issues')->nullOnDelete();
            $table->date('due_date')->nullable();
            $table->unsignedInteger('order')->default(0);  // kanban column sort
            $table->softDeletes();
            $table->timestamps();

            $table->unique(['project_id', 'code']);
            $table->index(['project_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
