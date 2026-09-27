<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SqlBlueprint;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    // Queue lives on MongoDB (mongodb queue driver: _id = uuid, queue,
    // payload, attempts, reserved_at, available_at, created_at). In the test
    // env (Mongo offline, sqlite default) the legacy SQL tables are created.
    public function up(): void
    {
        if ($this->mongoSchema()) {
            Schema::create('jobs', function (Blueprint $collection) {
                $collection->index(['queue', 'available_at'], null, null, ['name' => 'idx_queue_available_at']);
            });

            Schema::create('job_batches', function (Blueprint $collection) {
                $collection->index('batch_id', null, null, ['unique' => true, 'name' => 'uniq_batch_id']);
            });

            Schema::create('failed_jobs', function (Blueprint $collection) {
                // Failed driver "database-uuids" writes: id (uuid), uuid,
                // connection, queue, payload, exception, failed_at.
                $collection->index('uuid', null, null, ['unique' => true, 'name' => 'uniq_uuid']);
                $collection->index('failed_at', null, null, ['name' => 'idx_failed_at']);
            });

            return;
        }

        // ---- Legacy SQL schema (test fallback) --------------------------------

        Schema::create('jobs', function (SqlBlueprint $table) {
            $table->id();
            $table->string('queue')->index();
            $table->longText('payload');
            $table->unsignedSmallInteger('attempts');
            $table->unsignedInteger('reserved_at')->nullable();
            $table->unsignedInteger('available_at');
            $table->unsignedInteger('created_at');
        });

        Schema::create('job_batches', function (SqlBlueprint $table) {
            $table->string('id')->primary();
            $table->string('name');
            $table->integer('total_jobs');
            $table->integer('pending_jobs');
            $table->integer('failed_jobs');
            $table->longText('failed_job_ids');
            $table->mediumText('options')->nullable();
            $table->integer('cancelled_at')->nullable();
            $table->integer('created_at');
            $table->integer('finished_at')->nullable();
        });

        Schema::create('failed_jobs', function (SqlBlueprint $table) {
            $table->id();
            $table->string('uuid')->unique();
            $table->string('connection');
            $table->string('queue');
            $table->longText('payload');
            $table->longText('exception');
            $table->timestamp('failed_at')->useCurrent();

            $table->index(['connection', 'queue', 'failed_at']);
        });
    }

    public function down(): void
    {
        foreach (['jobs', 'job_batches', 'failed_jobs'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function mongoSchema(): bool
    {
        return ! app()->environment('testing') && \App\Support\MongoProbe::available();
    }
};
