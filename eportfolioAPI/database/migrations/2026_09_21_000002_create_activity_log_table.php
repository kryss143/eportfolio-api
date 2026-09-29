<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Activity log lives on MongoDB (written by ActivityObserver: user_id,
    // subject_type, subject_id, event, old_values/new_values, timestamps).
    //
    // Audit post-mongo Bug 2 (2026-09-28): index creation goes through
    // MongoSchema::ensureIndexes() — the single authority. The previous
    // inline createIndex calls drifted ({created_at: -1} requested under the
    // existing {created_at: 1} name "idx_created_at") and every later run
    // threw CommandException code 86 as an unhandled 500.
    public function up(): void
    {
        MongoSchema::createCollection('activity_log', [
            ['name' => 'idx_event', 'key' => ['event' => 1]],
            ['name' => 'idx_subject', 'key' => ['subject_type' => 1, 'subject_id' => 1]],
            ['name' => 'idx_user', 'key' => ['user_id' => 1]],
            ['name' => 'idx_created_at', 'key' => ['created_at' => 1]],
        ]);
    }

    public function down(): void
    {
        MongoSchema::dropCollection('activity_log');
    }
};
