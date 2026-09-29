<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Headline stats (Total Projects / Ongoing / Live Demos / Total Commits)
    // live on MongoDB, written by ContentSeeder and the admin MetricController.
    public function up(): void
    {
        // Small, fully-read collection; nothing worth indexing.
        MongoSchema::createCollection('metrics');
    }

    public function down(): void
    {
        MongoSchema::dropCollection('metrics');
    }
};
