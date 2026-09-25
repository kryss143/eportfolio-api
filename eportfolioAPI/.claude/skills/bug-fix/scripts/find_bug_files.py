#!/usr/bin/env python3
"""
find_bug_files.py

Recursively scans a root directory for Markdown (.md) files that contain
the whole word "bug" or "bugs" (case-insensitive), and writes a discovery
log to eportfolioAPI/bug-fixes/bug-fixes.md so the bug-fix skill has a
single place to check for pending source files before it starts fixing.

Usage:
    python3 find_bug_files.py --root /path/to/project
    python3 find_bug_files.py --root /path/to/project --output /path/to/eportfolioAPI/bug-fixes/bug-fixes.md

Behavior:
    - Walks --root recursively, skipping common noise directories
      (.git, node_modules, venv, __pycache__, dist, build, .next, etc.)
      and the "bug-fixes" output folder itself, so previous fix reports are
      never re-flagged as new source files. Files under "eportfolioAPI/bugs-reported"
      ARE scanned deliberately — that's the bug-finder skill's output folder
      and the expected source of bug reports for this skill to process.
    - Matches the whole word "bug" or "bugs", case-insensitive
      (so "debug", "bugatti" etc. do NOT match).
    - For each match, records the file path, line numbers, and a short
      snippet of each matching line.
    - Writes/overwrites a markdown discovery log at the output path
      (default: <root>/eportfolioAPI/bug-fixes/bug-fixes.md).
    - Prints the list of matching files to stdout as well, so the calling
      agent can immediately see what needs to be processed.
"""

import argparse
import os
import re
import sys
from datetime import date

SKIP_DIRS = {
    ".git", "node_modules", "venv", ".venv", "__pycache__",
    "dist", "build", ".next", ".cache", "bug-fixes",
}

WORD_RE = re.compile(r"\bbugs?\b", re.IGNORECASE)


def find_matches(root):
    """Walk root, return list of (filepath, [(line_no, line_text), ...])."""
    results = []
    for dirpath, dirnames, filenames in os.walk(root):
        # prune noise / previous-report directories in place
        dirnames[:] = [d for d in dirnames if d not in SKIP_DIRS and not d.startswith(".")]

        for fname in filenames:
            if not fname.lower().endswith(".md"):
                continue
            fpath = os.path.join(dirpath, fname)
            try:
                with open(fpath, "r", encoding="utf-8", errors="ignore") as f:
                    lines = f.readlines()
            except OSError:
                continue

            hits = []
            for i, line in enumerate(lines, start=1):
                if WORD_RE.search(line):
                    snippet = line.strip()
                    if len(snippet) > 120:
                        snippet = snippet[:117] + "..."
                    hits.append((i, snippet))

            if hits:
                results.append((fpath, hits))

    return results


def write_report(root, results, output_path):
    os.makedirs(os.path.dirname(output_path), exist_ok=True)
    today = date.today().isoformat()

    with open(output_path, "w", encoding="utf-8") as f:
        f.write(f"# Bug File Discovery Log — {today}\n\n")
        f.write(f"Scanned root: `{root}`\n\n")

        if not results:
            f.write("No `.md` files containing \"bug\"/\"bugs\" were found.\n")
            print("No matching .md files found.")
            return

        f.write(f"Found {len(results)} file(s) mentioning \"bug\"/\"bugs\":\n\n")
        for fpath, hits in results:
            rel = os.path.relpath(fpath, root)
            f.write(f"## `{rel}`\n\n")
            f.write("Status: ⚪ pending — not yet processed by the bug-fix skill\n\n")
            for line_no, snippet in hits:
                f.write(f"- line {line_no}: {snippet}\n")
            f.write("\n")

    print(f"Found {len(results)} file(s). Discovery log written to: {output_path}")
    for fpath, hits in results:
        print(f"  - {fpath} ({len(hits)} match(es))")


def main():
    parser = argparse.ArgumentParser(description="Find .md files containing bug/bugs.")
    parser.add_argument("--root", required=True, help="Root directory to scan.")
    parser.add_argument(
        "--output",
        default=None,
        help="Output path for the discovery log "
             "(default: <root>/eportfolioAPI/bug-fixes/bug-fixes.md)",
    )
    args = parser.parse_args()

    root = os.path.abspath(args.root)
    if not os.path.isdir(root):
        print(f"Error: root directory does not exist: {root}", file=sys.stderr)
        sys.exit(1)

    output_path = args.output or os.path.join(root, "eportfolioAPI", "bug-fixes", "bug-fixes.md")
    output_path = os.path.abspath(output_path)

    results = find_matches(root)
    write_report(root, results, output_path)


if __name__ == "__main__":
    main()