#!/bin/sh
# Runs on Vallic Cloud after each release is live (vallic.yaml, deploy.steps).
#
# - Not installed yet: says how to install, and leaves the release live. (The
#   very first release skips deploy steps anyway.)
# - Installed, with no configuration exported to config/sync: the
#   configuration lives on the site (docs/configuration.md), so database
#   updates, a cache rebuild and deploy hooks, without an import. Importing an
#   empty directory would delete the site's configuration.
# - Otherwise: drush deploy, which runs database updates, imports
#   config/sync and rebuilds the caches, in that order.
#
# Then Varnish and the Vallic CDN are cleared, where Purge is set up
# (recipes/portfolio_vallic_cloud), so the release starts from fresh pages.
set -eu

root="${COMPOSER_ROOT:-$(pwd)}"
drush="$root/vendor/bin/drush"
cd "${DRUPAL_ROOT:-$root/web}"

if ! "$drush" status --field=bootstrap 2>/dev/null | grep -qi successful; then
  echo "The site is not installed yet. Open /core/install.php on this environment,"
  echo "or run: vallic drush ENVIRONMENT -- site:install domain_investors (see docs/vallic-cloud.md)."
  exit 0
fi

if [ -f "$root/config/sync/core.extension.yml" ]; then
  "$drush" deploy --yes
else
  echo "No configuration in config/sync: it stays as it is on the site."
  "$drush" updatedb --yes
  "$drush" cache:rebuild
  "$drush" deploy:hook --yes
fi

if "$drush" pm:list --status=enabled --type=module --field=name 2>/dev/null | grep -qx purge; then
  "$drush" cache:rebuild-external --yes || echo "Clearing Varnish and the CDN failed; their pages expire on their own."
fi
