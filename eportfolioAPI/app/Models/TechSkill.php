<?php

namespace App\Models;

use App\Enums\TechCategory;
use MongoDB\Laravel\Eloquent\Model;

class TechSkill extends Model
{

    protected $connection = 'mongodb';
    protected $collection = 'skills';

    protected $fillable = [
        'category',
        'logo',
        'label',
    ];

    protected $casts = [
        'category' => TechCategory::class,
    ];

}

?>