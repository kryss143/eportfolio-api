<?php

namespace Tests\Feature;

use App\Models\Metric;
use Illuminate\Support\Facades\Http;
use Tests\Concerns\CleansMongoCollections;
use Tests\TestCase;

/**
 * Tests for the metrics:update command. Runs against the real MongoDB testing
 * cluster (eportfolio_testing), with GitHub responses stubbed via Http::fake.
 */
class UpdateMetricsCommandTest extends TestCase
{
    use CleansMongoCollections;

    protected function setUp(): void
    {
        parent::setUp();

        $this->cleanMongoCollections();
        $this->artisan('migrate', ['--force' => true]);
        $this->seedMetrics();

        config()->set('services.github.username', 'testuser');
        config()->set('services.github.token', 'test-token');
    }

    /**
     * Seed the metrics collection with the baseline labels the command
     * updates, mirroring ContentSeeder + mock-data.json.
     */
    protected function seedMetrics(): void
    {
        Metric::create([
            'label' => 'Total Projects',
            'value' => 1,
            'suffix' => null,
            'metricDescription' => 'Baseline',
        ]);

        Metric::create([
            'label' => 'Ongoing',
            'value' => 1,
            'suffix' => null,
            'metricDescription' => 'Baseline',
        ]);

        Metric::create([
            'label' => 'Live Demo/s Available',
            'value' => 2,
            'suffix' => null,
            'metricDescription' => 'Baseline',
        ]);

        Metric::create([
            'label' => 'Total Commits',
            'value' => 100,
            'suffix' => '+',
            'metricDescription' => 'Baseline',
        ]);
    }

    public function test_command_updates_metrics_from_github(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([
                ['name' => 'repo-a', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-a'],
                ['name' => 'repo-b', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-b'],
                ['name' => 'forked', 'fork' => true, 'private' => false, 'full_name' => 'other/forked'],
            ], 200),
            'api.github.com/search/commits*' => Http::response(['total_count' => 50], 200),
        ]);

        $this->artisan('metrics:update')
            ->expectsOutput('Updated 2 metric(s) from GitHub:')
            ->expectsOutput('  Total Projects: 2')
            ->expectsOutput('  Total Commits: 100+')
            ->assertExitCode(0);

        $projects = Metric::where('label', 'Total Projects')->first();
        $commits = Metric::where('label', 'Total Commits')->first();

        $this->assertSame(2, $projects->value);
        $this->assertSame(100, $commits->value);
        $this->assertSame('+', $commits->suffix);
    }

    public function test_command_dry_run_does_not_modify_database(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([
                ['name' => 'repo-a', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-a'],
            ], 200),
            'api.github.com/search/commits*' => Http::response(['total_count' => 42], 200),
        ]);

        $this->artisan('metrics:update', ['--dry-run' => true])
            ->expectsOutput('[dry-run] No writes performed.')
            ->assertExitCode(0);

        $projects = Metric::where('label', 'Total Projects')->first();
        $this->assertSame(1, $projects->value, 'dry-run must not modify existing values');
    }

    public function test_command_warns_when_github_not_configured(): void
    {
        config()->set('services.github.username', null);

        $this->artisan('metrics:update')
            ->expectsOutput('GitHub credentials are not configured (GITHUB_USERNAME missing). Nothing to do.')
            ->assertExitCode(0);
    }

    public function test_command_fails_when_mongodb_is_unwritable(): void
    {
        Http::fake();

        $this->pinMongoDown();

        $this->artisan('metrics:update')
            ->expectsOutput('MongoDB is not writable — skipping metric update.')
            ->assertExitCode(1);
    }

    public function test_command_creates_missing_metric_document(): void
    {
        // Remove the "Total Commits" metric — command should recreate it.
        Metric::where('label', 'Total Commits')->delete();

        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([
                ['name' => 'repo-a', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-a'],
            ], 200),
            'api.github.com/search/commits*' => Http::response(['total_count' => 25], 200),
        ]);

        $this->artisan('metrics:update')
            ->expectsOutputToContain('Total Commits: 25+')
            ->assertExitCode(0);

        $commits = Metric::where('label', 'Total Commits')->first();
        $this->assertNotNull($commits, 'command should recreate a missing metric');
        $this->assertSame(25, $commits->value);
        $this->assertSame('+', $commits->suffix);
    }

    public function test_command_does_not_modify_unchanged_metrics(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([
                ['name' => 'repo-a', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-a'],
            ], 200),
            'api.github.com/search/commits*' => Http::response(['total_count' => 75], 200),
        ]);

        $this->artisan('metrics:update')->assertExitCode(0);

        // ONGOING and LIVE_DEMOS are not GitHub-derived — they must stay unchanged.
        $ongoing = Metric::where('label', 'Ongoing')->first();
        $this->assertSame(1, $ongoing->value);

        $liveDemos = Metric::where('label', 'Live Demo/s Available')->first();
        $this->assertSame(2, $liveDemos->value);
    }
}
