#!/usr/bin/env python3
"""Simple BSM smoke tests: pages should load without obvious PHP/database fatals."""
from __future__ import annotations

import argparse
import sys
import urllib.error
import urllib.request

DEFAULT_PATHS = [
    "/site/",
    "/site/bible",
    "/site/newsletter",
    "/site/search",
    "/site/readings",
]
BAD_MARKERS = [
    "Fatal error",
    "Parse error",
    "Warning:",
    "Uncaught",
    "Please set the required environment variables",
    "Could not find .env file",
]


def check(base_url: str, path: str, timeout: float) -> bool:
    url = base_url.rstrip("/") + path
    try:
        with urllib.request.urlopen(url, timeout=timeout) as response:
            status = response.status
            body = response.read().decode("utf-8", errors="replace")
    except urllib.error.HTTPError as exc:
        body = exc.read().decode("utf-8", errors="replace")
        print(f"FAIL {path}: HTTP {exc.code}")
        if body:
            print(body[:500])
        return False
    except Exception as exc:
        print(f"FAIL {path}: {exc}")
        return False

    if status < 200 or status >= 400:
        print(f"FAIL {path}: HTTP {status}")
        return False

    for marker in BAD_MARKERS:
        if marker in body:
            print(f"FAIL {path}: found marker {marker!r}")
            print(body[:500])
            return False

    print(f"OK {path}: HTTP {status}")
    return True


def main() -> int:
    parser = argparse.ArgumentParser(description="Smoke-test local BibleStudyMan pages")
    parser.add_argument("--base-url", default="http://localhost:8080", help="base URL for the local site")
    parser.add_argument("--timeout", type=float, default=10.0, help="request timeout in seconds")
    parser.add_argument("paths", nargs="*", default=DEFAULT_PATHS, help="paths to test")
    args = parser.parse_args()

    ok = True
    for path in args.paths:
        ok = check(args.base_url, path, args.timeout) and ok
    return 0 if ok else 1


if __name__ == "__main__":
    raise SystemExit(main())
