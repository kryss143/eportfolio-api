<?php

use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    // Tech-skill chips (label + devicon logo grouped by TechCategory) live on
    // MongoDB, written by ContentSeeder and the admin TechSkillController,
    // which filters by category and sorts by label — both indexed.
    // (Bug 2: index creation routed through MongoSchema.)
    public function up(): void
    {
        MongoSchema::createCollection('tech_skills', [
            ['name' => 'idx_category', 'key' => ['category' => 1]],
            ['name' => 'idx_label', 'key' => ['label' => 1]],
        ]);
    }

    public function down(): void
    {
        MongoSchema::dropCollection('tech_skills');
    }
};
