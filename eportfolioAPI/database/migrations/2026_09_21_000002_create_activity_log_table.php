<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SqlBlueprint;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    // Activity log lives on MongoDB (written by ActivityObserver: user_id,
    // subject_type, subject_id, event, old_values/new_values, timestamps).
    // In the test env (Mongo offline, sqlite default) the legacy SQL table
    // is created instead.
    public function up(): void
    {
        if ($this->mongoSchema()) {
            Schema::create('activity_log', function (Blueprint $collection) {
                $collection->index('event', null, null, ['name' => 'idx_event']);
                $collection->index(['subject_type', 'subject_id'], null, null, ['name' => 'idx_subject']);
                $collection->index('user_id', null, null, ['name' => 'idx_user']);
                $collection->index('created_at', null, null, ['name' => 'idx_created_at']);
            });

            return;
        }

        // ---- Legacy SQL schema (test fallback) --------------------------------

        Schema::create('activity_log', function (SqlBlueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('subject_type');
            $table->string('subject_id')->nullable();
            $table->string('event'); // created, updated, deleted
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->timestamps();

            $table->index(['subject_type', 'subject_id']);
            $table->index('event');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }

    private function mongoSchema(): bool
    {
        return ! app()->environment('testing') && \App\Support\MongoProbe::available();
    }
};
