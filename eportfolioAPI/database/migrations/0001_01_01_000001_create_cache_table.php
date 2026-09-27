<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint as SqlBlueprint;
use Illuminate\Support\Facades\Schema;
use MongoDB\Laravel\Schema\Blueprint;

return new class extends Migration
{
    // Cache + locks live on MongoDB (mongodb cache store: _id = key, value,
    // expires_at as UTCDateTime; TTL index self-cleans). In the test env
    // (Mongo offline, sqlite default) the legacy SQL tables are created.
    public function up(): void
    {
        if ($this->mongoSchema()) {
            Schema::create('cache', function (Blueprint $collection) {
                $collection->index('expires_at', null, null, ['expireAfterSeconds' => 0, 'name' => 'ttl_expires_at']);
            });

            Schema::create('cache_locks', function (Blueprint $collection) {
                $collection->index('expires_at', null, null, ['expireAfterSeconds' => 0, 'name' => 'ttl_expires_at']);
            });

            return;
        }

        // ---- Legacy SQL schema (test fallback) --------------------------------

        Schema::create('cache', function (SqlBlueprint $table) {
            $table->string('key')->primary();
            $table->mediumText('value');
            $table->bigInteger('expiration')->index();
        });

        Schema::create('cache_locks', function (SqlBlueprint $table) {
            $table->string('key')->primary();
            $table->string('owner');
            $table->bigInteger('expiration')->index();
        });
    }

    public function down(): void
    {
        foreach (['cache', 'cache_locks'] as $table) {
            Schema::dropIfExists($table);
        }
    }

    private function mongoSchema(): bool
    {
        return ! app()->environment('testing') && \App\Support\MongoProbe::available();
    }
};
