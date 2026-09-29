<?php

use App\Models\Skill;
use App\Support\MongoSchema;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // Grouped skill lists (proficient/familiar/...) are a SINGLETON document.
    //
    // Audit post-mongo Bug 5 (2026-09-28): the collection had no uniqueness
    // and Skill::firstOrCreate([]) matched on nothing, so two concurrent
    // admin saves could create duplicate documents — after which
    // Skill::first() arbitrarily picked one and an admin's edit appeared to
    // silently vanish. A unique index on `key` plus one pre-created document
    // makes create-once semantics race-free. (A custom string _id was tried
    // first and rejected: the package's Eloquent layer treats _id as an
    // ObjectId and inserts fresh ObjectIds anyway — verified live.)
    public function up(): void
    {
        MongoSchema::createCollection('skills', [
            ['name' => 'uniq_key', 'key' => ['key' => 1], 'unique' => true],
        ]);

        // Pre-create the singleton document; the unique index guarantees no
        // racing firstOrCreate can ever duplicate it.
        DB::connection('mongodb')->getDatabase()->selectCollection('skills')->updateOne(
            ['key' => Skill::SINGLETON_KEY],
            ['$setOnInsert' => [
                'key' => Skill::SINGLETON_KEY,
                'proficient' => [],
                'familiar' => [],
                'authentication' => [],
                'architecture' => [],
                'toolsPlatforms' => [],
                'practices' => [],
                'ai' => [],
            ]],
            ['upsert' => true],
        );
    }

    public function down(): void
    {
        MongoSchema::dropCollection('skills');
    }
};
