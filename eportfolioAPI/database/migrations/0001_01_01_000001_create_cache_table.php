<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Cache + locks live on MongoDB (mongodb cache store: _id = key, value,
    // expires_at as UTCDateTime; TTL index self-cleans).
    // (Bug 2: index creation routed through MongoSchema.)
    public function up(): void
    {
        MongoSchema::createCollection('cache', [
            ['name' => 'ttl_expires_at', 'key' => ['expires_at' => 1], 'expireAfterSeconds' => 0],
        ]);

        MongoSchema::createCollection('cache_locks', [
            ['name' => 'ttl_expires_at', 'key' => ['expires_at' => 1], 'expireAfterSeconds' => 0],
        ]);
    }

    public function down(): void
    {
        foreach (['cache', 'cache_locks'] as $collection) {
            MongoSchema::dropCollection($collection);
        }
    }
};
