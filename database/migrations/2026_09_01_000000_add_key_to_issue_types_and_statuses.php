<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\IssueType;
use App\Models\Status;

return new class extends Migration
{
    /**
     * Introduce a stable `key` slug on statuses / issue_types so renaming a
     * status/type label does NOT orphans issues (previously Issue.status/type
     * stored the human `name`). Issues are backfilled to store the key.
     * Key generation mirrors Str::slug() for ASCII names (lowercase, spaces -> '-').
     */
    public function up(): void
    {
        Schema::table('issue_types', function (Blueprint $table) {
            $table->string('key')->after('project_id')->nullable();
            $table->unique(['project_id', 'key']);
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->string('key')->after('project_id')->nullable();
            $table->unique(['project_id', 'key']);
        });

        // Backfill keys from names (ASCII slug: lowercase, spaces -> dash).
        DB::statement("UPDATE issue_types SET `key` = LOWER(REPLACE(name, ' ', '-')) WHERE `key` IS NULL");
        DB::statement("UPDATE statuses SET `key` = LOWER(REPLACE(name, ' ', '-')) WHERE `key` IS NULL");

        // Backfill issues: map stored name -> key. Done in PHP (no DB-specific JOIN
        // syntax) so it runs on both MySQL and the SQLite test database.
        foreach (IssueType::all() as $t) {
            if ($t->key !== null) {
                DB::table('issues')->where('project_id', $t->project_id)
                    ->where('type', $t->name)->update(['type' => $t->key]);
            }
        }
        foreach (Status::all() as $s) {
            if ($s->key !== null) {
                DB::table('issues')->where('project_id', $s->project_id)
                    ->where('status', $s->name)->update(['status' => $s->key]);
            }
        }

        // Make keys non-nullable now that backfill is complete.
        Schema::table('issue_types', function (Blueprint $table) {
            $table->string('key')->nullable(false)->change();
        });
        Schema::table('statuses', function (Blueprint $table) {
            $table->string('key')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('issue_types', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'key']);
            $table->dropColumn('key');
        });

        Schema::table('statuses', function (Blueprint $table) {
            $table->dropUnique(['project_id', 'key']);
            $table->dropColumn('key');
        });
    }
};
