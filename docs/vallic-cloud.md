[← Domain Investors](../README.md)

# Deploy on Vallic Cloud

1. **Create the project** in the console as a Drupal project, connect your
   repository, and choose the environments' branches. `vallic.yaml` asks for
   MariaDB, Valkey (cache) and Vinyl Cache (Varnish).
2. **Set the variables** for what you connect, marked secret, per
   environment (test keys on staging): see [Variables](variables.md). The platform
   sets the database, files, hash salt, hostnames, Redis, Varnish and mail
   relay variables itself, and `web/sites/default/settings.vallic.php` reads
   them.
3. **Mail:** set the relay to your mail provider in `vallic.yaml` (the
   commented `opensmtpd` service) and `RELAY_PASSWORD` as a secret variable;
   without a relay, mail never leaves the platform.
4. **Deploy.** The first release skips the deploy steps. Afterwards
   `scripts/deploy.sh` runs the database updates (`drush deploy`, which also
   imports `config/sync`, once that holds an export) and clears Varnish and
   the CDN.
5. **Install, one of two ways:**
   - *Install on the environment:* open `https://ENVIRONMENT-URL/core/install.php`
     and answer the questions, choosing *Vallic Cloud* under *Also set up*;
     or `vallic drush production -- site:install domain_investors` with the
     [answers as for DDEV](install.md#locally-with-ddev)
     (`domain_investors_setup.extras=search,vallic_cloud`).
   - *Move a local install:* [install locally](install.md#locally-with-ddev), with *Vallic Cloud*
     under *Also set up* (or `ddev drush recipe ../recipes/portfolio_vallic_cloud`
     afterwards), push, then move the database and files:

     ```sh
     ddev export-db --gzip=false --file=portfolio.sql
     vallic db import production --file portfolio.sql
     vallic mount upload production ./web/sites/default/files
     vallic drush production -- cache:rebuild
     ```

6. **Domains:** add yours under *Routing* in the console. For the CDN, follow
   the console's CDN tab.

## What Vallic Cloud caching does

`recipes/portfolio_vallic_cloud` adds the Redis module (the cache itself is
set up by `settings.vallic.php`), and Purge with three purgers: Varnish by
cache tag, Varnish everything (`drush cache:rebuild-external`), and the
Vallic CDN. Pages are cached for an hour and purged by tag as domains and
pages change. `.vallic/varnish/` caches a page once per currency a visitor
chose, and passes admin, cart, checkout, payment, account, lander and offer
links straight to Drupal. `.vallic/nginx/server.conf` hands every
`/domain/...` path to Drupal, so `/domain/example.sh` and `/domain/example.md`
are pages rather than files the server refuses.

Away from the platform, unless `VARNISH_HOST` is set (for DDEV's Varnish
add-on, `VARNISH_HOST=varnish`), Purge queues and sends nothing, so local
logs stay free of failed purges.

## Keeping the admin pages to your addresses

The edge in front of the site can refuse them to everyone else: see [Sign-in
protection](sign-in-protection.md#on-vallic-cloud-keep-the-admin-pages-to-your-addresses).

## Prices

Machines are bought in the console, and the configurator there shows the total
as you choose. [The price list](https://vallic.com/cloud/pricing) has them by
region, and [the handbook](https://docs.vallic.com/start) explains what you
choose and what can't be changed later.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Deploys your visitors never notice.** A new release goes live in one step, with no maintenance page, and you can go back to an earlier one.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
