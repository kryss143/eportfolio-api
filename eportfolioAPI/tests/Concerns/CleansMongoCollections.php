<?php

namespace Tests\Concerns;

use Illuminate\Support\Facades\DB;

/**
 * Empties the MongoDB collections the app writes to, replacing
 * RefreshDatabase (which only migrates SQL stores and would leave Mongo
 * state — users, content, activity_log — leaking between tests). Use in
 * setUp() AND tearDown() so a crashing test never pollutes later ones.
 *
 * Collections are dropped only if empty-of-need: deleting documents keeps
 * the indexes created by the schema migrations (RefreshDatabase never
 * touches Mongo, so those indexes must survive the whole suite run).
 */
trait CleansMongoCollections
{
    protected function cleanMongoCollections(): void
    {
        if (! extension_loaded('mongodb')) {
            return;
        }

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
            ] as $collection) {
                $db->selectCollection($collection)->deleteMany([]);
            }
        } catch (\Throwable) {
            // Mongo unreachable (pinned to a closed port in phpunit.xml):
            // tests then exercise the mock-data path and write nothing, so
            // there is nothing to clean.
        }
    }
}
