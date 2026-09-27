# Bug Fix Report — AUDIT-app-2026-09-28-r2.md — 2026-09-28

Source: `bugs-reported/AUDIT-app-2026-09-28-r2.md` (3 findings). All 3 fixed. Verification: `php artisan test` → **14 passed, 33 assertions, 9.34s** (7 new r2 regression tests + the 7 existing round-1 tests still green); changed files `php -l` clean. `pattern-scan.md` (raw scanner output) was re-generated during the r2 audit and fully triaged there; its leads are either covered by the r2 findings or documented as false positives in that report.

🟢 **F1 — withFallback() substituted mock data for a truthful empty filtered result from a live DB**
   Root cause: the empty-result branch fired on *any* empty paginator/collection, including an empty *filtered* view of a populated live collection (e.g. `?status=published` on an all-drafts DB) — while the single-record helper already guarded via `collectionHasDocuments()`. Fix: same guard added to the list path — on empty, fall back to mock only when the collection has no documents at all; otherwise return the DB's truthful empty result. Correctly placed inside the try: a DB-down `collectionHasDocuments()` call throws a connection error and still reaches the mock fallback. Verified: `empty filtered result of populated collection is not substituted` (identical paginator identity returned), `empty result of truly empty collection still falls back to mock`, `connection failure still falls back to mock` — all three branches pinned via a harness overriding only `collectionHasDocuments()`.

🟢 **F2 — MongoProbe memoization never expired under single-process servers**
   Root cause: static `$available`/`$writable` verdicts lived for the whole process lifetime, so `php artisan serve` (the project's `composer dev` workflow) kept serving 500s on admin pages after an outage began post-boot — and even after Mongo recovered. Fix: 5-second TTL on both memoized verdicts (`$availableAt`/`$writableAt` timestamps + `memoIsFresh()`); `flush()` also clears the timestamps. Verified: `probe memoizes within ttl` (second call instant vs first ~1s), `probe re probes after ttl expires` and `write probe re probes after ttl expires` (a 10s-stale memo provably triggers a real re-probe — timings assert the difference).

🟢 **F3 — Admin blogs mock search covered title only (live path searches title + excerpt)**
   Root cause: degraded-mode filter diverged from the healthy path. Fix: excerpt clause added to the mock filter, mirroring the DB query. Verified: `admin blog mock search matches excerpt not title only` — searches "breakdown" (present only in a mock excerpt) and asserts the matching post is served; the old filter returned nothing for it.

## Files changed

- `app/Http/Controllers/Api/V1/FallbackData.php` (F1)
- `app/Support/MongoProbe.php` (F2)
- `app/Http/Controllers/Admin/BlogController.php` (F3)
- `tests/Feature/Audit20260928R2Test.php` (new — 7 regression tests covering all three fixes)

## Residual risk / follow-ups

- F1: the guard adds one `limit(1)` round trip per empty result only (same trade-off `withFallbackSingle()` already accepted) — no cost on non-empty hot paths.
- F2: TTL is a hard-coded 5s constant; if probes ever need per-environment tuning, promote it to config. Worst-case added latency is one full probe cycle per 5s window per probe type (bounded by the 1.5s/2.5s driver timeouts).
- The r1 note still stands: `find_bug_files.py` lists `vendor/` CHANGELOGs in the discovery log — script noise, out of scope here.
