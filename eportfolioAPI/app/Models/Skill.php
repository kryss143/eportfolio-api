<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Skill extends Model
{
    /**
     * Fixed key for the singleton record (audit post-mongo Bug 5).
     *
     * Deliberately a normal unique field rather than a custom string _id: the
     * package's Eloquent layer treats _id as an ObjectId (keyType string +
     * convertKey), and hand-set string _ids fight the driver's key handling
     * (verified: firstOrCreate with ['_id' => 'singleton'] inserted a fresh
     * ObjectId document). A unique-indexed `key` field gives the same
     * race-free create-once semantics with zero driver quirks — duplicate
     * racing inserts fail on the unique index, and every read targets the
     * same document via where('key', self::SINGLETON_KEY).
     */
    public const SINGLETON_KEY = 'skills-singleton';

    protected $connection = 'mongodb';

    protected $collection = 'skills';

    protected $fillable = [
        'key',
        'proficient',
        'familiar',
        'authentication',
        'architecture',
        'toolsPlatforms',
        'practices',
        'ai',
    ];

    protected $casts = [
        // 'json' rather than 'array' (audit F4, 2026-09-28): values are stored
        // as JSON-encoded strings by the MongoDB package; 'array' triggers a
        // USER_DEPRECATED on every write. Both casts decode to PHP arrays.
        'proficient' => 'json',
        'familiar' => 'json',
        'authentication' => 'json',
        'architecture' => 'json',
        'toolsPlatforms' => 'json',
        'practices' => 'json',
        'ai' => 'json',
    ];

    /**
     * The one and only skills document (Bug 5). Unique key + upsert: racing
     * requests converge on the same row instead of duplicating it.
     */
    public static function singleton(): self
    {
        return static::firstOrCreate(
            ['key' => self::SINGLETON_KEY],
            [
                'proficient' => [],
                'familiar' => [],
                'authentication' => [],
                'architecture' => [],
                'toolsPlatforms' => [],
                'practices' => [],
                'ai' => [],
            ]
        );
    }
}
