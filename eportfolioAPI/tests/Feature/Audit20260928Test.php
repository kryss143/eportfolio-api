<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\Project;
use App\Observers\ActivityObserver;
use MongoDB\Laravel\Eloquent\Model;
use Tests\Concerns\CleansMongoCollections;
use Tests\TestCase;

/**
 * Regression tests for the 2026-09-28 app/ audit (bugs-reported/AUDIT-app-2026-09-28.md).
 *
 * Runs against the live testing cluster (MONGODB_DATABASE=eportfolio_testing),
 * with the same real save lifecycle the original suite approximated through a
 * SQLite stand-in model. The stand-in is gone with SQLite itself.
 */
class Audit20260928Test extends TestCase
{
    use CleansMongoCollections;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanMongoCollections();
        $this->artisan('migrate', ['--force' => true]);
    }

    // ------------------------------------------------------------------
    // F1 — a real save with array/json-cast attributes must not crash,
    //      and must log only the actually-changed keys
    // ------------------------------------------------------------------

    public function test_save_with_json_cast_attributes_logs_changed_keys_only(): void
    {
        $model = new class extends Model
        {
            protected $table = 'audit_test_projects';

            protected $connection = 'mongodb';

            protected $guarded = [];

            public $timestamps = true;

            protected $casts = [
                'technologies' => 'json',
                'metrics' => 'json',
            ];
        };

        $class = get_class($model);
        $class::observe(ActivityObserver::class);

        $model->newQuery()->create([
            'title' => 'old title',
            'technologies' => ['PHP', 'Laravel'],
            'metrics' => ['2 role scopes'],
        ]);

        $found = $model->newQuery()->first();
        $found->title = 'new title';
        $found->save();

        $row = ActivityLog::query()
            ->where('subject_type', $class)
            ->where('event', 'updated')
            ->latest('_id')
            ->first();

        $this->assertNotNull($row, 'observer should persist an activity row');

        // ActivityLog casts old_values/new_values to arrays.
        $changes = $row->new_values;
        // Mongo timestamps carry millisecond precision, so updated_at can
        // legitimately bump alongside the real change (sqlite's second
        // precision never bumped it). Everything else must be exactly title.
        $this->assertEqualsCanonicalizing(
            ['title'],
            array_values(array_diff(array_keys($changes), ['updated_at'])),
            'only actually-changed keys should be logged (updated_at may bump)'
        );
        $this->assertSame('new title', $changes['title']);

        $old = $row->old_values;
        $this->assertSame('old title', $old['title'], 'old_values must be the pre-update snapshot');
    }

    public function test_save_without_changes_logs_nothing(): void
    {
        $model = new class extends Model
        {
            protected $table = 'audit_test_projects';

            protected $connection = 'mongodb';

            protected $guarded = [];

            public $timestamps = true;

            protected $casts = [
                'technologies' => 'json',
                'metrics' => 'json',
            ];
        };

        $class = get_class($model);
        $class::observe(ActivityObserver::class);

        $model->newQuery()->create(['title' => 'same', 'technologies' => ['a']]);

        $before = ActivityLog::count();
        $found = $model->newQuery()->first();
        $found->save(); // no attribute changes

        $this->assertSame($before, ActivityLog::count(), 'no-op saves must not create activity rows');
    }

    // ------------------------------------------------------------------
    // F1b — the real content models must observe through the real lifecycle
    // (Project has the json casts that crashed the observer pre-fix)
    // ------------------------------------------------------------------

    public function test_real_project_update_logs_activity(): void
    {
        $project = Project::create([
            'title' => 'Audit Fixture',
            'description' => 'created by Audit20260928Test',
            'status' => 'built',
            'featured' => false,
        ]);

        $this->assertSame(1, ActivityLog::where('event', 'created')->count());

        $project->update(['title' => 'Audit Fixture Renamed']);

        $row = ActivityLog::where('event', 'updated')->latest('_id')->first();
        $this->assertNotNull($row);
        // Mongo timestamps bump updated_at on every save (ms precision).
        $this->assertEqualsCanonicalizing(
            ['title'],
            array_values(array_diff(array_keys($row->new_values), ['updated_at'])),
        );
        $this->assertSame('Audit Fixture Renamed', $row->new_values['title']);
    }

    // ------------------------------------------------------------------
    // F2 — drafts (date IS NULL) are never public on the API
    // ------------------------------------------------------------------

    public function test_blog_index_excludes_drafts_by_default(): void
    {
        // Seed one published and one draft post through the real model.
        Blog::create([
            'title' => 'Published post',
            'slug' => 'published-post',
            'excerpt' => 'visible',
            'date' => '2024-03-15',
            'readTime' => '5 min read',
            'content' => '<p>visible</p>',
        ]);
        Blog::create([
            'title' => 'Draft post',
            'slug' => 'draft-post',
            'excerpt' => 'hidden',
            'date' => null,
            'readTime' => '5 min read',
            'content' => '<p>hidden</p>',
        ]);

        $response = $this->getJson('/api/v1/blogs');

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertContains('published-post', $slugs);
        $this->assertNotContains('draft-post', $slugs, 'drafts (date IS NULL) must never be public by default');
    }

    public function test_blog_show_serves_live_blog_by_slug(): void
    {
        Blog::create([
            'title' => 'Published post',
            'slug' => 'published-post',
            'excerpt' => 'visible',
            'date' => '2024-03-15',
            'readTime' => '5 min read',
            'content' => '<p>visible</p>',
        ]);

        $response = $this->getJson('/api/v1/blogs/published-post');

        $response->assertOk()->assertJsonPath('data.slug', 'published-post');
    }

    public function test_blog_index_honors_explicit_draft_filter(): void
    {
        Blog::create([
            'title' => 'Published post',
            'slug' => 'published-post',
            'excerpt' => 'visible',
            'date' => '2024-03-15',
            'readTime' => '5 min read',
            'content' => '<p>visible</p>',
        ]);

        $response = $this->getJson('/api/v1/blogs?status=draft');

        $response->assertOk();
        $this->assertCount(0, $response->json('data'));
    }

    // ------------------------------------------------------------------
    // F3 — the API's mock fallback must still engage on a real outage
    // ------------------------------------------------------------------

    public function test_blog_api_falls_back_to_mock_when_mongo_is_down(): void
    {
        $this->pinMongoDown();

        $response = $this->getJson('/api/v1/blogs');

        $response->assertOk();
        $this->assertNotEmpty(
            $response->json('data'),
            'mock fallback must engage when the DSN points at a closed port'
        );
    }
}
