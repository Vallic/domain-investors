[← Domain Investors](../README.md)

# Search on Meilisearch

The domain search at `/search` runs on the database out of the box. With
`portfolio_search_meilisearch` (chosen at install wherever Meilisearch runs,
or `drush recipe ../recipes/portfolio_search_meilisearch` later) it runs on
Meilisearch: forgiving of typos ("quilwork" finds quillwork.com) and quick
on thousands of names.

- **DDEV** runs Meilisearch v1.53.3 beside the site
  (`.ddev/docker-compose.meilisearch.yaml`); its dashboard is at
  `https://domain-investors.ddev.site:7701`.
- **Vallic Cloud** runs the same version from `vallic.yaml` and sets
  `MEILISEARCH_HOST` and `MEILI_MASTER_KEY` for each environment.

The site reads both variables (`settings.portfolio.php`), so the key is never
in configuration. After the recipe, or on a new environment, fill the index:
`drush search-api:index domains`. Meilisearch keeps its indexes when the
site's database goes (a reinstall, a database from elsewhere), so when an
index is put on Meilisearch, whatever Meilisearch holds under its name is
removed first, and filled again from the site. The database server stays, so moving the
index back to it under *Configuration > Search and metadata > Search API*
switches back. Not using Meilisearch? Remove it from `vallic.yaml` to save
its memory.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
