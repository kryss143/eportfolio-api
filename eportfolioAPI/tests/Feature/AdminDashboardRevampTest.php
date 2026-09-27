<?php

namespace Tests\Feature;

use App\Models\User;
use App\Support\MongoProbe;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CleansMongoCollections;
use Tests\TestCase;

/**
 * Smoke tests for the admin-dashboard revamp (2026-09-28): every admin page
 * must render through the new utility-based layout/components with no Blade
 * errors. Mongo is pinned to a closed port (phpunit.xml), so these exercise
 * the degraded mock-data path — exactly the path the new empty/pill states
 * were designed for.
 */
class AdminDashboardRevampTest extends TestCase
{
    // In tests the default connection is sqlite (:memory:) and the schema
    // migrations build the legacy SQL tables there (Mongo is pinned offline);
    // RefreshDatabase migrates/resets that store per test.
    use RefreshDatabase;
    use CleansMongoCollections;

    protected function setUp(): void
    {
        parent::setUp();

        // No-op while Mongo is pinned offline; keeps the suite safe if the
        // pin is ever lifted to test against a live MongoDB.
        $this->cleanMongoCollections();
    }

    private function admin(): User
    {
        return User::factory()->create(['is_admin' => true]);
    }

    public function test_dashboard_renders_new_layout(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Overview');
        $response->assertSee('Key metrics');
        $response->assertSee('Needs attention');
        $response->assertSee('Tech skills by category');
        $response->assertSee('Recent blog posts');
        $response->assertSee('Recent activity');
        $response->assertSee('aria-label="Main navigation"', false);

        // The redesigned shell renders content exactly once.
        $this->assertSame(
            1,
            substr_count($response->getContent(), 'id="main"'),
            'main landmark must appear exactly once (no duplicated mobile column)'
        );
    }

    public function test_index_pages_render_new_tables(): void
    {
        $admin = $this->admin();

        foreach ([
            '/admin/projects' => 'Add project',
            '/admin/blogs' => 'Add post',
            '/admin/tech-skills' => 'Add skill',
            '/admin/experiences' => 'Add experience',
            '/admin/metrics' => 'Add metric',
            '/admin/activity' => 'Activity Log',
        ] as $url => $marker) {
            $response = $this->actingAs($admin)->get($url);

            $response->assertOk();
            $response->assertSee($marker);
        }
    }

    public function test_dashboard_chart_has_accessible_alternative(): void
    {
        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Tech skills per category', false); // sr-only data table caption
    }
}
