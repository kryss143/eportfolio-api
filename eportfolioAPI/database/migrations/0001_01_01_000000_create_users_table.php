<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Auth users, sessions and login rate-limit counters live on MongoDB.
    // Schemaless: _id (ObjectId) is implicit; field structure is the model's.
    // Unique email preserves the SQL constraint's uniqueness guarantee.
    // (Bug 2: index creation routed through MongoSchema.)
    public function up(): void
    {
        MongoSchema::createCollection('users', [
            ['name' => 'uniq_email', 'key' => ['email' => 1], 'unique' => true],
        ]);

        MongoSchema::createCollection('password_reset_tokens');

        MongoSchema::createCollection('sessions');

        MongoSchema::createCollection('rate_limits', [
            ['name' => 'uniq_key', 'key' => ['key' => 1], 'unique' => true],
            ['name' => 'ttl_expires_at', 'key' => ['expires_at' => 1], 'expireAfterSeconds' => 0],
        ]);
    }

    public function down(): void
    {
        foreach (['users', 'password_reset_tokens', 'sessions', 'rate_limits'] as $collection) {
            MongoSchema::dropCollection($collection);
        }
    }
};
