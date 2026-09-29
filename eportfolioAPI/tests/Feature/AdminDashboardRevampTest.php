<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Concerns\CleansMongoCollections;
use Tests\TestCase;

/**
 * Smoke tests for the admin-dashboard revamp (2026-09-28): every admin page
 * must render through the new utility-based layout/components with no Blade
 * errors. Runs against the live testing cluster
 * (MONGODB_DATABASE=eportfolio_testing) with collections dropped per test —
 * the empty states render from genuinely empty collections.
 */
class AdminDashboardRevampTest extends TestCase
{
    use CleansMongoCollections;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanMongoCollections();
        $this->artisan('migrate', ['--force' => true]);
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
        // The sr-only caption lives in the chart branch, which renders only
        // when at least one tech skill exists; seed one so it does.
        \App\Models\TechSkill::create([
            'category' => \App\Enums\TechCategory::Backend,
            'logo' => './devicons/php-original.svg',
            'label' => 'PHP',
        ]);

        $response = $this->actingAs($this->admin())->get('/admin');

        $response->assertOk();
        $response->assertSee('Tech skills per category', false); // sr-only data table caption
    }
}
