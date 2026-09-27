#!/usr/bin/env python3
"""
scan_bug_patterns.py

A fast, language-agnostic FIRST PASS for the bug-finder skill. It greps a
codebase for text patterns that commonly indicate real bugs, and writes a
triage list. It does NOT replace manual review or the verification protocol
in SKILL.md — every hit here is a *lead*, not a confirmed bug. Many hits will
be false positives (intentional broad catches, safe raw SQL with bound
params, etc.); the skill's job is to read each one and confirm or dismiss it.

Usage:
    python3 scan_bug_patterns.py --root /path/to/project
    python3 scan_bug_patterns.py --root /path/to/project --output findings.md
    python3 scan_bug_patterns.py --root /path/to/project --category security

Categories: security, error_handling, correctness, performance, code_health
(default: all)
"""

import argparse
import os
import re
import sys
from datetime import date

SKIP_DIRS = {
    ".git", "node_modules", "venv", ".venv", "__pycache__", "vendor",
    "dist", "build", ".next", ".cache", "target",
    "coverage", ".pytest_cache",
}

CODE_EXTS = {
    ".py", ".js", ".jsx", ".ts", ".tsx", ".php", ".rb", ".java", ".go",
    ".cs", ".c", ".cpp", ".h", ".hpp", ".rs", ".kt", ".swift", ".scala",
    ".vue", ".blade.php",
}

# Each pattern: (category, label, regex, why)
PATTERNS = [
    # --- security ---
    ("security", "hardcoded secret",
     re.compile(r"""(?i)\b(api[_-]?key|secret|password|passwd|token)\b\s*[:=]\s*['"][^'"\s]{6,}['"]"""),
     "Possible hardcoded credential/secret in source."),
    ("security", "string-built SQL",
     re.compile(r"""(?i)(SELECT|INSERT|UPDATE|DELETE)\b.{0,80}["'`]\s*\+|f["']\s*(SELECT|INSERT|UPDATE|DELETE)\b|\.format\(.{0,40}\)\s*.{0,10}(SELECT|INSERT)"""),
     "Query built via string concatenation/formatting instead of parameter binding — possible SQL injection."),
    ("security", "raw/unescaped output",
     re.compile(r"\{!!.*!!\}|dangerouslySetInnerHTML|innerHTML\s*=|\|\s*safe\b"),
     "Unescaped output of potentially user-controlled content — possible XSS."),
    ("security", "debug/verbose mode flag",
     re.compile(r"(?i)\b(debug|DEBUG)\s*[:=]\s*(true|1|True)\b"),
     "Debug mode may be left enabled — check this isn't shipping to prod config."),
    ("security", "disabled cert/verification check",
     re.compile(r"(?i)verify\s*=\s*False|rejectUnauthorized\s*:\s*false|NODE_TLS_REJECT_UNAUTHORIZED"),
     "TLS/certificate verification appears disabled."),

    # --- error_handling ---
    ("error_handling", "overly broad catch",
     re.compile(r"except\s*:\s*$|except\s+Exception\s*:|catch\s*\(\s*\\?Throwable|catch\s*\(\s*Exception\b|catch\s*\{\s*\}"),
     "Broad catch may swallow unrelated failures and mask real bugs."),
    ("error_handling", "empty catch/except block",
     re.compile(r"catch\s*\([^)]*\)\s*\{\s*\}|except[^:]*:\s*pass\b"),
     "Empty catch/except silently discards the error entirely."),
    ("error_handling", "swallowed promise rejection",
     re.compile(r"\.catch\s*\(\s*\(\s*\)\s*=>\s*\{?\s*\}?\s*\)"),
     "Promise rejection handler is a no-op — failures vanish silently."),

    # --- correctness ---
    ("correctness", "TODO/FIXME/HACK/XXX",
     re.compile(r"(?i)\b(TODO|FIXME|HACK|XXX|BUG)\b"),
     "Marked as known-incomplete or known-broken by the original author."),
    ("correctness", "loose equality (JS)",
     re.compile(r"[^=!]==(?!=)|[^!]!=(?!=)"),
     "Loose equality can coerce types unexpectedly — verify this is intentional."),
    ("correctness", "unbounded/no-limit fetch-all",
     re.compile(r"(?i)\.all\(\)\s*$|SELECT\s+\*\s+FROM\b(?!.{0,60}LIMIT)"),
     "Fetch-all with no visible limit — check the table can't grow unbounded."),

    # --- performance ---
    ("performance", "query inside loop",
     re.compile(r"(?i)for\s*\(.{0,60}\)\s*\{[^}]{0,40}(\.find\(|\.query\(|SELECT\s)"),
     "Query call appears inside a loop — possible N+1."),

    # --- code_health ---
    ("code_health", "debug print left in",
     re.compile(r"(?i)^\s*(console\.log\(|print\(|dd\(|dump\(|var_dump\(|debugger\s*;)"),
     "Debug/print statement possibly left in from development."),
    ("code_health", "commented-out code block",
     re.compile(r"^\s*//.*\b(function|if|for|SELECT)\b|^\s*#.*\b(def|if|for|SELECT)\b"),
     "Looks like commented-out code rather than a prose comment — verify it's not hiding a revert."),
]

