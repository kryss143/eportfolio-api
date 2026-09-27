<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Command;
use MongoDB\Driver\Exception\ConnectionException;
use MongoDB\Driver\ReadPreference;

/**
 * Single source of truth for "is MongoDB reachable right now?" (reads) and
 * "is the replica-set primary writable right now?" (writes).
 *
 * Bug #2 (2026-09-25): every probe previously called getDatabase(), which only
 * returns the lazily constructed Database object and performs NO server I/O —
 * so Mongo was reported as available even while unreachable. Connection::ping()
 * performs an actual server selection and throws on failure.
 */
final class MongoProbe
{
    /**
     * How long a memoized verdict stays trusted. Process-lifetime memoization
     * (audit r2 F2, 2026-09-28) never expires under single-process servers
     * (`php artisan serve` / long-lived workers): an outage after the first
     * successful probe left admin pages taking the DB branch and 500ing until
     * restart, even after Mongo recovered. Time-boxing keeps the burst
     * protection within the TTL window while bounding staleness to it.
     */
    private const MEMO_TTL_SECONDS = 5;

    /** Memoized read-availability result and the time it was recorded. */
    private static ?bool $available = null;
    private static ?int $availableAt = null;

    /** Memoized write-availability result and the time it was recorded. */
    private static ?bool $writable = null;
    private static ?int $writableAt = null;

    private static function memoIsFresh(?int $recordedAt): bool
    {
        return $recordedAt !== null
            && (microtime(true) - $recordedAt) < self::MEMO_TTL_SECONDS;
    }

    public static function available(int $maxWaitMs = 1000): bool
    {
        if (self::$available !== null && self::memoIsFresh(self::$availableAt)) {
            return self::$available;
        }

        if (! extension_loaded('mongodb')) {
            self::$availableAt = microtime(true);

        return self::$available = false;
        }

        // Atlas SRV endpoints occasionally fail the TLS handshake in bursts
        // lasting several seconds. Keep retrying within $maxWaitMs, purging
        // between attempts so each try gets a fresh Client/topology (the
        // driver's cached one fails fast after a failed selection).
        //
        // Latency envelope (audit r3 F1, 2026-09-28): with the server DOWN,
        // one attempt always burns the full server-selection cycle before the
        // $maxWaitMs check can run — measured at ~2.5s per probe with the
        // config's serverSelectionTimeoutMS=2500, NOT the ~1s the previous
        // docblock claimed ($maxWaitMs only gates BETWEEN attempts). To make
        // the budget real, cap serverSelectionTimeoutMS <= maxWaitMs; that
        // trades against the burst tolerance above, so it stays a deliberate
        // config choice rather than a silent change here. With Mongo UP the
        // probe is one ping round trip.
        $start = microtime(true);
        $purged = false;
        do {
            try {
                DB::connection('mongodb')->ping();

                self::$availableAt = microtime(true);

                return self::$available = true;
            } catch (\Throwable) {
                if ((microtime(true) - $start) * 1000 >= $maxWaitMs) {
                    break;
                }
                // Purge at most once: a cached Client from an earlier failed
                // selection can be poisoned; after that let the driver's own
                // topology (serverSelectionTryOnce=false) recover, since
                // repeated purges mean fresh TLS handshakes every attempt.
                if (! $purged) {
                    DB::purge('mongodb');
                    $purged = true;
                }
                usleep(400_000);
            }
        } while (true);

        self::$availableAt = microtime(true);

        return self::$available = false;
    }

    /**
     * Can we WRITE right now — i.e. is the replica-set primary reachable?
     *
     * Connection::ping() uses ReadPreference::PRIMARY_PREFERRED, which
     * silently degrades to a secondary when the primary is unreachable
     * (observed during the 2026-09-25 primary partition: pings OK, writes
     * failing). This probe runs the ping in strict PRIMARY mode so a
     * reachable secondary never masquerades as write availability.
     *
     * Used by the admin mutation guards; read paths keep using available().
     */
    public static function writeAvailable(int $maxWaitMs = 1000): bool
    {
        if (self::$writable !== null && self::memoIsFresh(self::$writableAt)) {
            return self::$writable;
        }

        if (! extension_loaded('mongodb')) {
            self::$writableAt = microtime(true);

        return self::$writable = false;
        }

        $start = microtime(true);
        $purged = false;
        do {
            try {
                $connection = DB::connection('mongodb');
                $connection->getClient()->getManager()->executeCommand(
                    'admin',
                    new Command(['ping' => 1]),
                    ['readPreference' => new ReadPreference(ReadPreference::PRIMARY)],
                );

                self::$writableAt = microtime(true);

                return self::$writable = true;
            } catch (ConnectionException|\MongoDB\Driver\Exception\RuntimeException) {
                if ((microtime(true) - $start) * 1000 >= $maxWaitMs) {
                    break;
                }
                if (! $purged) {
                    DB::purge('mongodb');
                    $purged = true;
                }
                usleep(400_000);
            }
        } while (true);

        self::$writableAt = microtime(true);

        return self::$writable = false;
    }

    /**
     * Forget the memoized results and drop the cached (possibly poisoned)
     * connection so the next probe gets a fresh Client (tests / retries /
     * long-running workers).
     */
    public static function flush(): void
    {
        self::$available = null;
        self::$availableAt = null;
        self::$writable = null;
        self::$writableAt = null;
        DB::purge('mongodb');
    }
}
