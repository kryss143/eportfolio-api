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
        'technologies' => 'array',
        'metrics' => 'array',
        'featured' => 'boolean',
    ];
}
