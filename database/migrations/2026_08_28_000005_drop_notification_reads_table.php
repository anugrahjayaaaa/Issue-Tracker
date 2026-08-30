<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // ponytail: drop the obsolete notification_reads pivot (replaced by native Laravel notifications)
    public function up(): void
    {
        Schema::dropIfExists('notification_reads');
    }

    public function down(): void
    {
        //
    }
};
