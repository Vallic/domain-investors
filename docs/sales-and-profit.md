[← Domain Investors](../README.md)

# Sales and profit

Each domain's *Sale* tab records what it sold for,
the full price the buyer paid, and the marketplace's commission in
percent; the profit counts what you received. Sales on the site, by
payment link and from Atom's sales report are filled in for you; earlier
ones come in from a CSV file with *Import sales*. *Expenses* adds up, by
year, what buying and renewing your domains cost against what sales
brought, and what renews in the next 12 months. *Renewals paid* holds what
renewals really cost, added by hand or from a CSV file (Dynadot's order
history works as it is); a registrar sync that finds an expiry date moved
on records an estimate until then. Years without a record are estimated
from *Renewal costs*, a table of what a year costs by extension and
registrar, filled from a CSV file, your Dynadot or Porkbun account's
prices, or Porkbun's public prices.

## Profit

*Commerce > Domains > Sales and profit* shows what each sold domain earned, and the
totals by year. Profit is what you received (the price less the commission), minus
the purchase price, minus the renewals paid. A sale on the site fills in the sale
from its order, and canceling the order clears it. A commission is 0 for a sale on
the site or in private.

*Sale* has *Undo sale* for a sale entered by hand or found on a marketplace: it
clears the sale and puts the domain back on sale. A lease to own counts in full in
the year it started; if the buyer stops paying, clear the sale fields and put the
domain back on sale.

## Import sales

*Import sales* takes a CSV file or pasted rows with a header row: `domain`
(required), `sold` (a date), `price` (what the buyer paid), `currency`, `commission`
(percent), `received` (what you got, which works out the commission), `sold_via` (a
provider's ID or name) and `lease_months`. A preview shows what each row changes,
and the rows left out and why, before anything is saved. Sales on this site come
from their orders and are left out.

```sh
drush commerce-domains:sales-import sales.csv --dry-run
drush cdsimport sales.csv
```

## Renewals paid

*Renewals paid*, under *Expenses* and on each domain's *Costs* tab, holds what
renewals really cost. *Import renewals* takes a CSV file with `domain` (required),
`date`, `amount`, `currency`, `years`, `type`, `registrar` and `reference`. A file
with a type column keeps only its renewals, transfers and restores, so registrations
and auctions are passed over. Rows imported before add nothing, so a file can be
imported again as it grows:

```sh
drush commerce-domains:renewals-import dynadot_all.csv --dry-run
drush cdrenewals dynadot_all.csv
```

When a registrar sync finds an expiry date moved on by about a year or more, it
records a renewal marked estimated, at the yearly cost. Importing what was charged
replaces it.

## Renewal costs

*Commerce > Domains > Renewal costs* is a table of what a year costs, by extension,
registrar (or any) and currency. A domain takes its registrar's cost for the longest
extension it ends with, else the general one. *Import renewal costs* fills it from a
CSV file (`extension`, `renewal`, `currency`, `registrar`), your Dynadot or Porkbun
account's prices, or Porkbun's public prices, which need no key and fill only the
gaps unless told to replace.

```sh
drush commerce-domains:renewal-costs --from=dynadot --dry-run
drush cdrenew --from=porkbun_public --replace
drush cdrenew costs.csv
```

A domain's yearly cost is its own renewal cost, else what its latest recorded renewal
cost a year, else its registrar's cost, else the general one. Its *Costs* tab says
which applies.

## Estimates and currencies

Anniversaries of the expiry date without a record are estimated at the yearly cost,
the oldest first, since records usually start when a domain reached the registrar
they came from. So totals are right, but if the newest renewal is the missing one,
the estimate shows in an older year.

Amounts are kept in the currency charged, and added up per currency on *Expenses*.
Costs in another currency than the sale are converted at Commerce Exchanger's rates
for profit. A CSV row in a currency the site doesn't have is left out, with a
message: add it at *Commerce > Configuration > Currencies* first.

The [Commerce Domains README](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#running-the-portfolio) has the rest.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Backups come with it.** Production is backed up every day, and the last four days are kept, ready to restore.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
