<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Skill extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'skills';

    protected $fillable = [
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
}
