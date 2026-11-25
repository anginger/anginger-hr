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

# login rotates the session (and with it the CSRF token) — re-read the meta
curl -s -b "$JAR" -c "$JAR" "$BASE/" -o "$OUT"
CSRF=$(grep -o 'csrf-token" content="[^"]*"' "$OUT" | head -1 | sed 's/.*content="//;s/"//')

# 6. session + CSRF-protected API with cookie
check "GET session" 200 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" "$BASE/api/authentic/session")"
check "GET /api/employee/all (authed)" 200 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR" "$BASE/api/employee/all")"

# 7. API without session -> 403
check "GET /api/employee/all (guest)" 403 "$(curl -s -o /dev/null -w '%{http_code}' "$BASE/api/employee/all")"

# --- CRUD + archive flow (admin session in $JAR) ---
# NOTE: employee emp_id is a server-generated UUIDv7, so we look it up by name.
NAME="E2E$((RANDOM % 10000))"; UNAME="e2e-user-$$"; JAR2=$(mktemp); trap 'rm -f "$JAR" "$JAR2" "$OUT"' EXIT
api() { # method path body -> status (admin session)
  curl -s -o /dev/null -w '%{http_code}' -b "$JAR" -c "$JAR" -X "$1" "$BASE$2" \
    -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF" ${3:+-d "$3"}
}
api2() { # method path body -> status (second session)
  curl -s -o /dev/null -w '%{http_code}' -b "$JAR2" -c "$JAR2" -X "$1" "$BASE$2" \
    -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF" ${3:+-d "$3"}
}
mkemp() { # name salary -> json body (dep D01 exists in the seed)
  printf '{"emp_name":"%s","job_title":"工程師","dep_id":"D01","city":"台北","address":"測試路1號","phone":"0912345678","zip_code":"100","month_salary":%s,"annual_leave":10}' "$1" "$2"
}
check "create employee" 201 "$(api POST /api/employee "$(mkemp "$NAME" 50000)")"
EMP=$(curl -s -b "$JAR" "$BASE/api/employee/all" | php -r '$j=json_decode(stream_get_contents(STDIN),true); foreach((array)$j as $e){ if(($e["emp_name"]??"")===$argv[1]){ echo $e["emp_id"]; break; } }' "$NAME")
[ -n "$EMP" ] || { echo "FAIL could not look up created employee"; exit 1; }
check "validation: emp_name too long -> 400" 400 "$(api POST /api/employee "$(mkemp "這個名字實在是太長了超過八個字" 1)")"
check "validation: unknown dep_id -> 400" 400 "$(api POST /api/employee "$(mkemp "X1" 1 | sed 's/D01/ZZZ/')")"
check "read employee" 200 "$(api GET "/api/employee?emp_id=$EMP")"
check "update employee" 204 "$(api PUT /api/employee "{\"emp_id\":\"$EMP\",$(mkemp "改名員" 55000 | sed 's/^{//;s/}$//')}")"
check "create account for employee" 201 "$(api POST /api/account "{\"uuid\":\"$EMP\",\"username\":\"$UNAME\",\"password\":\"e2e-pass-123\"}")"
# second session needs its own csrf token (tokens are per-session)
curl -s -b "$JAR2" -c "$JAR2" "$BASE/site/login" -o "$OUT"
CSRF2=$(grep -o 'csrf-token" content="[^"]*"' "$OUT" | head -1 | sed 's/.*content="//;s/"//')
api2() { curl -s -o /dev/null -w '%{http_code}' -b "$JAR2" -c "$JAR2" -X "$1" "$BASE$2" -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF2" ${3:+-d "$3"}; }
check "login as new user" 201 "$(api2 POST /api/authentic/session "{\"username\":\"$UNAME\",\"password\":\"e2e-pass-123\"}")"
check "archive user" 200 "$(api POST /api/account/archive "{\"uuid\":\"$EMP\",\"archived\":true}")"
JAR3=$(mktemp); trap 'rm -f "$JAR" "$JAR2" "$JAR3" "$OUT"' EXIT
curl -s -b "$JAR3" -c "$JAR3" "$BASE/site/login" -o "$OUT"
CSRF3=$(grep -o 'csrf-token" content="[^"]*"' "$OUT" | head -1 | sed 's/.*content="//;s/"//')
check "archived user login -> 423" 423 "$(curl -s -o /dev/null -w '%{http_code}' -b "$JAR3" -X POST "$BASE/api/authentic/session" -H 'Content-Type: application/json' -H "X-CSRF-Token: $CSRF3" -d "{\"username\":\"$UNAME\",\"password\":\"e2e-pass-123\"}")"
check "unarchive user" 200 "$(api POST /api/account/archive "{\"uuid\":\"$EMP\",\"archived\":false}")"
check "delete employee with account -> 400" 400 "$(api DELETE "/api/employee?emp_id=$EMP")"
check "delete account" 204 "$(api DELETE "/api/account?uuid=$EMP")"
check "delete employee" 204 "$(api DELETE "/api/employee?emp_id=$EMP")"
check "deleted employee -> 404" 404 "$(api GET "/api/employee?emp_id=$EMP")"

exit $fail
