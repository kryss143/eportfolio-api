<?php

namespace App\Models;

use MongoDB\Laravel\Eloquent\Model;

class Metric extends Model
{
    protected $connection = 'mongodb';

    protected $collection = 'metrics';

    protected $fillable = [
        'label',
        'value',
        'suffix',
        'metricDescription',
    ];
}
