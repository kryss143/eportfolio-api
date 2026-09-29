<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Blog posts live on MongoDB (schemaless documents written by
    // ContentSeeder and the admin BlogController). The slug is looked up by
    // the public API (BlogController::show) and must stay unique — it carries
    // the SQL constraint's job here, same as uniq_email on users.
    // (Bug 2: index creation routed through MongoSchema.)
    public function up(): void
    {
        MongoSchema::createCollection('blogs', [
            ['name' => 'uniq_slug', 'key' => ['slug' => 1], 'unique' => true],
            ['name' => 'idx_date', 'key' => ['date' => 1]],
        ]);
    }

    public function down(): void
    {
        MongoSchema::dropCollection('blogs');
    }
};
