<?php

use App\Http\Controllers\Admin\ActivityController;
use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\ExperienceController;
use App\Http\Controllers\Admin\MetricController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\SkillController;
use App\Http\Controllers\Admin\TechSkillController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\LandingController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('home');

// Admin auth (guest)
Route::middleware('guest')->prefix('admin')->name('admin.')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    // Bug #6 (2026-09-25): rate-limit credential attempts — 5 per minute per
    // IP; Auth::attempt() does no throttling of its own.
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:5,1');
});

// Admin authenticated
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Resource CRUD
    Route::resource('projects', ProjectController::class);
    Route::post('projects/{project}/toggle-featured', [ProjectController::class, 'toggleFeatured'])->name('projects.toggle-featured');
    Route::post('projects/bulk-delete', [ProjectController::class, 'bulkDelete'])->name('projects.bulk-delete');

    Route::resource('blogs', BlogController::class);
    Route::post('blogs/{blog}/toggle-publish', [BlogController::class, 'togglePublish'])->name('blogs.toggle-publish');
    Route::post('blogs/bulk-delete', [BlogController::class, 'bulkDelete'])->name('blogs.bulk-delete');

    Route::resource('experiences', ExperienceController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    Route::resource('metrics', MetricController::class)->only(['index', 'create', 'store', 'edit', 'update']);

    // Skills is a singleton (one settings-style record, no per-item routes)
    Route::get('skills', [SkillController::class, 'index'])->name('skills.index');
    Route::get('skills/edit', [SkillController::class, 'edit'])->name('skills.edit');
    Route::put('skills', [SkillController::class, 'update'])->name('skills.update');

    Route::resource('tech-skills', TechSkillController::class);
    Route::post('tech-skills/bulk-delete', [TechSkillController::class, 'bulkDelete'])->name('tech-skills.bulk-delete');

    // Users
    Route::get('users', [UserController::class, 'index'])->name('users.index');
    Route::get('users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::post('users/{user}/toggle-admin', [UserController::class, 'toggleAdmin'])->name('users.toggle-admin');
    Route::delete('users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

    // Activity log
    Route::get('activity', [ActivityController::class, 'index'])->name('activity.index');
});
