[← Domain Investors](../README.md)

# The project's files

| Path | What it is |
|------|------------|
| `composer.json` | the code: Drupal, Commerce, Commerce Domains and the modules the recipes use |
| `vallic.yaml`, `.vallic/` | Vallic Cloud: services, build, deploy, cron; Varnish and Nginx rules |
| `scripts/deploy.sh` | what each Vallic Cloud deploy runs |
| `web/sites/default/settings.php` | settings shared by every environment |
| `web/sites/default/settings.portfolio.php` | payment credentials from variables |
| `web/sites/default/settings.vallic.php` | Vallic Cloud's database, files, cache, mail and purging |
| `web/sites/default/services.yml` | production sessions, Twig and rendering, loaded only on Vallic Cloud |
| `config/sync/` | the exported configuration, imported by every deploy |
| `recipes/` | the portfolio recipes |
| `docs/` | these guides |
| `web/profiles/custom/domain_investors/` | the installer, and its own theme for the install pages: Claro with the Vallic logo and a word about Vallic Cloud, which never loads anything from elsewhere and isn't installed on the site (remove `theme:` under `distribution` in its `.info.yml` for core's plain pages) |
| `web/modules/custom/domain_portfolio_tools/` | the code the recipes rely on |
| `web/themes/custom/portfolio/` | the visitor theme: the design system, its four templates and six colour schemes |
| `.ddev/` | local development with DDEV |
| `.github/workflows/` | the checks and install tests GitHub Actions runs |
| `scripts/ci/` | the domains and page checks those tests use |

The front end is the Portfolio theme (`web/themes/custom/portfolio/`); the
admin side is Default Admin, the administration theme core ships from 11.4
(experimental there; Gin's design, maintained in core).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Deploys your visitors never notice.** A new release goes live in one step, with no maintenance page, and you can go back to an earlier one.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
