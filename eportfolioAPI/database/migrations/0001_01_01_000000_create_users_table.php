<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SqlBlueprint;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    // Auth users, sessions and login rate-limit counters live on MongoDB.
    //
    // In the test environment Mongo is pinned to a closed port (phpunit.xml)
    // and the default DB_CONNECTION falls back to sqlite, so the same
    // migration creates the legacy SQL tables there instead — the suite runs
    // against SQL stores and exercises the app's mock-fallback paths.
    public function up(): void
    {
        if ($this->mongoSchema()) {
            Schema::create('users', function (Blueprint $collection) {
                // Schemaless: _id (ObjectId) is implicit; field structure is
                // the model's. Unique email preserves the SQL constraint.
                $collection->index('email', null, null, ['unique' => true, 'name' => 'uniq_email']);
            });

            Schema::create('password_reset_tokens', function (Blueprint $collection) {
                //
            });

            Schema::create('sessions', function (Blueprint $collection) {
                // Written by the mongodb session handler: _id (session id),
                // user_id, ip_address, user_agent, payload, last_activity.
            });

            Schema::create('rate_limits', function (Blueprint $collection) {
                // key (unique) + expires_at (UTCDateTime), self-cleaning.
                $collection->index('key', null, null, ['unique' => true, 'name' => 'uniq_key']);
                $collection->index('expires_at', null, null, ['expireAfterSeconds' => 0, 'name' => 'ttl_expires_at']);
            });

            return;
        }

        // ---- Legacy SQL schema (test fallback) --------------------------------

        Schema::create('users', function (SqlBlueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });

        Schema::create('password_reset_tokens', function (SqlBlueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (SqlBlueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });

        Schema::create('rate_limits', function (SqlBlueprint $table) {
            $table->string('key')->primary();
            $table->unsignedInteger('attempts');
            $table->timestamp('expires_at')->nullable()->index();
        });
    }

    public function down(): void
    {
        foreach (['users', 'password_reset_tokens', 'sessions', 'rate_limits'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    /** True when the schema should be built on MongoDB (runtime); false in
     * the test env, where Mongo is pinned offline and sqlite serves the
     * legacy schema. Memoized by MongoProbe: one ping per command run. */
    private function mongoSchema(): bool
    {
        return ! app()->environment('testing') && \App\Support\MongoProbe::available();
    }
};
