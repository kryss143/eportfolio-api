<?php

namespace Tests;

use App\Support\MongoProbe;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // The suite drops and recreates MongoDB collections between tests.
        // Refuse to run against anything but the dedicated testing database —
        // phpunit.xml pins MONGODB_DATABASE=eportfolio_testing, and this guard
        // also catches a real MONGODB_DATABASE exported in the shell (PHPUnit's
        // env vars don't override the real environment).
        $db = (string) config('database.connections.mongodb.database');
        if ($db !== 'eportfolio_testing') {
            throw new \RuntimeException(
                "Refusing to run the suite against \"{$db}\" — tests drop all collections, so MONGODB_DATABASE must be eportfolio_testing (phpunit.xml pins it)."
            );
        }
    }

    /**
     * Simulate a MongoDB outage for the current test: pin the DSN to a
     * closed port and tighten the driver timeouts so the failure is fast
     * (~100ms) and deterministic. Call before the first query of the test.
     */
    protected function pinMongoDown(): void
    {
        config()->set('database.connections.mongodb.dsn', 'mongodb://127.0.0.1:1/');
        config()->set('database.connections.mongodb.host', '127.0.0.1');
        config()->set('database.connections.mongodb.port', 1);
        config()->set('database.connections.mongodb.options.connectTimeoutMS', 100);
        config()->set('database.connections.mongodb.options.serverSelectionTimeoutMS', 100);
        config()->set('database.connections.mongodb.options.socketTimeoutMS', 100);
        DB::purge('mongodb');
        MongoProbe::flush();
    }

    protected function tearDown(): void
    {
        DB::purge('mongodb');
        MongoProbe::flush();

        parent::tearDown();
    }
}
