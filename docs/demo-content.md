[← Domain Investors](../README.md)

# Demo content

Try the site with a full catalog before you have added anything of your own: 200
domains, with a mix of endings and prices.

They are real domains, for sale by the people who make this project. **They are for
a copy to try things on, not a site open to buyers:** a buyer's order would be for a
domain you don't own. Remove them before you open.

## Add them

Tick *Demo content* under *Also set up* in the installer, or:

```sh
drush domain-investors:demo
```

With Drush at install, add `demo` to `domain_investors_setup.extras`. Prices are in
the store's currency, whichever you chose. With the domain search, index them:
`drush search-api:index`.

## Remove them

```sh
drush domain-investors:demo --remove
```

Only the domains the demo added are removed, never your own, even where a name is
the same. One that has an order stays, so the order keeps its domain: delete it
under *Commerce > Domains* once you have dealt with the order.

## What keeps them safe

- They are never listed on marketplaces, even once you connect yours.
- A domain already in your catalog is left as it is.
- A Vallic Cloud production environment isn't offered them, in the installer or with
  Drush. To try them there, use a staging copy.
- The list holds only names and asking prices
  (`web/modules/custom/domain_investors_tools/demo/domains.csv`).

To add your own domains instead, see [The domain catalog](catalog.md#import-your-domains).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
