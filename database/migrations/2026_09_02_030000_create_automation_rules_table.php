<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automation_rules', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('event'); // issue:status_changed, issue:created, issue:comment_added
            $table->json('conditions')->nullable(); // [{"field":"status","value":"done"}]
            $table->json('actions'); // [{"type":"assign","value":1},{"type":"notify","value":"watchers"}]
            $table->boolean('enabled')->default(true);
            $table->timestamps();

            $table->index(['project_id', 'event', 'enabled']);
        });

        Schema::create('automation_rule_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('automation_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('issue_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('success'); // success, error
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->index(['automation_rule_id', 'issue_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_rule_logs');
        Schema::dropIfExists('automation_rules');
    }
};
