<?php

namespace Database\Seeders;

use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Seeder;

/**
 * Seeds a handful of representative activity_log rows so the admin
 * Activity feed (ActivityController) and dashboard "recent activity" panel
 * are not empty on a fresh install.
 *
 * Every other table in the app gets seeded by AdminUserSeeder or
 * ContentSeeder; activity_log was the only one with no seeder — entries
 * only accumulated organically as admins edited content. These sample rows
 * use the same shape ActivityObserver writes (user_id, subject morph,
 * event, old/new values), so the UI renders them identically.
 */
class ActivityLogSeeder extends Seeder
{
    public function run(): void
    {
        ActivityLog::truncate();

        $admin = User::query()->orderBy('id')->first();

        $project = Project::query()->orderBy('id')->first();
        $blog = Blog::query()->orderBy('id')->first();

        $entries = [
            [
                'subject_type' => Project::class,
                'subject_id' => $project?->getKey(),
                'event' => 'created',
                'old_values' => null,
                'new_values' => $project ? ['title' => $project->title, 'status' => $project->status?->value ?? $project->status] : null,
            ],
            [
                'subject_type' => Project::class,
                'subject_id' => $project?->getKey(),
                'event' => 'updated',
                'old_values' => $project ? ['featured' => false] : null,
                'new_values' => $project ? ['featured' => true] : null,
            ],
            [
                'subject_type' => Blog::class,
                'subject_id' => $blog?->getKey(),
                'event' => 'created',
                'old_values' => null,
                'new_values' => $blog ? ['title' => $blog->title, 'slug' => $blog->slug] : null,
            ],
            [
                'subject_type' => Blog::class,
                'subject_id' => $blog?->getKey(),
                'event' => 'updated',
                'old_values' => $blog ? ['title' => $blog->title] : null,
                'new_values' => $blog ? ['title' => $blog->title] : null,
            ],
        ];

        foreach ($entries as $entry) {
            ActivityLog::create([
                'user_id' => $admin?->getKey(),
                ...$entry,
            ]);
        }
    }
}
