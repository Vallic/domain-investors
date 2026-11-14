# Changelog

All notable changes to the Domain Investors template. Versions follow the
git tags; the Commerce Domains release each one installs is noted.

## 1.0.2 - 2026-11-14

Commerce Domains 1.0.3.

### Added

- DNS records at NameBright: its domains on NameBright's nameservers get the
  DNS tab, DNS templates and the bulk action. A, AAAA, CNAME, MX and TXT
  records; no TTLs.
- *URL redirect, any registrar* in DNS templates: a Dynadot forward, a GNAME
  or Namecheap URL record, so one template redirects domains at any of
  them. Registrars without a redirect in their DNS API leave it out.

### Changed

- `drupal/commerce_domains` is required at `^1.0.3`.
- Docs: NameBright and redirects in
  [Connect a registrar](docs/registrars.md#dns-records).

### Upgrading a site made from 1.0.1

```sh
composer update drupal/commerce_domains
```

No database updates or configuration changes.

## 1.0.1 - 2026-11-14

Commerce Domains 1.0.2.

### Added

- DNS templates (*Commerce > Configuration > Domains > DNS templates*): up to
  20 records, `{domain}` for the domain's name. Use one on a domain's DNS
  tab, to review before it is written, or apply it to a selection with the
  new *Apply a DNS template* bulk action on *Commerce > Domains*.
- Registration details refresh every 1, 6, 12 or 24 hours (*Refresh every*
  on the provider), not only daily; *Refresh now* on the provider's
  *Registrations* tab, and *Refresh registrations* in the provider list.

### Fixed

- *Add a record* on the DNS tab of a domain with no records crashed.
- Dynadot URL forwards added on the DNS tab had no redirect type; they are
  sent as 302.

### Changed

- `drupal/commerce_domains` is required at `^1.0.2`.
- Docs: DNS records and keeping registrations up to date, in
  [Connect a registrar](docs/registrars.md); bulk actions in
  [The domain catalog](docs/catalog.md).
- GitHub Actions bumped.

### Upgrading a site made from 1.0.0

```sh
composer update drupal/commerce_domains
drush updb
drush config:export
```

The updates install the DNS template entity type, and add the *Apply a DNS
template* action to the Domains overview's bulk form.

## 1.0.0 - 2026-11-07

First release: the recipes, the installer, the Domain Investors theme and
the docs.
