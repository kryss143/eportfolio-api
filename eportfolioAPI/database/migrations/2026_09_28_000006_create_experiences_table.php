<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // The experience summary document (position, yearsOfExperience,
    // soloProjects, collabProjects) lives on MongoDB, written by
    // ContentSeeder and the admin ExperienceController.
    public function up(): void
    {
        // Single-document collection, always read whole; nothing to index.
        MongoSchema::createCollection('experiences');
    }

    public function down(): void
    {
        MongoSchema::dropCollection('experiences');
    }
};
