[← Domain Investors](../README.md)

# Recipes

`recipes/` holds the recipes the installer applies. `portfolio_base` is
always applied; the others are optional, at install or later with
`drush recipe ../recipes/NAME` (Drush runs from `web/`), and each builds on
`portfolio_base`. A recipe
leaves configuration that already exists as it is.

| Recipe                             | What it sets up |
|------------------------------------|-----------------|
| `portfolio_base`                   | Commerce with the domain product type (domain, Atom premium, sale terms, costs), the domain checkout and handover, offers, import, pricing and bulk editing, marketplace syncing and CSV files, the Owner and Assistant roles, the Portfolio theme with the domain list, domain pages, its pages (How it works, About, Contact with its form, Privacy policy, Terms and conditions), Default Admin with Coffee and the owner dashboard, pages, pathauto, redirects, meta tags, structured data, sitemap, Symfony Mailer, Antibot, an image CAPTCHA on sign-up, and two-factor authentication for the Owner and the Assistant |
| `portfolio_payments_stripe`        | Stripe Payment Element on the review step |
| `portfolio_payments_paypal`        | PayPal payment links                   |
| `portfolio_payments_wise`          | Wise Quick Pay, into your Wise business account |
| `portfolio_payments_bank_transfer` | Bank transfer, with your instructions |
| `portfolio_lease`                  | Lease to own on the site: a down payment at checkout, then monthly payments charged to the card saved with Stripe, or paid by bank transfer or Wise on request; visitor sign-up |
| `portfolio_escrow`                 | Escrow Pay and Escrow Offer, the Escrow item fields, the Escrow order workflow, "Negotiate on Escrow.com" on the offer form |
| `portfolio_marketplaces`           | US dollars and daily European Central Bank rates, for marketplace prices |
| `portfolio_search`                 | Domain search at `/search`, Search API on the database |
| `portfolio_search_meilisearch`     | The domain search on Meilisearch instead: typo tolerant, fast on large portfolios; with `portfolio_search` |
| `portfolio_vallic_cloud`           | Redis module; Purge with Varnish and Vallic CDN purgers |
| `portfolio_eu_invoicing`           | EU VAT, VAT numbers on billing details, an invoice per order as a PDF |
| `portfolio_google_tag`             | Google Tag Manager or Analytics from `GOOGLE_TAG_ID`, with ecommerce events and a lead per offer sent |

The install profile (`web/profiles/custom/domain_investors`) only asks the
questions and applies these. `web/modules/custom/domain_portfolio_tools`
holds what the recipes need from code: the owner dashboard block, provider
setup with keys (`drush domain-investors:provider`), the gateway forms that
keep credentials out of configuration, `drush domain-investors:escrow-setup`,
and the quiet Purge away from the platform.

Optional, at install or later: card payments (Stripe), PayPal, bank transfer,
Escrow.com, marketplaces, a domain search, Vallic Cloud caching, EU VAT and
invoices.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
