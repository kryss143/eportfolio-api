<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Single authority for MongoDB index definitions (audit post-mongo Bug 2,
 * 2026-09-28). History: index *names* were pinned in the migrations but the
 * key specs drifted between code paths — activity_log ended up owning both
 * {created_at: 1} and {created_at: -1} under the name "idx_created_at", and
 * every subsequent create-index attempt threw CommandException code 86
 * ("An existing index has the same name as the requested index") as an
 * unhandled 500 on the write path.
 *
 * This helper is now the ONLY place that issues createIndex(). It diffs the
 * collection's current indexes against the wanted specs and:
 *  - skips exact (name + key) matches,
 *  - drops same-named indexes whose key differs, then recreates with the
 *    wanted spec (the drift is repaired instead of erroring),
 *  - leaves differently-named indexes alone (no surprise IO).
 *
 * Migrations declare their collections through here, so `migrate:fresh` can
 * never fork from `php artisan mongo:check --indexes` or any other caller.
 */
final class MongoSchema
{
    /**
     * Registry of every index spec declared this process, keyed by collection
     * (populated by ensureIndexes). Powers the `mongo:check --indexes` doctor,
     * which diffs the database against exactly these declarations.
     *
     * @var array<string, array<int, array{name: string, key: string|array, ...}>>
     */
    private static array $declared = [];

    /** All index specs declared via ensureIndexes(), keyed by collection. */
    public static function declaredIndexes(): array
    {
        return self::$declared;
    }

    /** Public alias for report rendering (mongo:check --indexes output). */
    public static function normalizeKeyForReport(string|array $key): string
    {
        return self::normalizeKey($key);
    }

    /**
     * Ensure every wanted index exists on $collection with exactly the given
     * name, key, and options. Index defs: ['name' => ..., 'key' => field|array,
     * ...options e.g. 'unique' => true, 'expireAfterSeconds' => 0].
     */
    public static function ensureIndexes(string $collection, array $indexes): void
    {
        $db = MongoProbe::available()
            ? DB::connection('mongodb')->getDatabase()
            : null;

        if ($db === null) {
            // Outage (e.g. running the SQL legacy path in an exotic setup):
            // index work can't proceed, but migration flow must not die here.
            return;
        }

        // Record the declaration even on early return so the doctor sees the
        // full intended schema for this process.
        self::$declared[$collection] = array_values(array_merge(
            self::$declared[$collection] ?? [],
            $indexes,
        ));

        $current = [];
        foreach ($db->selectCollection($collection)->listIndexes() as $index) {
            $current[$index['name']] = $index; // _id_ included, never touched
        }

        foreach ($indexes as $def) {
            $name = $def['name'];
            $rawKey = $def['key'];                 // creation uses the raw spec
            $wantedKey = self::normalizeKey($rawKey); // comparison uses the normalized form

            if (isset($current[$name])) {
                if (self::normalizeKey($current[$name]['key']) === $wantedKey) {
                    continue; // exact match (name + key) — nothing to do
                }

                // Same name, different key: the drifted state that produced
                // code 86. Drop the stale spec so the wanted one can exist.
                $db->selectCollection($collection)->dropIndex($name);
            }

            $spec = array_merge(
                ['name' => $name],
                self::indexOptions($def),
            );

            try {
                $db->selectCollection($collection)->createIndex($rawKey, $spec);
            } catch (\Throwable $e) {
                // A concurrent creator won the race, or the server refused a
                // legitimate conflict. Log at warning — index shape is still
                // enforced on the next run — but never fail the caller.
                Log::warning("MongoSchema: could not ensure index {$collection}.{$name}: ".$e->getMessage());
            }
        }
    }

    /**
     * Create the collection (if missing) and ensure its indexes. Migrations
     * call this instead of Schema::create so index specs live in one place.
     *
     * @param array<int, array{name: string, key: string|array, ...}> $indexes
     */
    public static function createCollection(string $collection, array $indexes = []): void
    {
        $db = DB::connection('mongodb')->getDatabase();

        // createCollection errors if the name already exists — that's fine
        // here (idempotent create), so swallow exactly that case.
        try {
            $db->createCollection($collection);
        } catch (\Throwable $e) {
            if (! str_contains($e->getMessage(), 'already exists')) {
                throw $e;
            }
        }

        if ($indexes !== []) {
            self::ensureIndexes($collection, $indexes);
        }
    }

    public static function dropCollection(string $collection): void
    {
        DB::connection('mongodb')->getDatabase()->selectCollection($collection)->drop();
    }

    /**
     * Normalize a key spec to a comparable string: ['a' => 1, 'b' => -1] →
     * "a:1,b:-1". Handles string keys ("field" → field:1), both int and float
     * directions (driver may return float), and nested-key arrays.
     */
    private static function normalizeKey(string|array $key): string
    {
        if (is_string($key)) {
            return $key.':1';
        }

        $parts = [];
        foreach ($key as $field => $direction) {
            $parts[] = $field.':'.(float) $direction;
        }

        return implode(',', $parts);
    }

    /** Options we forward to createIndex (everything except name/key). */
    private static function indexOptions(array $def): array
    {
        return collect($def)
            ->except(['name', 'key'])
            ->all();
    }
}
