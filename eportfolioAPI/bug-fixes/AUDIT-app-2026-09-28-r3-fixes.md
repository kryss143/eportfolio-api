# Bug Fix Report — AUDIT-app-2026-09-28-r3.md — 2026-09-28

Source: `bugs-reported/AUDIT-app-2026-09-28-r3.md` (3 findings). All 3 fixed — with two report assumptions corrected against the real code before fixing (noted below). Verification: `php artisan test` → **17 passed, 44 assertions, 13.82s** (3 new r3 regression tests + all 14 prior tests green); changed files `php -l` clean; scanner output (`pattern-scan.md`) fully triaged inside the r3 report itself.

🟢 **F1 — Probe latency documentation claimed ~1s/~1.5s; measured down-mode cost is ~2.5s**
   Root cause: the probe's `$maxWaitMs` budget is checked only *between* attempts, so with the server down one attempt always burns the full server-selection cycle (`serverSelectionTimeoutMS=2500`) before any check runs — the documented envelopes were false. Fix: **documentation option** (as the report offered): both comment blocks (`MongoProbe.php`, `config/database.php`) now state the real ~2.5s envelope, how it arises, and that making the budget real (capping `serverSelectionTimeoutMS` ≤ `maxWaitMs`) is a deliberate owner decision because it trades against the documented Atlas TLS-burst tolerance — `serverSelectionTryOnce=false` stays as-is. Verified: measured timings recorded in the r3 report (2.55s/2.50s); no behavior change intended or observed; suite green.

🟢 **F2 — View composer fired write-probe on the public login page** *(report's fix was incomplete — corrected)*
   Report proposed narrowing to `admin.layouts.app`, but pre-fix verification showed **five index views** also consume `$mongoAvailable`/`$mongoWritable` for row-action gating — that fix would have broken them. Fix: `View::composer(['admin.layouts.app', 'admin.*.index'], ...)` — covers every consumer (verified by grep before and by test after) while removing the probes from the login page and all create/edit pages. Verified: `login page does not trigger mongo probes` (HTTP test asserting both probe timestamps stay null across a full request) and `index views still receive probe variables` (auth + `/admin/projects` asserts both timestamps set).

🟢 **F3 — Landing degraded path diverged from DB-path blog semantics** *(report's fix was wrong — corrected)*
   Report proposed `take(5)` — but the landing view renders **all** blogs (the take-5 widget is the admin dashboard's). The real divergence: the DB path is published-only + date-desc, while the mock path served file-order and would render an undated mock post as a broken date. Fix: mock branch applies the same semantics — `filter(fn ($b) => ! empty($b['date']))` + `sortByDesc('date')` — so degraded output always matches healthy-mode ordering and never fabricates a date for a draft. Verified: `landing degraded path orders mock blogs newest first` (HTTP test: newest post's rendered date string precedes the oldest's).

## Files changed

- `app/Providers/AppServiceProvider.php` (F2 — composer scope)
- `app/Http/Controllers/LandingController.php` (F3 — degraded-path blog semantics)
- `app/Support/MongoProbe.php` + `config/database.php` (F1 — documentation corrected to measured reality)
- `tests/Feature/Audit20260928R3Test.php` (new — 3 regression tests)

## Residual risk / follow-ups

- F1: if the ~2.5s degraded-mode stall ever becomes a product problem, the functional option (cap `serverSelectionTimeoutMS` ≤ ~900ms and accept the documented burst-tolerance trade-off) remains available — it is now correctly documented as an owner decision, not silently changed.
- F2: if a future admin view consumes the composer variables outside the layout/index patterns, the composer scope must be extended (the coverage test will catch the index case; a new *non-index* consumer would silently get `undefined variable` — worth remembering).
- Suite duration is now ~14s, dominated by deliberate real-probe TTL tests (2.5s each) — acceptable, but if it grows, consider faking the probe clock in tests instead of waiting out real selection cycles.
