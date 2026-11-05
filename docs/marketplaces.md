[← Domain Investors](../README.md)

# Connect marketplaces

Marketplaces you chose at install are added with their keys and **no
automatic pushes**. To add one later (with configuration in git, on your copy
of the site, then export and commit):

```sh
drush recipe ../recipes/portfolio_marketplaces   # once: USD and daily ECB rates
drush domain-investors:provider afternic
```

| Marketplace         | Variables                                      |
|---------------------|------------------------------------------------|
| Afternic            | `AFTERNIC_API_KEY`, `AFTERNIC_API_SECRET`      |
| Atom                | `ATOM_API_TOKEN`, `ATOM_USER_ID`               |
| Dynadot             | `DYNADOT_API_KEY`                              |
| fruits              | `FRUITS_CLIENT_ID`, `FRUITS_CLIENT_SECRET`     |
| GNAME               | `GNAME_APP_ID`, `GNAME_APP_KEY`                |
| NameSilo            | `NAMESILO_API_KEY`                             |
| Spaceship           | `SPACESHIP_API_KEY`, `SPACESHIP_API_SECRET`    |
| Unstoppable Domains | `UNSTOPPABLE_API_KEY`                          |
| BrandStore, DaaZ, DNX, NameClub, Sedo | none: they take CSV files    |

Set the variables, then review what a sync would change before anything is
pushed, push it, and only then turn on *Push catalog changes automatically*
on the provider:

```sh
drush commerce-domains:marketplace-sync afternic --dry-run --details
drush commerce-domains:marketplace-sync afternic
```

With automatic pushes on, changed domains are queued and pushed on cron
(every 15 minutes on Vallic Cloud), failed pushes are retried, and an hourly
check takes a domain a marketplace sold off sale here and on the others. To
push the queue at once: `drush advancedqueue:queue:process
commerce_domains_marketplace`. The dashboard and *Commerce > Domains >
Marketplace listings* show how each listing is doing.

For CSV marketplaces, each provider's *CSV export* tab downloads the file
its upload page takes, or `drush commerce-domains:csv-export sedo > sedo.csv`.

## What a sync does

Every sync reads both sides and pushes only the differences, so a failed or
skipped run is fixed by the next one. Listings for names the catalog doesn't
know, and listings a marketplace locks or shows as sold, are never touched. A
domain's [sale terms](sale-terms.md) decide what each marketplace lists.

*Commerce > Domains > Sync marketplaces* checks or syncs whole marketplaces from
the browser, and each domain's *Marketplaces* tab shows its status on each one,
with buttons to check or push that domain.

## When a marketplace sells a domain

The domain goes off sale here the way an order takes it off, and its listings on
the other marketplaces are removed: at once where pushes are automatic, otherwise
on their next sync. fruits and GNAME listings show no sale, so sales there aren't
seen: take those domains off sale yourself. If a sale falls through, put the
domain back on sale and the next syncs list it again. [Sales and
profit](sales-and-profit.md) covers recording the sale.

## Listing by hand

Tick *Marketplace* on a provider without a listing API, such as Squadhelp, to
record that you list domains there yourself. It can then be picked under *Not
listed on* and *Sold through*, and nothing is synced.

The [Commerce Domains README](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#syncing-marketplaces) has the details of each
marketplace, including which listing statuses count as sales.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
