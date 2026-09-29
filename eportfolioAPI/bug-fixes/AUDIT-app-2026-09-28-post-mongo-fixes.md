# Bug Fix Report — AUDIT-app-2026-09-28-post-mongo.md — 2026-09-28

Source: `bugs-reported/AUDIT-app-2026-09-28-post-mongo.md` (7 findings). All 7 fixed. Verification: `php artisan test` → **30 passed, 105 assertions** (8 new regression tests in `AuditPostMongoTest`); live repro re-run — with Mongo down (closed-port DSN, production-shape drivers) `/`, `/admin/login`, `/api/v1/blogs` all return **200** (were 500/500/200); `migrate:refresh --seed` green; `mongo:check --indexes` → "All declared indexes match".

🟢 **Bug 1 (Critical) — degraded mode unreachable: every page 500'd when Mongo was down**
   Root cause: `SESSION_DRIVER=mongodb` (and `CACHE_STORE=mongodb`) — the session middleware runs before any controller, so every page (including `/admin/login`) died on an unhandled `ConnectionTimeoutException` before any mock-fallback code could execute. The entire degraded-mode design was dead code; hidden from the suite because phpunit used `SESSION_DRIVER=array`. Fix: sessions and cache moved to the `file` driver (`.env`, `.env.example` — with rationale comments; queue stays on `mongodb`, workers tolerate outages). Verified: the new `test_pages_render_with_production_session_driver_while_mongo_is_down` pins the DSN to a closed port **while forcing the file session driver** (the production shape that hid the bug) and asserts `/` + `/admin/login` render; plus the live server repro above.

🟢 **Bug 2 (High) — index name/spec conflict → CommandException code 86**
   Root cause: index *names* were pinned in migrations but key *specs* drifted between code paths (`activity_log.idx_created_at` existed as `{created_at: 1}` while another path created `{created_at: -1}` under the same name) → same name + different key = code 86, unhandled 500 on the write path. Fix: new `app/Support/MongoSchema.php` is the **single index authority** — migrations declare collections + index specs through `MongoSchema::createCollection()/ensureIndexes()`, which diffs `listIndexes()` against the declared specs, **repairs** same-name/different-key drift (drop + recreate with the declared spec) and skips exact matches (idempotent). Verified: `test_ensure_indexes_repairs_drifted_index_spec` builds the exact drifted state that threw code 86, runs ensureIndexes, and asserts convergence to the declared key plus idempotent re-run; `mongo:check --indexes` doctor diffs/repairs live and reports per-index ✔/✘.

🟢 **Bug 3 (Medium, security) — login confirmed passwords for non-admin users**
   Root cause: `Auth::attempt()` ran first; the `is_admin` check happened after, logging the user out with the distinct error *"You do not have admin access"* — a free credential oracle. Fix: role folded into the credential query: `Auth::attempt($credentials + ['is_admin' => true])` — wrong-role takes the identical generic-failure path. Note: the boolean is load-bearing — Mongo compares strictly and the stored flag is a real bool, so `1` never matches (verified live before implementing). Verified: `test_non_admin_with_valid_password_gets_generic_error` (asserts the exact generic message + guest) and `test_admin_login_still_works`.

🟢 **Bug 4 (Medium) — blog bulk-delete left zero audit trail**
   Root cause: `Blog::whereIn('_id', $ids)->delete()` is a query-builder mass delete — models never instantiate, so `ActivityObserver::deleted()` never fires (the project controller already iterated models; inconsistent). Fix: blog `bulkDelete()` fetches models and deletes one by one, matching the project path. Verified: `test_blog_bulk_delete_logs_activity_for_each_document` asserts one `deleted` activity row per document.

