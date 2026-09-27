<?php

namespace App\Models;

use App\Enums\ProjectStatus;
use MongoDB\Laravel\Eloquent\Model;

class Project extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'projects';

    protected $fillable = [
        'title',
        'description',
        'technologies',
        'status',
        'githubLink',
        'demoLink',
        'image',
        'outcome',
        'metrics',
        'featured',
    ];

    protected $casts = [
        'status' => ProjectStatus::class,
        // 'json' rather than 'array' (audit F4, 2026-09-28): the MongoDB
        // package stores these as JSON-encoded strings, and 'array' triggers
        // a USER_DEPRECATED on every write. Both casts decode to PHP arrays.
        'technologies' => 'json',
        'metrics' => 'json',
        'featured' => 'boolean',
    ];
}
