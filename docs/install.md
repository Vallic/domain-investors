[← Domain Investors](../README.md)

# Install

You need PHP 8.5 and Composer, and for trying it locally,
[DDEV](https://ddev.com) (recommended) or any PHP and database setup.

```sh
composer create-project vallic/domain-investors my-portfolio
cd my-portfolio
```

(Until the template is published, clone its repository and run
`composer install` instead.)

## The installer's questions

After Drupal's usual steps (database, site name, your administrator
account), the installer sets up the portfolio and asks:

- **Store:** its name, email address, country and currency.
- **Payments:** Stripe, PayPal, Wise, bank transfer and Escrow.com, and
  lease to own (with Stripe, bank transfer or Wise).
- **Registrars:** where your domains are registered (Dynadot, GoDaddy,
  Namecheap, NameSilo, Spaceship, Porkbun and more), plus any without an
  API, such as Hover.
- **Marketplaces:** where you list (Afternic, Atom, Dynadot, fruits, GNAME,
  NameSilo, Spaceship, Unstoppable Domains, and through CSV files BrandStore,
  DaaZ, DNX, NameClub and Sedo), plus any you list on by hand, such as
  Squadhelp.
- **Also set up:** the domain search, Vallic Cloud caching, EU VAT and
  invoices, Google Tag Manager or Analytics, and [demo domains](demo-content.md)
  to try the site with.
- **Appearance:** the site's template and colour scheme.

It then applies the recipes your answers call for, creates the store, gives
your account the Owner role, and adds each registrar and marketplace with
keys that read its API credentials from environment variables. Payment
gateways start disabled and in test mode, unless their variables are already
set when you install. Every answer can be changed later in the admin pages,
and every optional [recipe](recipes.md) applied later with `drush recipe`.

## Locally, with DDEV

```sh
ddev start
ddev composer install
ddev drush site:install domain_investors
```

or open `https://domain-investors.ddev.site/core/install.php` and answer the
questions in the browser. With Drush, answers go in as form values; leave
any out for its default:

```sh
ddev drush site:install domain_investors \
  --site-name="My Domains" --account-name=me --account-mail=me@example.com \
  domain_investors_setup.store_email=sales@example.com \
  domain_investors_setup.country=NL \
  domain_investors_setup.currency=EUR \
  domain_investors_setup.payments=stripe,bank_transfer \
  domain_investors_setup.registrars=dynadot,namecheap \
  domain_investors_setup.other_registrars=Hover \
  domain_investors_setup.marketplaces=afternic,sedo \
  domain_investors_setup.other_marketplaces=Squadhelp \
  domain_investors_setup.extras=search \
  domain_investors_setup.template=standard \
  domain_investors_setup.scheme=light
```

The values for `payments` are `stripe`, `paypal`, `wise`, `bank_transfer`,
`escrow` and `lease` (with `stripe`, `bank_transfer` or `wise`); for `extras`, `search`, `meilisearch`, `vallic_cloud`,
`eu_invoicing`, `google_tag` and `demo`; for registrars and marketplaces, the provider IDs
`drush domain-investors:providers` lists; for `template`, `minimal`,
`standard`, `showcase` and `catalog`; for `scheme`, `light`, `dark`, `brass`,
`brass-night`, `mono`, `cobalt` and `custom` (your colours, set under
Appearance afterwards). An empty value (`domain_investors_setup.payments=`)
chooses none.

Commit `composer.lock`, and, if you keep configuration in git (see
[Configuration](configuration.md)), the export:

```sh
ddev drush config:export -y
git add -A && git commit -m "Install Domain Investors"
```

Elsewhere, put the database (and a `hash_salt`) in
`web/sites/default/settings.local.php` before installing, so the installer
does not write them into `settings.php`.

## On Vallic Cloud

See [Deploy on Vallic Cloud](vallic-cloud.md): either install
locally and move the database, or install on the environment itself.

Prefer not to do this by hand? [Set it up with Claude](setup-with-claude.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
