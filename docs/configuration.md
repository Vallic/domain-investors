[← Domain Investors](../README.md)

# Configuration: in git, or on the site

Drupal's configuration (providers, gateways, settings, the recipes' work) can
live in one of two places. Choose one:

- **On the site.** Leave `config/sync` without an export. You change
  settings in the admin pages of the live site, and deploys bring code only:
  `scripts/deploy.sh` runs database updates and rebuilds caches. The
  simplest way when you run the site on your own.
- **In git.** Export the configuration (`drush config:export`) and commit
  `config/sync`; every deploy then imports it with `drush deploy`. You make
  changes on a local or staging copy, export, commit and deploy: a change
  made on production is undone by the next deploy. Domains, orders, offers
  and pages are content, and stay where they are made. The way to go with a
  developer, or with staging.

With configuration in git, take a copy of production first (`vallic db dump
production`, `ddev import-db --file=...`), so the export carries the site's
own identity, or the import refuses it. Applying a recipe or adding a
provider is a configuration change: do it on the copy, export, commit.

Whichever you choose, no credential goes into configuration: see
[Keeping secrets out of configuration](secrets.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Deploys your visitors never notice.** A new release goes live in one step, with no maintenance page, and you can go back to an earlier one.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
