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
        'proficient' => 'array',
        'familiar' => 'array',
        'authentication' => 'array',
        'architecture' => 'array',
        'toolsPlatforms' => 'array',
        'practices' => 'array',
        'ai' => 'array',
    ];
}

?>