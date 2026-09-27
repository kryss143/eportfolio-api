<?php

namespace App\Console\Commands;

use App\Support\MongoProbe;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use MongoDB\BSON\UTCDateTime;
use MongoDB\Driver\Exception\ConnectionException as MongoConnectionException;
use RuntimeException;

class MigrateSqliteToMongodb extends Command
{
    /**
     * Tables migrated to MongoDB collections. Everything else in SQLite
     * (migrations, cache, sessions, jobs, ...) is either framework-managed
     * (the mongodb session/cache/queue drivers take over) or empty in this
     * project, so there is nothing to copy.
     */
    protected const TABLES = ['users', 'activity_log'];

    protected $signature = 'app:migrate-sqlite-to-mongodb
                            {--force : Run without confirmation (for CI / scripted runs)}
                            {--timeout=600 : Seconds of connection-error retrying before giving up}';

    protected $description = 'Copy users and activity_log from SQLite to MongoDB in bulk, create indexes, and verify the result (idempotent, safe to re-run)';

    public function handle(): int
    {
        // The Atlas endpoint fails TLS handshakes in multi-second bursts from
        // some networks. One bulk-copy needs only a handful of round trips,
        // and the long deadline lets the command wait out long bad stretches.
        $timeout = max(60, (int) $this->option('timeout'));

        if (! MongoProbe::available(30_000)) {
            $this->error("MongoDB is not reachable after {$timeout}s of retries — check the connection/VPN.");

            return self::FAILURE;
        }
        $this->info('MongoDB: connected ('.$this->mongoHost().')');

        if (! $this->option('force') && ! $this->confirm('Copy users and activity_log to the "'.$this->mongoDbName().'" database now?', true)) {
            $this->line('Aborted.');

            return self::SUCCESS;
        }

        $copied = $this->withMongoRetry($timeout, function (): array {
            $users = $this->copyUsers();
            $logs = $this->copyActivityLog();

            return ['users' => $users, 'activity_log' => $logs];
        });

        $this->withMongoRetry($timeout, fn () => $this->createIndexes());

        $this->newLine();
        $this->withMongoRetry($timeout, fn () => $this->verify($copied));

        return self::SUCCESS;
    }

    // ------------------------------------------------------------------
    // users
    // ------------------------------------------------------------------

    private function copyUsers(): int
    {
        $rows = DB::connection('sqlite')->table('users')->get()->all();
        $mongo = DB::connection('mongodb');

        // Existing mongo users (by email) — skip them on re-runs and reuse
        // their _id for the activity_log remap.
        $existing = [];
        // The mongodb builder aliases _id to "id" on returned docs (driver
        // 5.9) and omits it unless requested — read ->id, not ->_id, or
        // re-runs crash on an undefined property (non-idempotent).
        foreach ($mongo->table('users')->get(['_id', 'email']) as $doc) {
            $existing[(string) $doc->email] = (string) $doc->id;
        }

        $toInsert = [];
        $idMap = []; // sqlite id => mongo _id
        foreach ($rows as $row) {
            if (isset($existing[$row->email])) {
                $idMap[$row->id] = $existing[$row->email];

                continue;
            }

            $idMap[$row->id] = (string) new \MongoDB\BSON\ObjectId();
            $toInsert[] = [
                '_id' => new \MongoDB\BSON\ObjectId($idMap[$row->id]),
                'name' => $row->name,
                'email' => $row->email,
                'password' => (string) $row->password,
                'is_admin' => (bool) $row->is_admin,
                'email_verified_at' => $this->toDate($row->email_verified_at),
                'created_at' => $this->toDate($row->created_at),
                'updated_at' => $this->toDate($row->updated_at),
            ];
        }

        $inserted = 0;
        if ($toInsert !== []) {
            $mongo->table('users')->insert($toInsert);
            $inserted = count($toInsert);
        }

        $this->line("users: {$inserted} inserted, ".count($existing).' already present');

        $this->userIdMap = $idMap;

        return count($idMap);
    }

    // ------------------------------------------------------------------
    // activity_log
    // ------------------------------------------------------------------

    private array $userIdMap = [];

    private function copyActivityLog(): int
    {
        $rows = DB::connection('sqlite')->table('activity_log')->orderBy('id')->get()->all();
        if ($rows === []) {
            $this->line('activity_log: (empty)');

            return 0;
        }

        $mongo = DB::connection('mongodb');

        // Dedup key: the legacy auto-increment id, so re-runs never duplicate.
        $existing = [];
        foreach ($mongo->table('activity_log')->get(['legacy_sqlite_id']) as $doc) {
            if ($doc->legacy_sqlite_id !== null) {
                $existing[(int) $doc->legacy_sqlite_id] = true;
            }
        }

        $toInsert = [];
        foreach ($rows as $row) {
            $legacyId = (int) $row->id;
            if (isset($existing[$legacyId])) {
                continue;
            }

            $mongoUserId = $row->user_id !== null ? ($this->userIdMap[$row->user_id] ?? null) : null;

            $toInsert[] = [
                'legacy_sqlite_id' => $legacyId,
                // Remap the FK to the migrated Mongo user id (null if the
                // referenced sqlite user was absent).
                'user_id' => $mongoUserId,
                'subject_type' => $row->subject_type,
                'subject_id' => $row->subject_id,
                'event' => $row->event,
                'old_values' => $this->decodeJson($row->old_values),
                'new_values' => $this->decodeJson($row->new_values),
                'created_at' => $this->toDate($row->created_at),
                'updated_at' => $this->toDate($row->updated_at),
            ];
        }

        $inserted = 0;
        if ($toInsert !== []) {
            $mongo->table('activity_log')->insert($toInsert);
            $inserted = count($toInsert);
        }

        $this->line("activity_log: {$inserted} inserted, ".count($existing).' already present');

        return count($rows);
    }

