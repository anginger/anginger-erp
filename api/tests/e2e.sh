#!/usr/bin/env bash
# E2E smoke test against a running dev server.
# Usage: BASE=http://127.0.0.1:1271 api/tests/e2e.sh
set -u
BASE=${BASE:-http://127.0.0.1:1270}
JAR=$(mktemp); OUT=$(mktemp); trap 'rm -f "$JAR" "$OUT"' EXIT
fail=0

check() { # name expected actual
  if [ "$2" = "$3" ]; then echo "ok   $1 ($3)"; else echo "FAIL $1 (want $2 got $3)"; fail=1; fi
}

# 1. health endpoint (no auth)
check "GET /health" 200 "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/health")"

# 2. login without CSRF token -> 400
check "POST login w/o CSRF" 400 "$(curl -s -o /dev/null -w '%{http_code}' -X POST "$BASE/api/authentic/session" -H 'Content-Type: application/json' -d '{"username":"admin","password":"admin"}')"

# 3. fetch login page, extract CSRF meta
curl -s -c "$JAR" "$BASE/site/login" -o "$OUT"
CSRF=$(grep -o 'csrf-token" content="[^"]*"' "$OUT" | head -1 | sed 's/.*content="//;s/"//')
[ -n "$CSRF" ] || { echo "FAIL could not read csrf meta"; exit 1; }

# 4. five bad logins -> 429 on the sixth (use a throwaway username to not lock admin)
code=0
for i in 1 2 3 4 5; do
  code=$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" -X POST "$BASE/api/authentic/session" \
    -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF" \
    -d '{"username":"e2e-lock-'$$'","password":"nope"}')
done
check "5th bad login" 401 "$code"
check "6th bad login rate-limited" 429 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" -X POST "$BASE/api/authentic/session" -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF" -d '{"username":"e2e-lock-'$$'","password":"nope"}')"

# 5. good login -> 201
check "POST login admin/admin" 201 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" -X POST "$BASE/api/authentic/session" -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF" -d '{"username":"admin","password":"admin"}')"

# 6. session + CSRF-protected API with cookie
check "GET session" 200 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" "$BASE/api/authentic/session")"
check "GET /api/history/all (authed)" 200 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" "$BASE/api/history/all")"

# 7. API without session -> 401
check "GET /api/history/all (guest)" 403 "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/api/history/all")"

exit $fail
