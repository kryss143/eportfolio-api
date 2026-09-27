# Bug Fix Report — AUDIT-app-2026-09-28.md (+ pattern-scan.md) — 2026-09-28

Source: `bugs-reported/AUDIT-app-2026-09-28.md` (5 findings). All 5 fixed; 1 additional bug (F6) discovered by the new regression suite and fixed same-pass. Verification: `php artisan test` → **7 passed, 15 assertions, 1.87s** (Mongo pinned down per phpunit.xml, so API tests exercise the mock-fallback path deterministically); all changed files `php -l` clean; F5 tooling fix verified in a temp dir.

🟢 **F1 — ActivityObserver::updated() crashed every Project update (500, no audit row)**
   Root cause: `array_diff_assoc(getAttributes(), getOriginal())` compared raw JSON strings against cast-decoded arrays → `Array to string conversion` → `ErrorException` under Laravel's HTTP error handler. Fix: use `$model->getChanges()` (populated by `syncChanges()` before the `updated` event, before `syncOriginal()`), so the diff covers exactly the written keys with no array-to-string coercion. Verified: `Audit20260928Test::test_save_with_json_cast_attributes_logs_changed_keys_only` + `test_save_without_changes_logs_nothing` (real save lifecycle via a SQLite stand-in model with identical `json` casts — vanilla Eloquent stores those as JSON strings, the same shape that crashed the observer).

🟢 **F2 — Public API served draft blog posts (index default + show by slug)**
   Root cause: `whereNotNull('date')` was only applied when the client passed `?status=published`, and `show()` had no draft check — violating the codebase's own landing-page/dashboard invariant. Fix: drafts excluded by default in `index()` (explicit `?status=draft` still works), `whereNotNull('date')` added to `show()`, and the mock-fallback filter in `FallbackData::applyMockFilters()` mirrors the same default so degraded mode serves exactly what the healthy mode would. Verified: three API feature tests against the mock-fallback path (`...excludes_drafts_by_default`, `...serves_mock_blog_by_slug`, `...honors_explicit_draft_filter`).

🟢 **F3 — update() in Project/Blog/TechSkill admin controllers lacked the Mongo-down write guard**
   Root cause: the 2026-09-25 primary-partition fix series guarded store/destroy/toggle/bulk but skipped the three `update()` methods → unhandled driver exception (500) after form submit during a primary partition. Fix: same `denyWhenMongoDown('<index route>')` guard as the sibling methods, first statement of each `update()`. Verified: `php -l` on all three controllers + pattern parity re-checked via grep (every Mongo-backed mutation in `Admin/` now guarded; `SkillController` had its own strict-primary check already).

🟢 **F4 — Package deprecation (USER_DEPRECATED) on every content write for `array` casts**
   Root cause: `mongodb/laravel-mongodb` v5.9 stores array-cast values as JSON strings and emits a deprecation warning for that combination; `Project` (2 casts) and `Skill` (7 casts) hit it on every create/update. Fix: casts changed `'array'` → `'json'` (the package-sanctioned cast for JSON-string storage; identical decode behavior for resources and mock filters). Verified: runtime — the tinker probe that previously printed the deprecation now runs silent; static — `grep "=> 'array'" app/Models/` matches only `ActivityLog` (a SQLite/vanilla-Eloquent model where the cast is correct and the Mongo deprecation does not apply).

🟢 **F5 — scan_bug_patterns.py hardcoded `eportfolioAPI/` output prefix (nested dir when root IS eportfolioAPI)**
   Root cause: default output path `os.path.join(root, "eportfolioAPI", "bugs-reported", ...)` assumed the script was always run from the repo parent; same defect in `find_bug_files.py`. Fix: default output resolved as `<root>/bugs-reported/pattern-scan.md` (and `<root>/bug-fixes/bug-fixes.md`), and the stray `"eportfolioAPI"` entry removed from `SKIP_DIRS` (it would have silently skipped the real code directory when scanning the repo root). Verified: temp-dir run — output lands at `<root>/bugs-reported/`, no nesting; nested artifacts from this session relocated into `bugs-reported/` and `bug-fixes/` and the empty dirs removed.

🟠 **F6 — Mongo driver rejected env-provided timeouts (found by the new tests, fixed same-pass)**
   Root cause: `env()` returns strings, so phpunit.xml's `MONGODB_CONNECT_TIMEOUT_MS=100` reached the driver as `"100"` → `Expected 32-bit integer for "connectTimeoutMS" URI option` — any deployment setting these vars via env (tests, CI) 500'd on the first Mongo connection attempt; hidden in dev only because the local `.env` omits them. Fix: `(int) env(...)` casts in `config/database.php` for all three timeout options (the config layer owns type casting). Verified: full suite green after the cast (was 4 failures before).

## Files changed

- `app/Observers/ActivityObserver.php` (F1)
- `app/Http/Controllers/Api/V1/BlogController.php` (F2)
- `app/Http/Controllers/Api/V1/FallbackData.php` (F2, mock-path parity)
- `app/Http/Controllers/Admin/ProjectController.php`, `BlogController.php`, `TechSkillController.php` (F3)
- `app/Models/Project.php`, `app/Models/Skill.php` (F4)
- `config/database.php` (F6)
- `../.agents/skills/bug-finder/scripts/scan_bug_patterns.py`, `../.agents/skills/bug-fix/scripts/find_bug_files.py` (F5)
- `tests/Feature/Audit20260928Test.php` (new regression tests, F1+F2)

## Residual risk / follow-ups

- F2: confirm no consumer relies on reading drafts via `/api/v1/blogs*` before deploying (nothing in this repo does).
- F1: activity-log `new_values` now stores raw write values (JSON strings for cast fields). The admin activity view renders only changed *keys* today; if per-value diffs are wanted later, decode `json`-cast keys at render time.
- F3: with Mongo fully down (not just the primary), route-model binding still fails before the controller — pre-existing, broader than the partition scenario these guards target.
- The discovery log `bug-fixes/bug-fixes.md` lists `vendor/` CHANGELOGs as pending sources — script noise; `find_bug_files.py` should skip `vendor/` like its sibling scanner does (not fixed here — out of the reported scope).
