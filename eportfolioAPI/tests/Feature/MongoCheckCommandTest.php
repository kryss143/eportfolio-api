<?php

namespace Tests\Feature;

use App\Support\MongoProbe;
use Tests\TestCase;

/**
 * mongo:check failure path. phpunit.xml pins MONGODB_URI to a closed port
 * with 100ms timeouts, so both probes fail fast and deterministically. The
 * command must report the verdict AND surface the exact driver error text —
 * surfacing that error is the command's reason to exist.
 */
class MongoCheckCommandTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        MongoProbe::flush();
    }

    protected function tearDown(): void
    {
        MongoProbe::flush();

        parent::tearDown();
    }

    public function test_reports_failure_with_driver_error_when_mongo_is_unreachable(): void
    {
        $this->artisan('mongo:check', ['--wait' => 1])
            ->expectsOutputToContain('MongoDB connectivity check')
            ->expectsOutputToContain('Read availability (any node): FAILED')
            ->expectsOutputToContain('Write availability (primary): FAILED')
            ->expectsOutputToContain('✘ MongoDB is unreachable — admin pages fall back to read-only sample data.')
            ->expectsOutputToContain('Driver error (one strict-primary ping):')
            ->assertExitCode(1);
    }

    public function test_maps_server_selection_failure_to_remediation(): void
    {
        // 127.0.0.1:1 (closed port): the driver wraps the failure in a bare
        // "No suitable servers found" server-selection timeout — the fallback
        // signature must map it to remediation, distinct from the live
        // cluster's TLS failure.
        $this->artisan('mongo:check', ['--wait' => 1])
            ->expectsOutputToContain('Likely cause / remediation:')
            ->expectsOutputToContain('The driver could not select any server within serverSelectionTimeoutMS.')
            ->assertExitCode(1);
    }
}
