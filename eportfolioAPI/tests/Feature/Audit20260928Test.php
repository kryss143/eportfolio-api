<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Observers\ActivityObserver;
use App\Services\MockDataService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Regression tests for the 2026-09-28 app/ audit (bugs-reported/AUDIT-app-2026-09-28.md).
 *
 * phpunit.xml pins MONGODB_URI to a closed port, so Mongo is deterministically
 * unavailable here: API endpoints exercise their mock-fallback paths, and
 * ActivityLog lands on the in-memory SQLite connection.
 */
class Audit20260928Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // In-memory SQLite starts empty every test; the migration set (all
        // pinned to the sqlite connection) creates users/cache/jobs/activity_log.
        $this->artisan('migrate', ['--force' => true]);

        // Local stand-in for the Mongo content models: vanilla Eloquent stores
        // 'json' casts as JSON-encoded strings in the raw attributes and decodes
        // them for getOriginal() — exactly the storage shape that crashed the
        // observer's array_diff_assoc() (audit F1).
        Schema::dropIfExists('audit_test_projects');
        Schema::create('audit_test_projects', function ($t) {
            $t->id();
            $t->string('title')->nullable();
            $t->text('technologies')->nullable();
            $t->text('metrics')->nullable();
            $t->timestamps();
        });
    }

    protected function tearDown(): void
    {
        Schema::dropIfExists('audit_test_projects');

        parent::tearDown();
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
            ->latest('id')
            ->first();

        $this->assertNotNull($row, 'observer should persist an activity row');

        // ActivityLog casts old_values/new_values to arrays.
        $changes = $row->new_values;
        $this->assertSame(['title'], array_keys($changes), 'only actually-changed keys should be logged');
        $this->assertSame('new title', $changes['title']);

        $old = $row->old_values;
        $this->assertSame('old title', $old['title'], 'old_values must be the pre-update snapshot');
    }

    public function test_save_without_changes_logs_nothing(): void
    {
        $model = new class extends Model
        {
            protected $table = 'audit_test_projects';

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
    // F2 — drafts (date IS NULL) are never public on the API
    // ------------------------------------------------------------------

    public function test_blog_index_mock_path_excludes_drafts_by_default(): void
    {
        $mock = MockDataService::get('blogs');
        $this->assertNotEmpty($mock, 'mock blogs must exist for the fallback path');
        $this->assertTrue(
            collect($mock)->every(fn ($b) => ! empty($b['date'])),
            'mock fixture currently has no drafts; this test pins the default filter'
        );

        $response = $this->getJson('/api/v1/blogs');

        $response->assertOk();
        $slugs = collect($response->json('data'))->pluck('slug');
        $this->assertContains($mock[0]['slug'], $slugs);
    }

    public function test_blog_show_mock_path_serves_mock_blog_by_slug(): void
    {
        $slug = MockDataService::get('blogs')[0]['slug'];

        $response = $this->getJson("/api/v1/blogs/{$slug}");

        $response->assertOk()->assertJsonPath('data.slug', $slug);
    }

    public function test_blog_index_mock_path_honors_explicit_draft_filter(): void
    {
        $response = $this->getJson('/api/v1/blogs?status=draft');

        $response->assertOk();
        // No mock drafts exist; the explicit draft filter must yield an empty set.
        $this->assertCount(0, $response->json('data'));
    }
}
