<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Experience extends Model
{

    protected $connection = 'mongodb';
    protected $collection = 'experiences';

    protected $fillable = [
        'position',
        'yearsOfExperience',
        'soloProjects',
        'collabProjects',
    ];

}

?>