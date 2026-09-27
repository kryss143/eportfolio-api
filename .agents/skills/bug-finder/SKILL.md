---
name: bug-finder
description: Hunt for real bugs in a codebase like a senior software engineer (10+ years) - systematic review across security, correctness, error handling, resilience, and performance, with runtime verification, judgment about what's actually a bug, and a severity-ranked report. Use this skill whenever the user asks to find bugs, audit code, review for defects, do a code review, or "check what's broken" in a feature, module, or whole project - even if they don't say "audit" or name a framework.
---

# Bug Finder

Review code the way a senior engineer would: understand the system and its
conventions first, hunt systematically by bug class, **verify every finding
before reporting it**, exercise judgment about what's actually broken vs.
intentional, and rank by real-world impact — not by how interesting the code
looks.

## Core principles

1. **Impact over volume.** A bug that locks every admin out of the panel beats
   ten style nits. Severity reflects user-facing and data-facing consequences,
   not how clever the catch was.
2. **Never report unverified findings.** Static review finds suspects; a finding
   only becomes a bug when you can reproduce it (runtime test, targeted script,
   or an airtight trace through the actual framework/language runtime). Label
   anything unproven as "suspected" and say exactly what would confirm it.
3. **Trust nothing at the boundaries.** Every request input, every fallback
   path, every "impossible" state is suspect.
4. **The happy path lies.** Most bugs live in degraded modes: DB down, empty
   collections, invalid input, concurrent requests, first run after seed,
   partial network failure, clock/timezone edge cases.
5. **Know the difference between a bug and a decision.** Before flagging
   something, check whether it's actually intentional (a deliberate trade-off,
   a documented limitation, a TODO already tracked elsewhere). Read commit
   history/blame and nearby comments when it's ambiguous — a senior engineer
   doesn't file a ticket against code they haven't understood yet. If genuinely
   unsure, report it as a question ("is this intentional?") rather than a
   confident defect.
6. **Read the conventions before judging deviations from them.** A pattern
   used consistently across the codebase is a convention, even an unusual one;
   a pattern used once, differently from everywhere else, is a lead worth
   checking.
7. **Adapt the checklist to the actual stack.** The categories below use
   Laravel/PHP examples for concreteness because they're common and illustrate
   the failure mode clearly, but every category applies to any
   framework/language. Map each item to the equivalent concept in the stack
   you're actually looking at (e.g. mass assignment → Rails strong params,
   Django form/serializer fields, or manual DTO mapping in Go/Java) rather than
   skipping categories because the exact API name doesn't match.

## Fast first pass: scripts/scan_bug_patterns.py

Before the manual hunt, run the bundled heuristic scanner to surface leads
quickly:

```bash
python3 scripts/scan_bug_patterns.py --root <path to project>
```

It greps every source file under `<root>` for text patterns that commonly
indicate real bugs — hardcoded secrets, string-built SQL, overly broad or
empty catch blocks, swallowed promise rejections, TODO/FIXME/HACK markers,
loose equality, unbounded fetch-alls, queries inside loops, leftover debug
prints, commented-out code — grouped by category (security, error_handling,
correctness, performance, code_health). Limit to specific categories with
repeated `--category` flags (e.g. `--category security --category error_handling`).
It writes a triage list to `eportfolioAPI/bugs-reported/pattern-scan.md`
(default; override with `--output`).

**Every hit is a lead, not a confirmed bug.** This is regex-based and will
have false positives (an intentional broad catch, safely parameterized SQL
that happens to contain a `+`, a comment that merely mentions "TODO" in
prose). Use the list to focus the manual hunt below, then run each promising
hit through the same verification protocol as anything found by eye — never
copy a scanner hit straight into the report without reading and confirming it
against the real code. Findings the scan missed (logic errors, degraded-mode
inconsistencies, authorization gaps) are exactly why the manual checklist
still runs in full — the scanner only catches what's expressible as a text
pattern.

## Workflow

1. **Map the scope.** Identify the language/framework(s), database
   connections, entry points (routes/controllers/handlers), models, and
   views/serializers in scope. Note multi-database or multi-service setups —
   they are bug goldmines (wrong-connection queries, connection-specific
   validation rules, inconsistent degradation).
2. **Trace one request end-to-end** before hunting: entry point → middleware/
   interceptors → validation → model/service → response. This reveals the
   conventions that deviations from become bugs. Skim recent commit history for
   the area in scope — recently-touched code is disproportionately likely to
   carry the bug the user is asking about.
