<?php

namespace Tests\Feature;

use App\Services\GitHubMetricsService;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Tests for GitHubMetricsService. Uses Http::fake to stub GitHub API
 * responses — no network calls, no MongoDB.
 */
class GitHubMetricsServiceTest extends TestCase
{
    /** @var GitHubMetricsService */
    protected $service;

    protected function setUp(): void
    {
        parent::setUp();

        config()->set('services.github.username', 'testuser');
        config()->set('services.github.token', 'test-token');

        $this->service = new GitHubMetricsService();
    }

    public function test_is_configured_returns_true_when_username_is_set(): void
    {
        $this->assertTrue($this->service->isConfigured());
    }

    public function test_is_configured_returns_false_when_username_is_empty(): void
    {
        config()->set('services.github.username', null);

        $this->assertFalse($this->service->isConfigured());
    }

    public function test_fetch_metrics_returns_repo_count_and_total_commits(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([
                ['name' => 'repo1', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo1'],
                ['name' => 'repo2', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo2'],
                ['name' => 'forked', 'fork' => true, 'private' => false, 'full_name' => 'other/forked'],
                ['name' => 'secret', 'fork' => false, 'private' => true, 'full_name' => 'testuser/secret'],
            ], 200),
            'api.github.com/search/commits*' => Http::response([
                'total_count' => 75,
            ], 200),
        ]);

        $metrics = $this->service->fetchMetrics();

        $this->assertSame(2, $metrics['ownRepoCount']);
        $this->assertSame(150, $metrics['total']); // 75 commits per repo × 2 repos
    }

    public function test_fetch_metrics_handles_pagination(): void
    {
        Http::fake([
            // Page 1 — full page (100 items), plus a fork that should be filtered.
            'api.github.com/users/testuser/repos?per_page=100&page=1*' => Http::response(
                array_fill(0, 100, ['name' => 'repo', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo']),
                200
            ),
            // Page 2 — partial page (3 items), ends pagination.
            'api.github.com/users/testuser/repos?per_page=100&page=2*' => Http::response([
                ['name' => 'repo-a', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-a'],
                ['name' => 'repo-b', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-b'],
                ['name' => 'repo-c', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo-c'],
            ], 200),
            'api.github.com/search/commits*' => Http::response(['total_count' => 10], 200),
        ]);

        $metrics = $this->service->fetchMetrics();

        // 100 (page 1, all non-fork) + 3 (page 2) = 103 own repos.
        $this->assertSame(103, $metrics['ownRepoCount']);
        $this->assertSame(1030, $metrics['total']); // 10 × 103
    }

    public function test_fetch_metrics_returns_zeros_when_repo_list_is_empty(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([], 200),
        ]);

        $metrics = $this->service->fetchMetrics();

        $this->assertSame(0, $metrics['ownRepoCount']);
        $this->assertSame(0, $metrics['total']);
    }

    public function test_fetch_metrics_handles_github_rate_limit_gracefully(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([], 200),
            'api.github.com/search/commits*' => Http::response(['message' => 'rate limit exceeded'], 403),
        ]);

        $metrics = $this->service->fetchMetrics();

        $this->assertSame(0, $metrics['ownRepoCount']);
        $this->assertSame(0, $metrics['total']);
    }

    public function test_fetch_metrics_handles_repo_fetch_failure(): void
    {
        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response(['message' => 'server error'], 500),
        ]);

        $metrics = $this->service->fetchMetrics();

        $this->assertSame(0, $metrics['ownRepoCount']);
        $this->assertSame(0, $metrics['total']);
    }

    public function test_fetch_metrics_handles_missing_token(): void
    {
        config()->set('services.github.token', null);

        Http::fake([
            'api.github.com/users/testuser/repos*' => Http::response([
                ['name' => 'repo1', 'fork' => false, 'private' => false, 'full_name' => 'testuser/repo1'],
            ], 200),
            'api.github.com/search/commits*' => Http::response(['total_count' => 5], 200),
        ]);

        $metrics = $this->service->fetchMetrics();

        $this->assertSame(1, $metrics['ownRepoCount']);
        $this->assertSame(5, $metrics['total']);
    }

    public function test_fetch_metrics_sends_auth_token_when_configured(): void
    {
        Http::fake(function ($request) {
            $this->assertStringContainsString('token test-token', $request->header('Authorization')[0] ?? '');

            return Http::response([], 200);
        });

        $this->service->fetchMetrics();
    }
}
