<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MongoProbe;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Regression tests for the r3 audit fixes (bugs-reported/AUDIT-app-2026-09-28-r3.md).
 *
 * phpunit.xml pins MONGODB_URI to a closed port: Mongo is deterministically
 * down, so every endpoint here exercises its degraded/mock path.
 */
class Audit20260928R3Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MongoProbe::flush();
        $this->artisan('migrate', ['--force' => true]);
    }

    protected function tearDown(): void
    {
        MongoProbe::flush();

        parent::tearDown();
    }

    /**
     * The probe stamps these statics when it actually runs; null means the
     * composer never invoked it during the request under test.
     */
    private function probeTimestamps(): array
    {
        return [
            'availableAt' => (new ReflectionProperty(MongoProbe::class, 'availableAt'))->getValue(),
            'writableAt' => (new ReflectionProperty(MongoProbe::class, 'writableAt'))->getValue(),
        ];
    }

    // ------------------------------------------------------------------
    // F2 — the guest login page must not pay for Mongo probes
    // ------------------------------------------------------------------

    public function test_login_page_does_not_trigger_mongo_probes(): void
    {
        $response = $this->get('/admin/login');

        $response->assertOk()->assertSee('Sign in');

        $timestamps = $this->probeTimestamps();
        $this->assertNull($timestamps['availableAt'], 'read probe must not run for the login page');
        $this->assertNull($timestamps['writableAt'], 'write probe must not run for the login page');
    }

    public function test_index_views_still_receive_probe_variables(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $response = $this->actingAs($admin)->get('/admin/projects');

        $response->assertOk();

        $timestamps = $this->probeTimestamps();
        $this->assertNotNull($timestamps['availableAt'], 'index views must still get mongoAvailable');
        $this->assertNotNull($timestamps['writableAt'], 'index views must still get mongoWritable');
    }

    // ------------------------------------------------------------------
    // F3 — landing degraded path must mirror the DB branch's blog
    //      semantics: published-only, newest first
    // ------------------------------------------------------------------

    public function test_landing_degraded_path_orders_mock_blogs_newest_first(): void
    {
        $response = $this->get('/');

        $response->assertOk();

        // Newest mock post (2024-03-15) must render before the oldest
        // (2024-02-20) — the mock fixture is currently in date order, but this
        // pins the sortByDesc parity with the DB path's orderByDesc('date').
        $body = $response->getContent();
        $newest = strpos($body, 'Mar 15, 2024');
        $oldest = strpos($body, 'Feb 20, 2024');

        $this->assertNotFalse($newest, 'newest mock blog must render');
        $this->assertNotFalse($oldest, 'oldest mock blog must render');
        $this->assertLessThan($oldest, $newest, 'blogs must render newest-first on the degraded path');
    }
}
