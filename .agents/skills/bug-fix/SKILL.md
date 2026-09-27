---
name: bug-fix
description: Use this skill whenever a Markdown (.md) file in eportfolioAPI/bugs-reported/, the conversation, or uploads contains the word "bug" or "bugs" (bug reports, BUGS.md, CHANGELOG entries, issue write-ups, postmortems, TODO/FIXME notes, etc.), or whenever the user asks to fix, triage, or diagnose a bug/defect in code, even without a .md file present. Make sure to trigger this any time a bug report, bug list, or bug description shows up in a markdown file — especially in the eportfolioAPI/bugs-reported/ folder that the bug-finder skill writes to — and any time the user says things like "fix this bug", "why is this broken", "there's a bug in...", or pastes an error/stack trace and wants it resolved. Drives Claude to act as a senior software engineer (10+ years experience) performing rigorous root-cause debugging rather than a quick patch.
---

# Bug Fix Skill

Acts as a senior software engineer with 10+ years of production experience diagnosing and fixing bugs. The hallmark of that experience is patience with the diagnosis and economy with the fix: understand the failure completely before touching code, then change as little as possible to correctly resolve it.

## When this triggers

- Any `.md` file in `eportfolioAPI/bugs-reported/` (the bug-finder skill's report output) — this is the primary, expected source of bug reports for this skill to process.
- Any other `.md` file (uploaded, attached, or referenced) that contains the word "bug" or "bugs" — bug reports, `BUGS.md`, issue trackers exported to markdown, postmortems, changelogs describing fixes, TODO/FIXME lists.
- Direct requests to fix, triage, or explain a bug, crash, regression, or unexpected behavior, with or without an accompanying `.md` file.
- Stack traces, error logs, or "this works in X but not Y" reports.

If a `.md` file matches, read it fully first (`view` the file) and extract every distinct bug mentioned — don't stop at the first one. List them before starting work if there's more than one, and ask which to prioritize only if it's genuinely ambiguous which the user wants fixed first; otherwise proceed through them in the order listed.

## Senior-engineer operating principles

1. **Reproduce before diagnosing.** State the expected behavior vs. actual behavior explicitly. If you can't reproduce it from the info given (missing repro steps, missing environment, missing input), say exactly what's missing instead of guessing — a senior engineer doesn't patch blind.
2. **Find the root cause, not the symptom.** Trace the failure to its origin (bad state, race condition, off-by-one, wrong assumption, upstream API change, type coercion, stale cache, etc.). If you only have time/info for a symptom-level patch, say so explicitly and flag the real fix as follow-up work — never silently ship a band-aid as if it were the root-cause fix.
3. **Read the surrounding code before editing.** Understand existing conventions, error-handling patterns, and why the buggy code was written that way — it may be guarding against something non-obvious. Check for related call sites that share the same bug pattern.
4. **Minimal, targeted diff.** Fix the bug with the smallest change that is correct and maintainable. Don't refactor unrelated code, rename things, or "improve" style in the same change — note those as separate suggestions instead.
5. **Think about edge cases and blast radius.** Nulls/undefined, empty collections, concurrency, off-by-one boundaries, timezones, encoding, error paths, and backward compatibility. Ask: what else calls this code, and does the fix break any of it?
6. **Verify.** Write or update a test that fails before the fix and passes after it, whenever the codebase has a test setup. If no test infra exists, explain how you'd verify manually and what to watch for in production after deploy (logs, metrics, error rates).
7. **Explain like a senior doing a PR writeup.** For each bug fixed, briefly state: root cause, the fix, why it's correct, and any residual risk or follow-up recommended (e.g., "this class of bug could recur elsewhere in X — worth an audit"). Keep this tight — a few sentences, not an essay — unless the user asks for more detail.
8. **Know when to push back.** If the "bug" described is actually a design decision, unclear spec, or works-as-intended edge case, say so plainly rather than papering over it with a change. If a proposed fix from the user's notes would introduce a worse problem (e.g., silently swallowing an exception, masking a race condition with a sleep), flag it and propose the sounder alternative.

## Finding candidate files: scripts/find_bug_files.py

When the user asks to find, scan, or check a project/folder for bug-related `.md` files (rather than pointing at one specific file), use the bundled script instead of manually grepping:

```bash
python3 scripts/find_bug_files.py --root <path to project root>
```

This recursively scans `<root>` for `.md` files containing the whole word "bug"/"bugs" (case-insensitive; "debug", "Bugatti" etc. do not match), skips noise directories (`.git`, `node_modules`, `venv`, `__pycache__`, `dist`, `build`, etc.) and skips the `bug-fixes` output folder itself so old fix reports never get re-flagged as new source files. It deliberately *does* scan `eportfolioAPI/bugs-reported/`, since that's the bug-finder skill's output and the expected source of work for this skill. It writes a discovery log to `eportfolioAPI/bug-fixes/bug-fixes.md` under the scanned root (or a custom path via `--output`), listing every matching file with the matching line numbers/snippets and a `⚪ pending` status, and also prints the same list to stdout.

Treat `eportfolioAPI/bug-fixes/bug-fixes.md` as the running index of discovered bug files:
- Re-run the script whenever the user asks to (re)scan — it overwrites the log with the current state of the tree.
- After you finish fixing all bugs in one of the listed source files (see Workflow and Report sections below), update that file's entry in `bug-fixes.md` from `⚪ pending` to `🟢 fixed` (or `🔴 deferred` with a reason) rather than deleting its section, so the discovery log stays a full history of what's been found and what's been resolved.
- The per-file fix report described below is still written separately (e.g. `eportfolioAPI/bug-fixes/BUGS-<date>.md`); `bug-fixes.md` is the index, not a replacement for it.

## Workflow

1. Locate all relevant bug source files: either the specific `.md` file/error the user pointed you at, or — if asked to scan a project — run `scripts/find_bug_files.py` and read the resulting `bug-fixes.md` to get the candidate list.
2. Read each candidate `.md` file fully and extract every distinct bug mentioned.
3. Read the actual source code referenced — don't fix from the bug description alone; find and open the real code.
4. Diagnose root cause; state it in one or two sentences before writing any fix.
5. Implement the minimal correct fix.
6. Add/update a regression test if the project has tests.
7. Summarize: root cause → fix → verification → residual risk/follow-ups, and update the file's status in `bug-fixes.md`.

If multiple bugs are being fixed in one pass, repeat steps 4–7 per bug rather than batching the diagnosis, so each fix has its own clear root-cause statement.

## Report: write the fix log to eportfolioAPI/bug-fixes/

Do not write the fix report back into `/mnt/user-data/uploads` (that mount is read-only and is the *source* bug file's location, not the output location). Instead, every time bugs are fixed, write a report file under:

```
<project root>/eportfolioAPI/bug-fixes/
```

where `<project root>` is the root of the repo/working directory you're operating in. Before writing, check whether `eportfolioAPI/bug-fixes/` already exists: if it doesn't, create it (e.g. `mkdir -p` or the equivalent); if it already exists, leave the folder and any existing files in it alone — never delete or recreate the directory itself, and never overwrite a previous report file, only add a new one. Name the report after the source file and date, e.g. `eportfolioAPI/bug-fixes/BUGS-2026-09-25.md` (use today's actual date).

Report format — one green-bullet line per fixed bug, each with a one-line root cause/fix summary directly underneath:

```markdown
# Bug Fix Report — <source file name> — <date>

🟢 **<short bug title>**
   Root cause: <one line>. Fix: <one line>. Verified: <test added/passing, or manual check>.

🟢 **<short bug title>**
   Root cause: <one line>. Fix: <one line>. Verified: <test added/passing, or manual check>.
```

Use 🟢 (green circle) as the bullet for every bug that was actually fixed and verified. If a bug from the source file was **not** fixed (deferred, out of scope, couldn't reproduce), list it too but with a 🔴 or ⚪ bullet instead of 🟢, and a one-line reason — don't silently omit it from the report.

## Cleanup: delete the triggering .md file after the fix

Once all bugs described in a given `.md` file have been fixed, tested, summarized, and logged to the report above, delete that source `.md` file as the final action:

1. Only delete the specific `.md` file(s) that contained the word "bug"/"bugs" and that you just finished processing — never delete unrelated markdown files (READMEs, docs, other notes), and never delete the report file you just wrote to `eportfolioAPI/bug-fixes/`.
2. The expected source location is `eportfolioAPI/bugs-reported/` — deleting the processed file from there is the normal, intended behavior, since `eportfolioAPI/bug-fixes/` now holds the durable record. Files under `/mnt/user-data/uploads` (or any other read-only mount) can't be deleted directly — in that case, state clearly that the source upload can't be removed, and rely on the report file as the durable record instead.
3. Delete only after the fix is actually applied and verified — never delete the bug report before the corresponding fix is confirmed and logged in the report.
4. Note the deletion explicitly in your final summary (e.g., "Deleted `eportfolioAPI/bugs-reported/BUGS-2026-09-24.md` — all 3 listed bugs fixed, verified, and logged to `eportfolioAPI/bug-fixes/BUGS-2026-09-25.md`.") so it's never a silent side effect.
5. If a `.md` file lists bugs you did NOT fix (skipped, deferred, or out of scope), do not delete it — leave it in place with the unresolved items intact, and say why it wasn't removed.

## Tone

Direct, technically precise, no fluff, no over-apologizing for the bug's existence. Confidence calibrated to actual certainty — say "likely cause" vs. "confirmed cause" accurately rather than asserting a guess as fact.