3. **Run `scripts/scan_bug_patterns.py`** for a fast first pass, then **hunt by
   category** using the checklist below — the scan surfaces leads, the
   checklist covers what text patterns can't (logic, authorization,
   degraded-mode consistency). Read the actual code — do not pattern-match
   from memory of "how frameworks usually work"; verify the version/config
   actually in use where behavior varies by version.
4. **Verify at runtime.** Write a throwaway script or use a REPL/tinker
   equivalent to reproduce each suspected bug against the real code. Confirm
   the fix actually resolves it — mentally trace it if execution isn't
   possible, but say so. Delete throwaway scripts afterward.
5. **Report** in the format below, with a fix order and, if fixes are applied,
   the smallest correct diff per fix — not an opportunistic refactor of
   everything nearby.

## Hunting checklist

### Security
- [ ] **Mass assignment / over-posting:** is every allow-list (`$fillable`,
  strong params, serializer `fields`, DTO mapping) complete and minimal?
  Missing fields silently drop on create/update (e.g. an `is_admin` flag that
  never persists); extra fields like `id`/`role` let clients overwrite keys
  they shouldn't touch.
- [ ] **Authorization on every mutating endpoint:** is the check present *and*
  actually enforced (role checked after auth, not just assumed by route
  grouping)? Confirm it can't be bypassed by hitting the endpoint directly with
  a non-privileged session/token.
- [ ] **Unvalidated input reaching queries:** `sort`, `direction`, `per_page`,
  `status`, enum values, free-text search terms. Crafted values can throw
  500s, cause unbounded dumps, or (for raw/string-built queries) enable
  injection. Cap paginators, whitelist enums, whitelist sort columns,
  parameterize everything.
- [ ] **Error responses leaking internals:** stack traces, SQL, file paths,
  internal IPs returned to clients.
- [ ] **CSRF/CORS on all state-changing requests**; method-override spoofing
  handled correctly.
- [ ] **Secrets committed** (`.env`, API keys, tokens in code/history) and
  debug/verbose modes left enabled in non-dev config.

### Correctness
- [ ] **Validation rules vs. the right connection/table.** With multiple DB
  connections, a bare `unique:table,column` may resolve the *default*
  connection, not the model's — use the model-qualified form. Verify key
  column names for non-standard/non-integer primary keys.
- [ ] **Query features the driver/ORM can't actually compile:** aggregation or
  raw-select syntax that works on one backend but throws or returns unexpected
  key shapes (`_id` vs `id`) on another. Prefer in-language grouping for small
  collections when the query layer is unreliable across backends.
- [ ] **Error swallowing:** a catch block broad enough (`catch (\Throwable)`,
  bare `except:`, empty `catch {}`) to convert *any* failure into a
  fallback/default/200 response. Catch only the specific exception types
  expected; let everything else propagate or log loudly.
- [ ] **Degraded-mode consistency:** when the primary data source is down or
  empty, do *all* sibling endpoints degrade the same way? Do filters,
  pagination envelopes, date formats, and query strings survive the fallback
  path? An endpoint that returns unfiltered mock data for a filtered request is
  lying with a 200.
- [ ] **Empty vs. missing:** does "collection empty" trigger the same fallback
  path as "service unavailable"? Paginated vs. plain collections often take
  different branches and diverge silently.
- [ ] **Enum/case mismatches:** stored values vs. validated values vs.
  displayed values (`in-progress` vs `IN_PROGRESS`). Invalid values that
  silently match nothing need a 4xx, not an empty result set.
- [ ] **First-run scenarios:** does a fresh migrate/init + seed actually
  produce a usable system (e.g. seeder fields not dropped by the allow-list)?
- [ ] **Concurrency:** race conditions on read-then-write sequences, missing
  row locks on financial/inventory-style updates, non-idempotent retries.

### Error handling & resilience
- [ ] **Catch-block specificity** (see above) and whether exceptions are
  logged with enough context to debug later, not just swallowed silently.
- [ ] **Actions on degraded data:** list views may render action buttons for
  fallback/mock rows that can't actually be mutated (no real backing record).
  Hide actions or short-circuit mutations when data is known-degraded.
- [ ] **HTTP/status codes:** client errors → 4xx, faults → 5xx, never
  200-with-wrong-data.
- [ ] **Partial failures:** uploads, mail, external API calls, background
  jobs — is failure handled, retried where appropriate, and reported, or
  silently dropped?

### Data integrity
- [ ] **Transactions** around multi-step writes; observers/listeners/side
  effects that assume persistence already happened.
- [ ] **Unique constraints enforced in the DB**, not only in application-level
  validation (a race between two validation checks can still double-insert).
- [ ] **Date/format/timezone consistency** between code paths (raw string vs.
  proper datetime type, UTC vs. local, ISO-8601 vs. ad hoc format).

