<?php

namespace Tests\Feature;

use App\Support\MongoProbe;
use Illuminate\Pagination\LengthAwarePaginator;
use MongoDB\Driver\Exception\ConnectionTimeoutException;
use ReflectionProperty;
use Tests\TestCase;

/**
 * Regression tests for the r2 audit fixes (bugs-reported/AUDIT-app-2026-09-28-r2.md).
 *
 * Mongo-outage scenarios simulate the outage by overriding the mongodb DSN to
 * a closed port (per-test) instead of a global phpunit pin — the rest of the
 * suite runs against the live eportfolio_testing database.
 */
class Audit20260928R2Test extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // These tests are all about degraded/offline behaviour: simulate the
        // outage up front. Live-Mongo coverage lives in the other suites.
        $this->pinMongoDown();
    }

    // ------------------------------------------------------------------
    // F1 — withFallback(): empty *filtered* result of a POPULATED live
    //      collection must not be substituted with mock data
    // ------------------------------------------------------------------

    private function fallbackHarness(bool $collectionHasDocuments)
    {
        // Anonymous controller using the real trait, with collectionHasDocuments()
        // overridden so both branches are testable without touching MongoDB.
        return new class($collectionHasDocuments)
        {
            use \App\Http\Controllers\Api\V1\FallbackData;

            public function __construct(private readonly bool $hasDocs) {}

            public function collectionHasDocuments(string $collection): bool
            {
                return $this->hasDocs;
            }

            public function run(callable $dbCallback, string $collection)
            {
                return $this->withFallback($dbCallback, $collection);
            }
        };
    }

    public function test_empty_filtered_result_of_populated_collection_is_not_substituted(): void
    {
        $emptyPage = new LengthAwarePaginator(collect(), 0, 15);

        $result = $this->fallbackHarness(true)->run(fn () => $emptyPage, 'blogs');

        // The DB answered truthfully (populated collection, filter matched
        // nothing) — the exact empty page must come back, not mock content.
        $this->assertSame($emptyPage, $result);
        $this->assertCount(0, $result->items());
    }

    public function test_empty_result_of_truly_empty_collection_still_falls_back_to_mock(): void
    {
        request()->merge(['status' => 'published']); // mock blogs all have dates

        $emptyPage = new LengthAwarePaginator(collect(), 0, 15);

        $result = $this->fallbackHarness(false)->run(fn () => $emptyPage, 'blogs');

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->total(), 'mock substitution must still engage on an empty DB');
        $this->assertNotEmpty($result->items());
    }

    public function test_connection_failure_still_falls_back_to_mock(): void
    {
        $result = $this->fallbackHarness(true)->run(
            fn () => throw new ConnectionTimeoutException('simulated outage'),
            'blogs'
        );

        $this->assertInstanceOf(LengthAwarePaginator::class, $result);
        $this->assertGreaterThan(0, $result->total(), 'connection errors must keep triggering the mock fallback');
    }

    // ------------------------------------------------------------------
    // F2 — MongoProbe memoization must expire (TTL), not live forever
    // ------------------------------------------------------------------

    public function test_probe_memoizes_within_ttl(): void
    {
        $first = microtime(true);
        $this->assertFalse(MongoProbe::available(), 'probe must fail with the pinned DSN');
        $firstTook = microtime(true) - $first;
        $this->assertGreaterThan(0.05, $firstTook, 'first call should have actually probed the server');

        $second = microtime(true);
        $this->assertFalse(MongoProbe::available());
        $secondTook = microtime(true) - $second;
        $this->assertLessThan(0.2, $secondTook, 'second call within the TTL must return the memoized verdict instantly');
    }

    public function test_probe_re_probes_after_ttl_expires(): void
    {
        $this->assertFalse(MongoProbe::available());

        // Forge a stale memo (10s old > 5s TTL): the next call must re-probe
        // (slow) rather than trust the cached verdict forever.
        $prop = new ReflectionProperty(MongoProbe::class, 'availableAt');
        $prop->setValue(null, microtime(true) - 10);

        $start = microtime(true);
        $this->assertFalse(MongoProbe::available());
        $took = microtime(true) - $start;

        $this->assertGreaterThan(0.05, $took, 'a stale memo must trigger a real re-probe');
    }

    public function test_write_probe_re_probes_after_ttl_expires(): void
    {
        $this->assertFalse(MongoProbe::writeAvailable());

        $prop = new ReflectionProperty(MongoProbe::class, 'writableAt');
        $prop->setValue(null, microtime(true) - 10);

        $start = microtime(true);
        $this->assertFalse(MongoProbe::writeAvailable());
        $took = microtime(true) - $start;

        $this->assertGreaterThan(0.05, $took, 'a stale write memo must trigger a real re-probe');
    }

    // ------------------------------------------------------------------
    // F3 — admin blogs mock search must cover title AND excerpt
    // ------------------------------------------------------------------

    public function test_admin_blog_mock_search_matches_excerpt_not_title_only(): void
    {
        // "breakdown" appears only in an excerpt of the mock blogs — the old
        // title-only filter returned nothing for it.
        request()->merge(['search' => 'breakdown']);

        $view = app(\App\Http\Controllers\Admin\BlogController::class)->index(request());
        $blogs = $view->getData()['blogs'];

        $slugs = collect($blogs->items())->pluck('slug');
        $this->assertContains('nextjs-app-router', $slugs, 'excerpt-only term must match via excerpt search');
    }
}
