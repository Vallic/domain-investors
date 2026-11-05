[← Domain Investors](../README.md)

# Variables

| Variable | Read by |
|----------|---------|
| `STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SIGNING_SECRET` | Stripe gateway, `settings.portfolio.php` |
| `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID` | PayPal gateway, `settings.portfolio.php` |
| `WISE_TAG`, `WISE_PUBLIC_KEY` | Wise gateway, `settings.portfolio.php` |
| `MEILISEARCH_HOST`, `MEILI_MASTER_KEY` | Meilisearch for the domain search, set by Vallic Cloud and `.ddev/config.yaml` (`MEILISEARCH_PORT` if not 7700). `settings.portfolio.php` |
| `GOOGLE_TAG_ID` | Google Tag Manager or Analytics IDs, comma separated (`GTM-…`, `G-…`); unset, no tag is added. `settings.portfolio.php` |
| `TFA_ENCRYPTION_KEY` | encrypts the secrets of two-factor authentication: 32 random bytes, base64 (`openssl rand -base64 32`), a secret, and the same for as long as the site keeps its users (a new one makes every set-up authenticator unreadable). Unset, or not 32 bytes, two-factor authentication stays off. Key module (`key.key.tfa`, Environment provider), `settings.portfolio.php` |
| `ESCROW_API_KEY`, `ESCROW_EMAIL`, `ESCROW_WEBHOOK_SECRET` | Escrow.com gateways, `settings.portfolio.php` |
| `PAYMENT_MODE` | every gateway's mode: `live` or `test` |
| registrar and marketplace credentials ([registrars](registrars.md), [marketplaces](marketplaces.md)) | the providers' keys (Key module, Environment provider) |
| `VARNISH_HOST` | Purge, away from the platform (DDEV's Varnish add-on) |
| `VALLIC_*`, `DB_*`, `REDIS_*`, `VARNISH_*`, `SMTP_HOST` | set by Vallic Cloud, read by `settings.vallic.php` |

Set them in `.ddev/.env` locally, and as secret variables on Vallic Cloud: see
[Connect a registrar](registrars.md) for how, and [Keeping secrets out of
configuration](secrets.md) for why.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
