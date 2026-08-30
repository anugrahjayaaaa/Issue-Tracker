<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('licenses', function (Blueprint $table) {
            $table->id();
            $table->string('plan_slug');
            $table->string('license_key');               // signed: LIC-{slug}-{hash}
            $table->string('type');                      // recurring|lifetime|manual
            $table->string('status')->default('active'); // active|revoked|expired
            $table->string('issued_to')->nullable();     // bound to the instance it was activated on (NON-TRANSFERABLE)
            $table->timestamp('expires_at')->nullable();  // null = never expires
            $table->json('snapshot')->nullable();         // plan limits/features at issue time (catalog versioning)
            $table->text('revoke_reason')->nullable();
            $table->timestamps();

            $table->unique('license_key');
            // ponytail: at most ONE active license per instance (race guard, §10.9)
            $table->index(['status', 'issued_to']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('licenses');
    }
};
