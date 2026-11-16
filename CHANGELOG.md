# Changelog

All notable changes to the Domain Investors template. Versions follow the
git tags; the Commerce Domains release each one installs is noted.

## 1.0.6 - 2026-11-16

Commerce Domains 1.0.6.

### Fixed

- Profit and expenses counted a renewal on the day a domain was registered
  when that registration was the purchase: domains caught as they dropped
  (backorders, pending-delete auctions) and hand registrations each showed
  one estimated renewal too many. A registration's first year is no longer
  counted.
- Importing a registrar's order history into *Import renewal costs* crashed
  on a currency cell like "EURO EUR (€)". The renewal cost, renewals and sales
  imports now read the code from such cells, and report a cell without one on
  its row.

### Changed

- `drupal/commerce_domains` is required at `^1.0.6`.
- Docs: which years are estimated, in
  [Sales and profit](docs/sales-and-profit.md#renewals-paid).

### Upgrading a site made from 1.0.5

```sh
composer update drupal/commerce_domains
```

No database updates or configuration changes: profit, expenses and each
domain's *Costs* tab are corrected as soon as the code is updated.

## 1.0.5 - 2026-11-16

Commerce Domains 1.0.5.

### Added

- Lease price bands and down payment on *Sale terms*
  (*Commerce > Configuration > Domains*): change the longest lease below each
  price, and *Down payment on a lease* (10%) for domains that set none. The
  same on this site and every marketplace, within each marketplace's limit;
  Spaceship's file now uses that down payment instead of a fixed 25%.
- A lease can cost more than paying in full: a percentage for each term under
  *Commerce > Domains > Settings > Domain leases*, none by default. It is
  added to the order as a fee once the buyer chooses the lease, so the down
  payment, the monthly payments and the invoice include it. Marketplaces are
  not affected.

### Changed

- `drupal/commerce_domains` is required at `^1.0.5`.
- Docs: the price bands and down payment in [Sale terms](docs/sale-terms.md);
  where the down payment comes from, and charging more for a lease, in
  [Lease to own](docs/lease-to-own.md).

### Upgrading a site made from 1.0.4

```sh
composer update drupal/commerce_domains
drush updb
drush config:export
```

The update keeps the price bands as they were, and moves the lease
recipe's default down payment to *Sale terms*. On a site without the lease
recipe it is set to 25%, as Spaceship's file had. Run it before importing
configuration that has the new settings. With the lease recipe, Spaceship's
file goes from 25% to that down payment (10% unless you changed it), so
upload it again. Marketplaces get changed bands with their next full sync.

## 1.0.4 - 2026-11-14

Commerce Domains 1.0.4.

### Added

- Notes on DNS template records: what each one is for, such as the
  marketplace a TXT record verifies. Shown in the template form and list,
  never written to the registrar.

### Fixed

- Namecheap domains on other nameservers showed an error on the DNS tab;
  they now say the records are wherever the nameservers point, and the bulk
  action skips them.

### Security

- Drupal core 11.4.12, with the Symfony 7.4.22/7.4.23 security releases.
- Entity Print 2.20.0 (SA-CONTRIB-2026-327).

### Changed

- `drupal/commerce_domains` is required at `^1.0.4`.
- Docs: record notes in [Connect a registrar](docs/registrars.md#dns-records).

### Upgrading a site made from 1.0.3

```sh
composer update "drupal/core*" drupal/entity_print drupal/commerce_domains -W
drush updb
```

## 1.0.3 - 2026-11-14

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
