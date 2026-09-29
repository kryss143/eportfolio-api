<?php

namespace App\Console\Commands;

use App\Support\MongoProbe;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use MongoDB\Driver\Command as MongoCommand;
use MongoDB\Driver\ReadPreference;
use MongoDB\Driver\Exception\RuntimeException as MongoRuntimeException;
use Throwable;

/**
 * Diagnose MongoDB connectivity from the exact environment the app runs in —
 * same config, same driver, same network. Answers the recurring question
 * "why is the read-only sample-data banner showing?" by printing the probes'
 * verdicts plus the verbatim driver error, with known failure signatures
 * mapped to remediation steps.
 *
 * The probes swallow driver exceptions by design (they are availability
 * verdicts, not diagnostics), so on failure this command additionally runs
 * one raw strict-primary ping to capture the precise exception text.
 */
class MongoCheckCommand extends Command
{
    protected $signature = 'mongo:check
                            {--wait=2 : Seconds to keep retrying each probe before giving up}
                            {--indexes : Also diff every collection\'s indexes against the MongoSchema declarations (Bug 2 doctor)}';

    protected $description = 'Check MongoDB read/write availability and print the exact driver error (diagnoses the read-only sample-data banner)';

    /** Known error signatures (lowercase substring) → remediation hints. */
    private const SIGNATURES = [
        'tlsv1 alert internal error' => [
            'Atlas rejected the TLS handshake before sending a certificate.',
            'On shared-tier (M0) clusters this is almost always the IP Access List:',
            'add your current public IP (or 0.0.0.0/0 for dev) under Security → Network Access.',
            'Also possible: a TLS-inspecting firewall, VPN, or corporate proxy in the path.',
        ],
        'connection refused' => [
            'Nothing is listening on the target host:port.',
            'For local dev, start mongod; for a cluster, verify host/port in MONGODB_URI.',
        ],
        'authentication failed' => [
            'The host accepted the connection but rejected the credentials.',
            'Verify the user exists and its password/role under Database Access in Atlas.',
        ],
        'not allowed' => [
            'The server refused the operation for this user/host combination.',
            'Check Database Access roles and the Network Access list in Atlas.',
        ],
        'timed out' => [
            'The network path exists but the server did not answer in time.',
            'Check VPN status, firewall egress rules on port 27017, and cluster status.',
        ],
        'no suitable servers found' => [
            'The driver could not select any server within serverSelectionTimeoutMS.',
            'Check network reachability to the cluster, VPN/firewall egress rules on',
            'port 27017, and the project IP Access List in Atlas (Security → Network Access).',
        ],
    ];

    public function handle(): int
    {
        $waitMs = max(1, (int) $this->option('wait')) * 1000;
        $dsn = (string) config('database.connections.mongodb.dsn');
        $host = parse_url($dsn, PHP_URL_HOST) ?: '(no DSN configured)';
        $ext = extension_loaded('mongodb') ? phpversion('mongodb') : null;

        $this->info('MongoDB connectivity check');
        $this->line("  host:     {$host}");
        $this->line('  database: '.config('database.connections.mongodb.database', 'eportfolio'));
        $this->line('  driver:   '.($ext !== null ? "mongodb {$ext}" : 'EXTENSION NOT LOADED'));
        $this->line('  server-selection timeout: '.($this->selectionTimeoutMs() ?? 'driver default').'ms');
        $this->newLine();

        if ($ext === null) {
            $this->error('The PHP mongodb extension is not loaded — the app is running degraded without it.');

            return self::FAILURE;
        }

        // Fresh verdicts: the command's job is to probe NOW, not to report a
        // memoized result a web request recorded a second ago.
        MongoProbe::flush();
        $readOk = MongoProbe::available($waitMs);
        $writeOk = MongoProbe::writeAvailable($waitMs);

        if ($writeOk && $this->option('indexes')) {
            return $this->checkIndexes();
        }

        $this->renderProbe('Read availability (any node)', $readOk);
        $this->renderProbe('Write availability (primary)', $writeOk);

        if ($writeOk) {
            $this->newLine();
            $this->info('✔ MongoDB is fully available — the admin UI serves live data and accepts writes.');

            return self::SUCCESS;
        }

        $this->newLine();
        if ($readOk) {
            $this->warn('⚠ Reads work but the replica-set primary is unreachable — this is the');
            $this->line('  "primary partition" signature: list pages show live data, mutations fail.');
        } else {
            $this->error('✘ MongoDB is unreachable — admin pages fall back to read-only sample data.');
        }

        $detail = $this->rawError();
        if ($detail !== null) {
            $this->newLine();
            $this->line('Driver error (one strict-primary ping):');
            foreach (explode("\n", trim($detail)) as $line) {
                $this->line('  '.$line);
            }
            $this->renderHints($detail);
        }

        return self::FAILURE;
    }

    /**
     * Bug 2 doctor: diff each collection's actual indexes against the specs
     * the migrations declare through MongoSchema, and repair same-name
     * different-key drift (the code-86 source) via ensureIndexes. Exit 0 when
     * everything matches after the pass.
     */
    private function checkIndexes(): int
    {
        $db = DB::connection('mongodb')->getDatabase();
        $declared = \App\Support\MongoSchema::declaredIndexes();

        $this->newLine();
        $this->info('Index audit (MongoSchema is the single authority):');

        $problems = 0;
        foreach ($declared as $collection => $indexes) {
            $exists = in_array($collection, $db->listCollectionNames()->toArray(), true);
            if (! $exists) {
                $this->line("  <fg=yellow>{$collection}: collection missing — run migrate</>");
                $problems++;

                continue;
            }

            // Repair drift + create missing (idempotent, same code path the
            // migrations use — this run converges the database to the specs).
            \App\Support\MongoSchema::ensureIndexes($collection, $indexes);

            $actual = [];
            foreach ($db->selectCollection($collection)->listIndexes() as $index) {
                $actual[$index['name']] = $index['key'];
            }

            foreach ($indexes as $def) {
                $key = \App\Support\MongoSchema::normalizeKeyForReport($def['key']);
                if (($actual[$def['name']] ?? null) !== null) {
                    $actualKey = \App\Support\MongoSchema::normalizeKeyForReport($actual[$def['name']]);
                    if ($actualKey === $key) {
                        $this->line("  <fg=green>✔</> {$collection}.{$def['name']} {$key}");

                        continue;
                    }
                    $this->line("  <fg=red>✘ {$collection}.{$def['name']} key drift: want {$key}, have {$actualKey}</>");
                    $problems++;

                    continue;
                }
                $this->line("  <fg=red>✘ {$collection}.{$def['name']} missing (create failed — see log)</>");
                $problems++;
            }
        }

        if ($problems === 0) {
            $this->newLine();
            $this->info('✔ All declared indexes match.');

            return self::SUCCESS;
        }

        return self::FAILURE;
    }

    /**
     * One raw strict-primary ping to capture the exact driver exception the
     * probes absorbed. Null when the ping unexpectedly succeeds.
     */
    private function rawError(): ?string
    {
        try {
            DB::connection('mongodb')->getClient()->getManager()->executeCommand(
                'admin',
                new MongoCommand(['ping' => 1]),
                ['readPreference' => new ReadPreference(ReadPreference::PRIMARY)],
            );

            return null;
        } catch (MongoRuntimeException $e) {
            return '['.class_basename($e).'] '.$e->getMessage();
        } catch (Throwable $e) {
            return '['.class_basename($e).'] '.$e->getMessage();
        }
    }

    /** serverSelectionTimeoutMS from the mongodb connection options, if set. */
    private function selectionTimeoutMs(): ?int
    {
        $options = (array) config('database.connections.mongodb.options');

        return isset($options['serverSelectionTimeoutMS'])
            ? (int) $options['serverSelectionTimeoutMS']
            : null;
    }

    private function renderProbe(string $label, bool $ok): void
    {
        $verdict = $ok ? '<fg=green>OK</>' : '<fg=red>FAILED</>';
        $this->line("  {$label}: {$verdict}");
    }

    private function renderHints(string $detail): void
    {
        $lower = mb_strtolower($detail);

        foreach (self::SIGNATURES as $needle => $hints) {
            if (str_contains($lower, $needle)) {
                $this->newLine();
                $this->info('Likely cause / remediation:');
                foreach ($hints as $hint) {
                    $this->line('  • '.$hint);
                }

                return;
            }
        }
    }
}