### Performance
- [ ] **N+1 queries** in loops over related records.
- [ ] **Unbounded queries** (`all()`/`SELECT *` with no limit) on tables that
  grow without bound.
- [ ] **Missing indexes** on columns used in frequent `WHERE`/`JOIN`/`ORDER BY`
  clauses, if schema/migrations are in scope.

### Code health
- [ ] **Dead code:** uncalled methods, unused parameters, copy-pasted logic
  that has drifted from its original source.
- [ ] **Duplication** between controllers/services/handlers that has already
  diverged in subtle, bug-prone ways.

### Views / frontend
- [ ] **Broken markup:** unclosed tags leaking one section's markup/styling
  into another (check conditional banners, flash/toast blocks).
- [ ] **Missing/null keys:** code iterating data whose shape differs between
  the normal and fallback/error paths — guard optional keys instead of
  assuming shape.
- [ ] **Escaping of user-supplied content** (auto-escaped interpolation vs.
  raw/unescaped output) — flag any raw output of user-controlled data.

## Verification protocol

For each suspected bug, before reporting:
1. Identify the exact file and line. Quote the code.
2. Reproduce: run the request/query against real code (REPL script, feature
   test, HTTP call). Capture the actual output/error, not an assumed one.
3. Confirm the fix: apply the corrected logic and re-run to prove the
   difference. Mark findings "Fixed + verified" only when this is done.
4. Clean up throwaway verification scripts — don't leave debug artifacts in
   the repo.

If reproduction is impossible in this environment (needs prod data, an
external service, specific infra), mark it 🟠 **Suspected** with the concrete
repro steps a developer should run to confirm it themselves.

## Report format

Save the report as a single markdown file under `eportfolioAPI/bugs-reported/`.
Check first whether that folder already exists: if not, create it (e.g.
`mkdir -p` or the equivalent); if it does exist, leave the folder and any
files already in it alone — never delete or recreate the directory, and never
overwrite an existing report, only add a new one. Name the file
`BUGS-<date>.md` or `AUDIT-<area>-<date>.md`. This folder is the handoff point
to the bug-fix skill — every file here should be a real, ready-to-fix bug
report, not a draft, since the bug-fix skill treats everything in
`eportfolioAPI/bugs-reported/` as pending work. The document should contain:

1. **Header:** date, scope, stack/framework identified, method (static +
   runtime).
2. **Summary table:** `# | Severity | Title | Status`.
3. **Per finding:** severity, file:line, code snippet, **Impact** (what
   actually breaks, with runtime evidence), **Recommended fix** (concrete,
   minimal code — not a rewrite), **Residual risk** if any (e.g. "this class
   of bug likely recurs in sibling endpoint X — worth a follow-up pass").
4. **"Verified fine" section:** what you checked and found sound, including
   anything that looked suspicious at first but turned out intentional — this
   is as valuable as the bug list and prevents re-auditing the same ground.
5. **Suggested fix order:** by dependency and user impact (e.g. "fix the one
   that blocks all admin access first").
6. **Fix log:** dated list of applied fixes with per-fix verification notes,
   filled in only as fixes are actually applied.

### Severity rubric

- 🔴 **Critical** — blocks core functionality entirely (cannot log in, cannot
  save content) or is exploitable for data loss/unauthorized access.
- 🟠 **High** — wrong data served, errors swallowed silently, feature broken in
  a common degraded mode, exploitable-ish input handling.
- 🟡 **Medium** — inconsistent behavior between paths, missing validation on
  non-critical input, degraded UX with a workaround.
- 🟢 **Low** — dead code, duplication, footguns not currently reachable,
  format inconsistencies.

## Tone

Direct, technically precise, no hedging beyond what the evidence warrants —
say "confirmed" vs. "suspected" accurately rather than dressing up a guess as
a fact. Don't inflate severity to make the audit look more valuable, and don't
manufacture findings to fill out every category — a short, accurate report
beats a padded one.

## Final self-check

- [ ] Every reported bug has file:line and runtime evidence (or is explicitly
      marked Suspected with repro steps).
- [ ] At least one degraded-mode scenario traced end-to-end (dependency down,
      empty DB, first run).
- [ ] All input parameters on public endpoints validated (type, bounds, enum).
- [ ] Every catch/except block examined for over-breadth.
- [ ] Anything that looked like a bug but turned out intentional is recorded
      in "Verified fine", not silently dropped.
- [ ] "Verified fine" section written.
- [ ] Fix order proposed; any applied fixes are minimal diffs, each verified
      by re-run.
- [ ] Throwaway scripts deleted; no debug artifacts left behind.