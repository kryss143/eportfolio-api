<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Blog;
use App\Models\Project;
use App\Models\Skill;
use App\Models\User;
use App\Support\MongoSchema;
use App\Support\MongoProbe;
use Illuminate\Support\Facades\DB;
use Tests\Concerns\CleansMongoCollections;
use Tests\TestCase;

/**
 * Regression tests for the post-mongo audit fixes
 * (bugs-reported/AUDIT-app-2026-09-28-post-mongo.md).
 *
 * Runs against the live testing cluster (MONGODB_DATABASE=eportfolio_testing).
 * The outage scenario (Bug 1) simulates Mongo being down via a closed-port
 * DSN while keeping the PRODUCTION-shape drivers — that combination is what
 * hid the original bug from the suite.
 */
class AuditPostMongoTest extends TestCase
{
    use CleansMongoCollections;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanMongoCollections();
        $this->artisan('migrate', ['--force' => true]);
    }

    // ------------------------------------------------------------------
    // Bug 3 — a valid non-admin password must be indistinguishable from an
    // invalid one (no credential oracle on /admin/login)
    // ------------------------------------------------------------------

    public function test_non_admin_with_valid_password_gets_generic_error(): void
    {
        User::create([
            'name' => 'Pleb',
            'email' => 'pleb@example.com',
            'password' => 'correct-horse',
            'is_admin' => false,
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'pleb@example.com',
            'password' => 'correct-horse',
        ]);

        $response->assertSessionHasErrors('email');
        $this->assertSame(
            'The provided credentials do not match our records.',
            session('errors')->getBag('default')->first('email'),
            'valid non-admin credentials must NOT be distinguished from invalid ones'
        );
        $this->assertGuest();
    }

    public function test_admin_login_still_works(): void
    {
        User::create([
            'name' => 'Boss',
            'email' => 'boss@example.com',
            'password' => 'super-secret',
            'is_admin' => true,
        ]);

        $this->post('/admin/login', [
            'email' => 'boss@example.com',
            'password' => 'super-secret',
        ])->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    }

    // ------------------------------------------------------------------
    // Bug 1 — production-shape drivers (file session, NOT array): with Mongo
    // down, / and /admin/login must still render (mock-data degraded mode)
    // ------------------------------------------------------------------

    public function test_pages_render_with_production_session_driver_while_mongo_is_down(): void
    {
        // The suite normally runs SESSION_DRIVER=array; force the file driver
        // so this test exercises the shape production actually uses. Before
        // Bug 1 was fixed (SESSION_DRIVER=mongodb), this request 500'd in the
        // session middleware before any controller ran.
        config()->set('session.driver', 'file');

        $this->pinMongoDown();

        $this->get('/')->assertOk();
        $this->get('/admin/login')->assertOk();
    }

    // ------------------------------------------------------------------
    // Bug 2 — MongoSchema::ensureIndexes repairs same-name/different-key
    // drift instead of throwing code 86, and converges to the declared spec
    // ------------------------------------------------------------------

    public function test_ensure_indexes_repairs_drifted_index_spec(): void
    {
        $db = DB::connection('mongodb')->getDatabase();
        $db->selectCollection('drift_test')->drop();

        // Create the drifted state: idx_created_at with a DESCENDING key,
        // while the migration declares ASCENDING under the same name.
        $db->selectCollection('drift_test')->createIndex(['created_at' => -1], ['name' => 'idx_created_at']);

        // Pre-fix: this exact call threw CommandException code 86.
        MongoSchema::ensureIndexes('drift_test', [
            ['name' => 'idx_created_at', 'key' => ['created_at' => 1]],
        ]);

        $indexes = [];
        foreach ($db->selectCollection('drift_test')->listIndexes() as $index) {
            $indexes[$index['name']] = $index['key'];
        }

        $this->assertArrayHasKey('idx_created_at', $indexes);
        $this->assertSame(['created_at' => 1], $indexes['idx_created_at'], 'drifted key must be repaired to the declared spec');

        // Idempotent: a second run must not throw (exact match → skip).
        MongoSchema::ensureIndexes('drift_test', [
            ['name' => 'idx_created_at', 'key' => ['created_at' => 1]],
        ]);

        $db->selectCollection('drift_test')->drop();
    }

    // ------------------------------------------------------------------
    // Bug 4 — blog bulkDelete must leave an audit trail (observer fires)
    // ------------------------------------------------------------------

    public function test_blog_bulk_delete_logs_activity_for_each_document(): void
    {
        $a = Blog::create(['title' => 'A', 'slug' => 'bulk-a', 'excerpt' => 'e', 'readTime' => '1 min', 'content' => 'c']);
        $b = Blog::create(['title' => 'B', 'slug' => 'bulk-b', 'excerpt' => 'e', 'readTime' => '1 min', 'content' => 'c']);

        ActivityLog::where('event', 'deleted')->delete();
        $before = ActivityLog::where('event', 'deleted')->count();

        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->post(route('admin.blogs.bulk-delete'), ['ids' => [(string) $a->_id, (string) $b->_id]])
            ->assertRedirect();

        $this->assertSame(
            $before + 2,
            ActivityLog::where('event', 'deleted')->count(),
            'bulk blog deletion must produce one deleted log per document'
        );
        $this->assertNull(Blog::find($a->_id));
        $this->assertNull(Blog::find($b->_id));
    }

    // ------------------------------------------------------------------
    // Bug 7 — malformed ids are rejected at validation, not passed to the
    // driver
    // ------------------------------------------------------------------

    public function test_bulk_delete_rejects_malformed_ids(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        foreach (['not-an-id', 'ZZZZ', 'a-f0-9-g-h-i-j'] as $junk) {
            $this->actingAs($admin)
                ->post(route('admin.blogs.bulk-delete'), ['ids' => [$junk]])
                ->assertSessionHasErrors('ids.0');
        }
    }

    // ------------------------------------------------------------------
    // Bug 5 — the Skill singleton has a fixed _id; writes always target the
    // same document
    // ------------------------------------------------------------------

    public function test_skill_singleton_uses_fixed_document_id(): void
    {
        $skills = Skill::singleton();
        $this->assertSame(Skill::SINGLETON_KEY, $skills->key);

        // Concurrent-shaped access: two singleton() calls must resolve the
        // SAME document, and a write must not spawn a duplicate.
        $again = Skill::singleton();
        $this->assertSame((string) $skills->_id, (string) $again->_id);

        $skills->update(['proficient' => ['PHP']]);

        $this->assertSame(1, Skill::count(), 'singleton writes must never create a second document');
        $this->assertSame(['PHP'], Skill::singleton()->proficient);
    }

    // ------------------------------------------------------------------
    // Bug 6 — degraded-mode admin search is case-insensitive (parity with
    // the live path's case-insensitive `like`)
    // ------------------------------------------------------------------

    public function test_admin_mock_search_is_case_insensitive(): void
    {
        $this->pinMongoDown();

        request()->merge(['search' => 'GRABCAT']);
        $view = app(\App\Http\Controllers\Admin\ProjectController::class)->index(request());
        $projects = $view->getData()['projects'];

        $this->assertGreaterThan(
            0,
            count($projects->items()),
            'upper-case needle must match mixed-case mock titles (live path parity)'
        );
    }
}
