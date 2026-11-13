[← Domain Investors](../README.md)

# The domain catalog

One product per domain, titled with the name as buyers
  should see it (LearnPhpOnline.com). The domain, its label and extension are
  derived from the title; you record the registrar, the expiry date, what it
  cost and what renewing it costs.

## A domain's page in the admin

A domain has tabs beside *Edit*, each with its own Save:

- **Costs:** what you paid, when, auto-renew, and whether to keep it or let it
  expire. See [Sales and profit](sales-and-profit.md).
- **Sale:** what it sold for, through which marketplace.
- **Valuation:** an appraisal, shown only, with a provider that appraises (Atom).
- **Registrar:** the registrar and its dates; with a registrar's API, the
  nameservers, lock, auto-renew and auth code. See
  [Connect a registrar](registrars.md).
- **DNS:** the records, where the registrar's API can edit them, and a DNS
  template to use. See [Connect a registrar](registrars.md#dns-records).
- **Prices:** its price in other currencies. See
  [Prices in more currencies](currencies.md).
- **Marketplaces:** its status on each one. See
  [Connect marketplaces](marketplaces.md).

Mark a domain *Internal* to keep it for your own use: never for sale, on the site
or on marketplaces, while renewals and registrar checks still count it.

*Commerce > Domains* lists every domain with its registrar, expiry date, price
and status on each marketplace.

## Import your domains

*Commerce > Domains > Import*, or the *Import domains* task on the
dashboard, takes:

- **a list**, one domain per line, optionally with a price
  (`example.com 1500`);
- **a CSV file** with a header row: `domain`, `price`, `currency`,
  `buy_now`, `offers`, `minimum_offer`, `description`, `registrar`,
  `expires`, `atom_premium`, and, optionally, what each domain cost and its
  sale: `purchase_price`, `purchase_date`, `renewal_cost`, `sold`,
  `sold_for`, `commission`, `sold_via` (the Commerce Domains README lists
  every column);
- **a registrar account**: *Read the account* lists the domains there that
  the catalog lacks.

The same from Drush, with a dry run first:

```sh
drush commerce-domains:import domains.csv --dry-run --details
drush commerce-domains:import domains.csv
drush commerce-domains:import --from-registrar=dynadot --unpublished
```

Domains without a price get the first matching pricing rule (*Commerce >
Configuration > Domains > Domain pricing*). Bulk actions on *Commerce >
Domains* reprice, set sale terms, choose marketplaces, apply a DNS
template, publish and unpublish a selection. With the domain search, index what you imported:
`drush search-api:index`.

The [Commerce Domains README](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#importing-domains) has every option, including
updating domains already in the catalog.

## Prices without typing them

A domain saved without a price gets the first pricing rule that matches it, by
extension, length, word count and Atom premium. A domain with a price keeps it,
even when the rules change; *Apply pricing rules* on *Commerce > Domains* reprices
a selection. Rounding steps round repriced domains and prices in other currencies.

## When a domain goes off sale

Placing an order takes its domain off sale, here and, with the next sync, on the
marketplaces, so a paid order can't be sold twice. So does a sale found on a
marketplace, and letting a domain expire (14 days before its expiry date).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Backups come with it.** Production is backed up every day, and the last four days are kept, ready to restore.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
