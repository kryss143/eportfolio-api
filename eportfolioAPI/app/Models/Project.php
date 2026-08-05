<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Projects extends Model
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
        'technologies' => 'array',
        'metrics' => 'array',
        'featured' => 'boolean',
    ];
}

?>