CATEGORY_ORDER = ["security", "error_handling", "correctness", "performance", "code_health"]


def scan(root, categories):
    results = {cat: [] for cat in CATEGORY_ORDER}
    for dirpath, dirnames, filenames in os.walk(root):
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS and not d.startswith(".")]
        for fname in filenames:
            ext = os.path.splitext(fname)[1]
            if ext not in CODE_EXTS:
                continue
            fpath = os.path.join(dirpath, fname)
            try:
                with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
                    lines = f.readlines()
            except OSError:
                continue

            for i, line in enumerate(lines, start=1):
                for cat, label, regex, why in PATTERNS:
                    if cat not in categories:
                        continue
                    if regex.search(line):
                        snippet = line.strip()
                        if len(snippet) > 140:
                            snippet = snippet[:137] + "..."
                        results[cat].append((fpath, i, label, snippet, why))
    return results


def write_report(root, results, output_path):
    os.makedirs(os.path.dirname(output_path) or ".", exist_ok=True)
    today = date.today().isoformat()
    total = sum(len(v) for v in results.values())

    with open(output_path, "w", encoding="utf-8") as f:
        f.write(f"# Bug Pattern Scan — {today}\n\n")
        f.write(f"Scanned root: `{root}`\n\n")
        f.write(
            "**These are leads, not confirmed bugs.** Each hit needs manual "
            "read + the verification protocol in SKILL.md before it goes in "
            "the audit report. Expect false positives.\n\n"
        )
        f.write(f"Total hits: {total}\n\n")

        if total == 0:
            f.write("No pattern matches found in this pass.\n")
            print("No matches found.")
            return

        for cat in CATEGORY_ORDER:
            hits = results.get(cat, [])
            if not hits:
                continue
            f.write(f"## {cat.replace('_', ' ').title()} ({len(hits)})\n\n")
            for fpath, line_no, label, snippet, why in hits:
                rel = os.path.relpath(fpath, root)
                f.write(f"- ⚪ **{label}** — `{rel}:{line_no}`\n")
                f.write(f"  - Code: `{snippet}`\n")
                f.write(f"  - Why flagged: {why}\n")
            f.write("\n")

    print(f"Scan complete: {total} hit(s) across {len([c for c in results if results[c]])} categories.")
    print(f"Report written to: {output_path}")


def main():
    parser = argparse.ArgumentParser(description="Heuristic scan for common bug patterns.")
    parser.add_argument("--root", required=True, help="Root directory to scan.")
    parser.add_argument("--output", default=None,
                         help="Output path (default: <root>/bugs-reported/pattern-scan.md)")
    parser.add_argument("--category", action="append", choices=CATEGORY_ORDER,
                         help="Limit to one or more categories (repeatable). Default: all.")
    args = parser.parse_args()

    root = os.path.abspath(args.root)
    if not os.path.isdir(root):
        print(f"Error: root directory does not exist: {root}", file=sys.stderr)
        sys.exit(1)

    categories = set(args.category) if args.category else set(CATEGORY_ORDER)
    output_path = args.output or os.path.join(root, "bugs-reported", "pattern-scan.md")
    output_path = os.path.abspath(output_path)

    results = scan(root, categories)
    write_report(root, results, output_path)


if __name__ == "__main__":
    main()