🟢 **Bug 5 (Medium) — Skill singleton had no uniqueness (silent edit-loss shape)**
   Root cause: `Skill::firstOrCreate([])` matched on nothing with no unique index — concurrent saves could create duplicate documents, after which `Skill::first()` arbitrarily picked one. Design note: a fixed string `_id` was tried first and **rejected after live testing** — the package's Eloquent layer treats `_id` as an ObjectId and inserted fresh ObjectIds anyway. Final fix: unique index on a normal `key` field (`Skill::SINGLETON_KEY = 'skills-singleton'`, `uniq_key` index in the skills migration, which also pre-creates the singleton doc via upsert) + `Skill::singleton()` accessor; admin controller edit/update both target it. Verified: `test_skill_singleton_uses_fixed_document_id` (same `_id` on repeated singleton() calls, count stays 1, write targets the singleton).

🟢 **Bug 6 (Low) — degraded-mode admin search was case-sensitive (parity broken)**
   Root cause: admin mock-path filters used `str_contains()` with raw casing, while the live path's `like` operator is case-insensitive on Mongo (verified during the audit) — "GRABCAT" matched nothing in degraded mode but matched on the healthy path. Fix: both sides lowercased via `mb_strtolower()` in `Admin/ProjectController` and `Admin/BlogController` mock filters (`FallbackData::applyMockFilters()` already did this correctly). Verified: `test_admin_mock_search_is_case_insensitive` pins Mongo down and asserts an upper-case needle matches mixed-case mock titles.

🟢 **Bug 7 (Low, hardening) — bulk-delete accepted arbitrary id strings**
   Root cause: `'ids.*' => ['string']` let malformed non-ObjectId strings reach `whereIn('_id', ...)` where driver cast behaviour is inconsistent. Fix: `'ids.*' => ['string', 'regex:/^[a-f0-9]{24}$/i']` in all three bulk-delete endpoints (projects, blogs, tech-skills). Verified: `test_bulk_delete_rejects_malformed_ids` (junk ids rejected at validation with a field error).

## Files changed

- `.env`, `.env.example` (Bug 1 — SESSION_DRIVER/CACHE_STORE → file, with rationale comments)
- `app/Support/MongoSchema.php` (new — Bug 2 single index authority: ensureIndexes/createCollection/dropCollection + declaration registry)
- All 9 schema migrations (Bug 2 — index specs declared through MongoSchema; skills migration also pre-creates the singleton, Bug 5)
- `app/Console/Commands/MongoCheckCommand.php` (Bug 2 — `--indexes` doctor flag: diffs + repairs + per-index report)
- `app/Http/Controllers/Admin/AuthController.php` (Bug 3)
- `app/Http/Controllers/Admin/BlogController.php` (Bugs 4, 6, 7)
- `app/Http/Controllers/Admin/ProjectController.php` (Bugs 6, 7)
- `app/Http/Controllers/Admin/TechSkillController.php` (Bug 7)
- `app/Models/Skill.php` (Bug 5 — SINGLETON_KEY, `key` fillable, singleton() accessor)
- `app/Http/Controllers/Admin/SkillController.php` (Bug 5 — edit/update target the singleton)
- `database/seeders/ContentSeeder.php` (Bug 5 — seed the singleton with its fixed key)
- `tests/Feature/AuditPostMongoTest.php` (new — 8 regression tests, one per fix + the admin-login-still-works control)

## Residual risk / follow-ups

- Bug 1: `SESSION_DRIVER=file` means multi-server deployments need sticky sessions or a shared store (redis/memcached/mongodb-with-mongo-healthy) — revisit if the app ever scales horizontally; single-box dev/prod-shape is correct as-is.
- Bug 2: `MongoSchema::ensureIndexes()` only repairs indexes declared in *this* process's migrations; `mongo:check --indexes` covers the full set in one command. Orphaned indexes (names not in any declaration) are intentionally left untouched.
- Bug 5: databases created before this fix may hold duplicate skills documents; dedupe manually if a duplicate is suspected (`db.skills.find({}, {_id: 1}).count() > 1`), keeping the one with `key: 'skills-singleton'`.
- The suite still pins `MONGODB_DATABASE=eportfolio_testing` + drops collections per test (TestCase guard refuses any other database) — unchanged and still load-bearing.
