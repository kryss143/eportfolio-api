<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Portfolio projects live on MongoDB (schemaless documents written by
    // ContentSeeder and the admin ProjectController). Indexes mirror the
    // query patterns the controllers actually use (Bug 2: via MongoSchema).
    public function up(): void
    {
        MongoSchema::createCollection('projects', [
            ['name' => 'idx_status', 'key' => ['status' => 1]],
            ['name' => 'idx_featured', 'key' => ['featured' => 1]],
        ]);
    }

    public function down(): void
    {
        MongoSchema::dropCollection('projects');
    }
};
