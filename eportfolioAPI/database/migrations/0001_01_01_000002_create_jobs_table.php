<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Queue lives on MongoDB (mongodb queue driver: _id = uuid, queue,
    // payload, attempts, reserved_at, available_at, created_at).
    // (Bug 2: index creation routed through MongoSchema.)
    public function up(): void
    {
        MongoSchema::createCollection('jobs', [
            ['name' => 'idx_queue_available_at', 'key' => ['queue' => 1, 'available_at' => 1]],
        ]);

        MongoSchema::createCollection('job_batches', [
            ['name' => 'uniq_batch_id', 'key' => ['batch_id' => 1], 'unique' => true],
        ]);

        MongoSchema::createCollection('failed_jobs', [
            // Failed driver "database-uuids" writes: id (uuid), uuid,
            // connection, queue, payload, exception, failed_at.
            ['name' => 'uniq_uuid', 'key' => ['uuid' => 1], 'unique' => true],
            ['name' => 'idx_failed_at', 'key' => ['failed_at' => 1]],
        ]);
    }

    public function down(): void
    {
        foreach (['jobs', 'job_batches', 'failed_jobs'] as $collection) {
            MongoSchema::dropCollection($collection);
        }
    }
};