    // ------------------------------------------------------------------
    // Indexes (collections created on demand by the drivers)
    // ------------------------------------------------------------------

    private function createIndexes(): void
    {
        $this->info('Creating indexes…');
        $db = DB::connection('mongodb')->getDatabase();

        $db->selectCollection('users')->createIndex(['email' => 1], ['unique' => true, 'name' => 'uniq_email']);

        $db->selectCollection('activity_log')->createIndex(['event' => 1], ['name' => 'idx_event']);
        $db->selectCollection('activity_log')->createIndex(['subject_type' => 1, 'subject_id' => 1], ['name' => 'idx_subject']);
        $db->selectCollection('activity_log')->createIndex(['user_id' => 1], ['name' => 'idx_user']);
        // Ascending: a single-field index serves both sort directions, and
        // the schema migrations create this same name ascending — a -1 spec
        // under an existing same-name index makes re-runs fail with an
        // IndexOptionsConflict.
        $db->selectCollection('activity_log')->createIndex(['created_at' => 1], ['name' => 'idx_created_at']);

        // TTL indexes so the framework collections self-clean (mirrors the
        // sqlite cache table's expiration behaviour).
        $db->selectCollection('cache')->createIndex(['expires_at' => 1], ['expireAfterSeconds' => 0, 'name' => 'ttl_expires_at']);
        $db->selectCollection('sessions')->createIndex(['last_activity' => 1], ['expireAfterSeconds' => config('session.lifetime', 120) * 60, 'name' => 'ttl_last_activity']);

        $this->line('  users: uniq_email | activity_log: event, subject, user, created_at | cache/sessions: TTL');
    }

    // ------------------------------------------------------------------
    // Verification
    // ------------------------------------------------------------------

    private function verify(array $copied): void
    {
        $this->info('Verifying…');

        $sqliteUsers = DB::connection('sqlite')->table('users')->count();
        $mongoUsers = DB::connection('mongodb')->table('users')->count();

        $sqliteLogs = DB::connection('sqlite')->table('activity_log')->count();
        $mongoLogs = DB::connection('mongodb')->table('activity_log')->count();

        $this->table(
            ['table', 'sqlite rows', 'mongo docs'],
            [
                ['users', $sqliteUsers, $mongoUsers],
                ['activity_log', $sqliteLogs, $mongoLogs],
            ],
        );

        // Query the MONGO connection directly — the User model's connection
        // depends on the current DB_CONNECTION env, which may still point at
        // sqlite when this command runs (the config flip happens afterwards).
        $admin = DB::connection('mongodb')->table('users')->where('email', env('ADMIN_EMAIL', 'admin@example.com'))->first()
            ?? DB::connection('mongodb')->table('users')->where('is_admin', true)->first();

        if ($admin !== null) {
            $this->info("Admin user present in MongoDB: {$admin->email}");
        } else {
            $this->warn('No admin user found in MongoDB! AdminUserSeeder may need to run.');
        }

        $ok = $mongoUsers >= $sqliteUsers && $mongoLogs >= $sqliteLogs && $admin !== null;

        $this->newLine();
        if ($ok) {
            $this->info('✔ Migration verified. Runtime config (DB_CONNECTION, SESSION_DRIVER, CACHE_STORE, QUEUE_CONNECTION) must point at mongodb to complete the switch.');
        } else {
            $this->error('✘ Verification failed — see counts above.');
        }
    }

    // ------------------------------------------------------------------
    // Helpers
    // ------------------------------------------------------------------

    /**
     * Run a Mongo phase, retrying through TLS/selection bursts until the
     * deadline. The cached Client is deliberately KEPT between attempts:
     * with serverSelectionTryOnce=false the driver retries selection within
     * its own budget, and purging would discard its topology state and
     * generate fresh TLS handshakes every attempt (which can make
     * server-side throttling worse).
     */
    private function withMongoRetry(int $timeoutSeconds, callable $fn): mixed
    {
        $deadline = microtime(true) + $timeoutSeconds;
        $last = null;

        while (true) {
            try {
                return $fn();
            } catch (MongoConnectionException $e) {
                $last = $e;
                if (microtime(true) >= $deadline) {
                    throw new RuntimeException("MongoDB unreachable for {$timeoutSeconds}s (connection-error retries exhausted).", 0, $last);
                }
                $this->warn('  connection error — retrying ('.substr($e->getMessage(), 0, 60).'…)');
                usleep(5_000_000);
            }
        }
    }

    /** Decode a sqlite JSON column into a native array for Mongo storage. */
    private function decodeJson(?string $json): ?array
    {
        if ($json === null || $json === '') {
            return null;
        }

        $decoded = json_decode($json, true);

        return is_array($decoded) ? $decoded : null;
    }

    /** Convert a sqlite datetime string to a BSON-safe UTCDateTime. */
    private function toDate(?string $value): ?UTCDateTime
    {
        if ($value === null || $value === '') {
            return null;
        }

        return new UTCDateTime(Carbon::parse($value)->getTimestampMs());
    }

    private function mongoHost(): string
    {
        $dsn = (string) config('database.connections.mongodb.dsn');

        return parse_url($dsn, PHP_URL_HOST) ?: '(unknown host)';
    }

    private function mongoDbName(): string
    {
        return (string) config('database.connections.mongodb.database', 'eportfolio');
    }
}
