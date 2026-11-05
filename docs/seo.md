[← Domain Investors](../README.md)

# SEO

Clean URLs, redirects, meta tags and Open Graph, Product
structured data with the price, and an XML sitemap.

## Addresses

Domains live at `/domain/example.com`. New pages take their address from their title
(`/[node:title]`), and the pages the site comes with have short ones of their own:
see [The storefront](storefront.md#pages). *Configuration > Search and metadata >
URL redirects* holds redirects from old addresses.

## Meta tags and sharing

Each domain page gets, without any work, a title ("example.com is for sale | Your
Site"), a description ("example.com is for sale. Buy it now or make an offer."), a
canonical address, and Open Graph tags for sharing. Change the wording at
*Configuration > Search and metadata > Metatag*.

## Structured data

Domain pages carry Product structured data in the *Domain names* category, with an
offer at the price the visitor sees, so search engines can show it.

## Sitemap

`/sitemap.xml` lists the domains for sale and the pages, generated on cron. Settings
are at *Configuration > Search and metadata > Simple XML Sitemap*. To generate it at
once: `drush simple-sitemap:generate`.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
