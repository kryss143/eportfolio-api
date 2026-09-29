<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Drops the MongoDB collections the app writes to, replacing the old
 * RefreshDatabase flow (SQL transactions on the sqlite fallback no longer
 * exist — the whole app, tests included, runs on MongoDB). Use in setUp()
 * AND tearDown() so a crashing test never pollutes later ones.
 *
 * Collections are dropped (not just emptied) so index state is rebuilt by
 * the schema migrations each test's migrate run creates from — no stale
 * index can leak between tests.
 *
 * Every collection named here must exist in the migration set.
 */
trait CleansMongoCollections
{
    protected function cleanMongoCollections(): void
    {
        try {
            $db = DB::connection('mongodb')->getDatabase();

            foreach ([
                'users',
                'activity_log',
                'projects',
                'blogs',
                'tech_skills',
                'experiences',
                'metrics',
                'skills',
                'sessions',
                'cache',
                'cache_locks',
                'jobs',
                'job_batches',
                'failed_jobs',
                'rate_limits',
                // Stand-in collection used by Audit20260928Test's anonymous model.
                'audit_test_projects',
            ] as $collection) {
                $db->selectCollection($collection)->drop();
            }
        } catch (\Throwable $e) {
            throw new \RuntimeException(
                'MongoDB is required for the test suite (see MONGODB_URI / MONGODB_DATABASE=eportfolio_testing) but is unreachable: '.$e->getMessage(),
                0,
                $e
            );
        }
    }
}
