# Migrating SQLite → MongoDB

Everything needed is already in place. This finishes the switch after Atlas
connectivity is restored (see `atlas-connectivity-2026-09-25.md` for the
blocker that paused it — an egress-IP allowlist issue, not a code issue).

## What's already in place

- `php artisan app:migrate-sqlite-to-mongodb` — bulk-copies `users` +
  `activity_log`, remaps `user_id` FKs to the new Mongo ids, creates indexes
  (incl. cache/session TTL indexes), verifies counts. **Idempotent**: re-runs
  skip existing docs (dedup keys: `email` for users, `legacy_sqlite_id` for
  activity_log).
- `App\Support\MongoProbe` — real `ping()`-based availability probe (bug #2
  fix), deadline-aware: `MongoProbe::available(30_000)`.
- `mongodb` stores in `config/cache.php` and `config/queue.php`; timeout knobs
  are env-overridable (`MONGODB_CONNECT_TIMEOUT_MS`,
  `MONGODB_SERVER_SELECTION_TIMEOUT_MS`, `MONGODB_SOCKET_TIMEOUT_MS`).
- All five legacy migrations are pinned to the `sqlite` connection, so
  `php artisan migrate` never touches the default (mongodb) connection.

## Three steps to finish

### 1. Run the migration (once Atlas accepts connections)

```bash
php artisan app:migrate-sqlite-to-mongodb --force
```

Expected output: `users: 1 inserted, 0 already present`,
`activity_log: 11 inserted, 0 already present`, index creation, and a
verification table with `✔`.

### 2. Flip the runtime config

In `.env` (and `.env.example` for fresh setups):

```ini
DB_CONNECTION=mongodb
SESSION_DRIVER=mongodb
CACHE_STORE=mongodb
QUEUE_CONNECTION=mongodb
```

Then `php artisan config:clear`.

(phpunit.xml already pins `DB_CONNECTION=sqlite` + `:memory:` for tests, so
the suite keeps working unchanged. The `sqlite` connection stays defined in
`config/database.php` for tests and rollback.)

### 3. Smoke test

```bash
php artisan tinker --execute="echo App\Models\User::count();"   # 1
php artisan config:clear && php artisan serve
# - log in at /admin/login with the seeded admin
# - POST a blog/project → confirm it appears on / and /api/v1/*
# - activity log page shows the new entries
php artisan test
```

## If connectivity is still flaky (rotating egress IP)

The command retries through TLS bursts (deadline `--timeout=120` by default).
If bursts outlast it, re-run — it's idempotent. For a deterministic path while
the allowlist is incomplete, run against the reachable secondary's *sibling*
primary the moment `hello` reports it writable:

```bash
# discover current primary (works on any reachable member):
php -r "\$c=new MongoDB\Client(getenv('U'));print_r(\$c->admin->command(['hello'=>1])->toArray()[0]['primary'] ?? 'none');"
# then point MONGODB_URI at that host with ?directConnection=true and run the command
```

(Replace the env `U` with your full credentials DSN; never commit it.)

## Rollback

Set `DB_CONNECTION=sqlite`, `SESSION_DRIVER=database`, `CACHE_STORE=database`,
`QUEUE_CONNECTION=database` and `php artisan config:clear`. SQLite data was
never deleted — the migration only copies.
