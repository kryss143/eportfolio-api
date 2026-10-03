<?php

namespace App\Services;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GitHubMetricsService
{
    protected string $username;

    protected ?string $token;

    protected int $perPage = 100;

    protected int $requestDelayMs = 500;

    public function __construct()
    {
        $this->username = (string) config('services.github.username');
        $this->token = config('services.github.token')
            ? (string) config('services.github.token')
            : null;
    }

    /**
     * Fetch live metrics from the GitHub API.
     *
     * @return array{ownRepoCount: int, total: int}
     */
    public function fetchMetrics(): array
    {
        $ownRepoCount = 0;
        $totalCommits = 0;

        $repos = $this->fetchAllRepos();

        foreach ($repos as $repo) {
            if (($repo['fork'] ?? false) || ($repo['private'] ?? false)) {
                continue;
            }

            $ownRepoCount++;
            $totalCommits += $this->countCommitsForRepo($repo['full_name']);

            // Avoid hammering the rate limit (30 search reqs/min authenticated).
            usleep($this->requestDelayMs * 1000);
        }

        return [
            'ownRepoCount' => $ownRepoCount,
            'total' => $totalCommits,
        ];
    }

    /**
     * Fetch all public repositories for the configured user (paginated).
     *
     * @return array<int, array>
     */
    protected function fetchAllRepos(): array
    {
        $repos = [];
        $page = 1;

        do {
            $response = $this->githubGet('users/'.urlencode($this->username).'/repos', [
                'per_page' => $this->perPage,
                'page' => $page,
                'type' => 'public',
                'sort' => 'updated',
            ]);

            if ($response->failed() || $response->status() >= 400) {
                Log::warning('GitHub repos request failed', [
                    'status' => $response->status(),
                    'body' => $response->body(),
                ]);

                break;
            }

            $pageRepos = $response->json() ?? [];

            if (! is_array($pageRepos) || empty($pageRepos)) {
                break;
            }

            $repos = array_merge($repos, $pageRepos);
            $page++;
        } while (count($pageRepos) === $this->perPage);

        return $repos;
    }

    /**
     * Count commits authored by the configured user in a given repo.
     *
     * Uses the GitHub Search API. The search endpoint caps total_count at
     * 1000 results; we treat total_count as the authoritative commit count.
     */
    protected function countCommitsForRepo(string $fullRepoName): int
    {
        $response = $this->githubGet('search/commits', [
            'q' => 'author:'.$this->username.' repo:'.$fullRepoName,
            'per_page' => 1,
        ]);

        if ($response->failed() || $response->status() >= 400) {
            Log::warning("GitHub commit search failed for {$fullRepoName}", [
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return 0;
        }

        $body = $response->json();

        if (! is_array($body)) {
            return 0;
        }

        return (int) ($body['total_count'] ?? 0);
    }

    /**
     * Issue a GET to the GitHub API with auth headers.
     */
    protected function githubGet(string $uri, array $query = []): Response
    {
        $builder = Http::acceptJson();

        if (! empty($this->token)) {
            $builder = $builder->withToken($this->token);
        }

        return $builder->timeout(30)->get('https://api.github.com/'.$uri, $query);
    }

    /**
     * Check whether GitHub credentials are configured.
     */
    public function isConfigured(): bool
    {
        return ! empty($this->username);
    }
}
