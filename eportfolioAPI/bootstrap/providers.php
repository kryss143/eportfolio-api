<?php

use App\Providers\AppServiceProvider;
use MongoDB\Laravel\MongoDBServiceProvider;

$providers = [
    AppServiceProvider::class,
];

// Only load MongoDB provider when the extension is installed
if (extension_loaded('mongodb')) {
    $providers[] = MongoDBServiceProvider::class;
}

return $providers;
