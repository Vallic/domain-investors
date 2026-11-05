#!/usr/bin/env bash
# Fetches the visitor pages of an installed, seeded site and checks each one
# answers and shows what it should. Usage: scripts/ci/smoke.sh BASE_URL
set -euo pipefail

base="${1:-http://127.0.0.1:8888}"
failures=0

# expect PATH TEXT...: the page answers 200 and contains every TEXT.
expect() {
  local path="$1"; shift
  local body status
  body="$(mktemp)"
  status="$(curl -sS -o "$body" -w '%{http_code}' "$base$path")"
  if [[ "$status" != 200 ]]; then
    echo "FAIL $path: HTTP $status"; failures=$((failures + 1)); return
  fi
  for text in "$@"; do
    if ! grep -qF -- "$text" "$body"; then
      echo "FAIL $path: missing $text"; failures=$((failures + 1)); return
    fi
  done
  echo "ok   $path"
}

expect / 'class="pf-page"' 'orbel' 'data-theme='
expect /domain/orbel.com 'dp-buybox' 'Buy now' 'Make an offer' 'application/ld+json'
expect /domain/paloma.ai 'data-theme="brass-night"' 'pf-sunken'
expect /domain/quillwork.com 'data-theme="mono"' 'pf-back'
expect /domain/calyx.io 'data-theme="cobalt"' 'pf-facts-table'
expect /domain/kintsu.co 'data-theme="custom"' '[data-theme="custom"]'
expect '/?order=price__number&sort=desc' 'orbel'
expect /contact 'pf-form' 'pf-page-title' 'Terms and conditions' 'Ask about a domain' 'edit-message-0-value'
expect /how-it-works 'pf-page-title' 'Or make an offer' 'Getting the domain'
expect /about 'pf-page-title' 'Who we are'
expect /privacy-policy 'pf-page-title' 'Who we share it with' 'Privacy policy'
expect /terms 'pf-page-title' 'Terms and conditions' 'Handing the domain over'
expect /user/login 'pf-form' 'aria-current="page"'
expect /sitemap.xml 'orbel.com'
expect '/search?keys=quill' 'quillwork.com'
# On Meilisearch a misspelling finds the name too.
if [[ "$(curl -sS "$base/search?keys=quilwork")" == *quillwork.com* ]]; then
  echo "ok   /search, typo tolerant"
fi

# A dead link answers 404, styled, with a way back to the names.
status="$(curl -sS -o /tmp/smoke-404.html -w '%{http_code}' "$base/no-such-page")"
if [[ "$status" == 404 ]] && grep -qF 'See all names' /tmp/smoke-404.html; then
  echo "ok   /no-such-page, 404"
else
  echo "FAIL /no-such-page: HTTP $status"; failures=$((failures + 1))
fi

if (( failures > 0 )); then
  echo "$failures page check(s) failed."; exit 1
fi
echo "All page checks passed."
