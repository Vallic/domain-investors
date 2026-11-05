#!/usr/bin/env bash
# Signs in and fetches the owner's admin pages of an installed, seeded site:
# each one answers and shows what it should. Usage:
#   scripts/ci/admin-smoke.sh BASE_URL ONE_TIME_LOGIN_URL
set -euo pipefail

base="${1:?base URL}"
login="${2:?one-time login URL, from drush user:login}"
failures=0
jar="$(mktemp)"
trap 'rm -f "$jar"' EXIT

curl -sS -L -o /dev/null -c "$jar" -b "$jar" "$login"

# expect PATH TEXT: the page answers 200 and contains TEXT.
expect() {
  local body status
  body="$(mktemp)"
  status="$(curl -sS -L -o "$body" -w '%{http_code}' -c "$jar" -b "$jar" "$base$1")"
  if [[ "$status" != 200 ]]; then
    echo "FAIL $1: HTTP $status"; failures=$((failures + 1))
  elif ! grep -qF -- "$2" "$body"; then
    echo "FAIL $1: missing $2"; failures=$((failures + 1))
  else
    echo "ok   $1"
  fi
  rm -f "$body"
}

expect /admin/dashboard 'Tasks'
expect /admin/commerce/domains/expiring 'Expiring soon'
expect /admin/commerce/domains/profit 'Import sales'
expect /admin/commerce/domains/profit/import 'CSV file'
expect /admin/commerce/domains/expenses 'By year'
expect /admin/commerce/domains/expenses/renewals 'Renewals paid'
expect /admin/commerce/domains/expenses/renewals/import 'CSV file'
expect /admin/commerce/domains/expenses/renewals/add 'Paid on'
expect /admin/commerce/domains/renewal-costs 'Renewal costs'
expect /admin/commerce/domains/renewal-costs/import 'Fill from'
expect /admin/commerce/domains/renewal-costs/add 'Cost a year'
expect /admin/commerce/domains/settings/portfolio 'Expiring soon'
expect /admin/commerce/domains/settings/sale-terms 'Longest lease by price'
expect /product/1/edit 'orbel.com'
expect /product/1/costs 'Renewals paid'
expect /product/1/sale 'Commission'
expect /product/1/valuation 'orbel.com'
expect /product/1/registrar 'orbel.com'

if (( failures )); then
  echo "$failures admin page checks failed."
  exit 1
fi
echo "All admin page checks passed."
