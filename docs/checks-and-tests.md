[← Domain Investors](../README.md)

# Checks and tests

GitHub Actions (`.github/workflows/tests.yml`) runs on every push to `main`
and every pull request, and each Monday with the newest Commerce Domains
release, so what an update will need shows up before the update:

- **Coding standards:** `composer validate`, PHP syntax, and Drupal's coding
  standards on the profile, `domain_portfolio_tools` and the Portfolio theme
  (`phpcs.xml.dist`).
- **Install:** the profile installed into MariaDB 11.8, twice: with the
  answers most owners give, and with every optional recipe. Each site gets a
  few domains (`scripts/ci/seed.php`), one for each template and scheme;
  `scripts/ci/smoke.sh` then checks the visitor pages answer and show what
  they should, `scripts/ci/admin-smoke.sh` does the same for the owner's
  pages, the demo domains are added and removed, and the run fails on any error in
  Drupal's log.
- **Recipe fields:** `scripts/ci/recipe-fields.php` compares the domain
  fields `portfolio_base` carries with those of Commerce Domains, and fails
  when the module has added or reworded one.

The custom code has no PHPUnit tests of its own; Commerce Domains' tests run
on drupal.org.

Locally, `ddev exec vendor/bin/phpcs` checks the standards. To run the page
checks against the DDEV site after `ddev drush php:script scripts/ci/seed.php`,
use `scripts/ci/smoke.sh http://domain-investors.ddev.site`, and
`ddev drush php:script scripts/ci/recipe-fields.php` after updating Commerce
Domains.

Keeping the code up to date runs through the same checks: see [Automatic
updates](automatic-updates.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Deploys your visitors never notice.** A new release goes live in one step, with no maintenance page, and you can go back to an earlier one.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
