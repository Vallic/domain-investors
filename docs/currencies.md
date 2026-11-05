[← Domain Investors](../README.md)

# Prices in more currencies

The store sells in the currency chosen at install, and marketplaces get
prices converted at the European Central Bank's rates
(`portfolio_marketplaces`). To show visitors prices in their currency too:
add the currencies (*Commerce > Configuration > Currencies*), enable
Commerce Currency Resolver's *Cookie* and *Exchanger* submodules, set its
source at *Commerce > Configuration > Currency resolver*, and place its
currency block. `.vallic/varnish/` already caches a page once per currency a
visitor chooses; it has an example for picking the currency by country.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
