<?php

use App\Http\Controllers\Api\V1\BlogController;
use App\Http\Controllers\Api\V1\ExperienceController;
use App\Http\Controllers\Api\V1\MetricController;
use App\Http\Controllers\Api\V1\ProjectController;
use App\Http\Controllers\Api\V1\SkillController;
use App\Http\Controllers\Api\V1\TechSkillController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/blogs', [BlogController::class, 'index']);
    Route::get('/blogs/{slug}', [BlogController::class, 'show']);

    Route::get('/experiences', [ExperienceController::class, 'index']);

    Route::get('/metrics', [MetricController::class, 'index']);

    Route::get('/projects', [ProjectController::class, 'index']);
    Route::get('/projects/{id}', [ProjectController::class, 'show']);

    Route::get('/skills', [SkillController::class, 'index']);

    Route::get('/tech-skills', [TechSkillController::class, 'index']);
    Route::get('/tech-skills/{category}', [TechSkillController::class, 'byCategory']);
});
