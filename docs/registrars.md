[← Domain Investors](../README.md)

# Connect a registrar

Each registrar the installer added reads its API credentials from
environment variables, through keys (*Configuration > System > Keys*). Set
them, and the registrar is connected:

| Registrar           | Variables                                                   |
|---------------------|-------------------------------------------------------------|
| Atom                | `ATOM_API_TOKEN`, `ATOM_USER_ID`, `ATOM_REGISTRAR_TOKEN`      |
| Dynadot             | `DYNADOT_API_KEY`                                           |
| GNAME               | `GNAME_APP_ID`, `GNAME_APP_KEY` (a reseller account)        |
| GoDaddy             | `GODADDY_API_TOKEN`                                         |
| IONOS               | `IONOS_API_KEY`                                             |
| NameBright          | `NAMEBRIGHT_CLIENT_ID`, `NAMEBRIGHT_CLIENT_SECRET`          |
| Namecheap           | `NAMECHEAP_API_USER`, `NAMECHEAP_API_KEY`, `NAMECHEAP_CLIENT_IP` |
| NameSilo            | `NAMESILO_API_KEY`                                          |
| Porkbun             | `PORKBUN_API_KEY`, `PORKBUN_SECRET_KEY`                     |
| Spaceship           | `SPACESHIP_API_KEY`, `SPACESHIP_API_SECRET`                 |
| Unstoppable Domains | `UNSTOPPABLE_API_KEY`                                       |

- **Locally:** replace the `replace-me` samples in `.env` beside `web/`
  (the installer wrote them; git ignores the file), then enable the
  registrar: it starts disabled while its keys are samples. `.ddev/.env`
  works too, after `ddev restart`.
- **On Vallic Cloud:** `vallic var set DYNADOT_API_KEY 'the-key' production
  --secret`, then `vallic var apply production`; or *Variables* in the
  console, marked secret.

Each provider's page (*Commerce > Configuration > Domains > Domain
providers*) says which variables it reads and whether they are set, never
their values. To read a credential from a file outside the web root instead,
switch its key to the *File* provider, with *Strip trailing line breaks*. To
add a registrar later, with its keys:

```sh
drush domain-investors:provider porkbun   # or several: dynadot godaddy
drush domain-investors:provider Hover     # a registrar without an API
drush domain-investors:providers          # every provider, what it can do, its variables
```

For a domain on the registrar's own nameservers, its *DNS* tab edits the
records at Dynadot, GNAME, GoDaddy, Namecheap, NameSilo, Porkbun and
Spaceship: the changes are reviewed before they are written, and the zone as
it was is kept, to be put back ([Commerce Domains README](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#dns-records)). The
Owner has it; the Assistant does not, as it acts on the registrar at once.

Check what the account holds against the catalog, then record it:

```sh
drush commerce-domains:registrar-sync dynadot --dry-run --details
drush commerce-domains:registrar-sync dynadot
```

Turn on *Refresh registration details daily* on the provider to have cron
keep expiry dates and auto-renew in line. *Also add the domains the account
holds that the catalog lacks*, below it, has cron add new ones too, once a
day: unpublished, not listed on marketplaces, and priced by the first
matching pricing rule, else 0. Publish and price them when you are ready to
sell.

## What a registrar can do

Where a registrar's API can change domains, each domain gets a *Registrar* tab:
the registration as the registrar has it, its nameservers, the transfer lock,
auto-renew and the auth code. What each registrar allows differs: see
[Domain management](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#domain-management) in the Commerce Domains README. The
page reads the registrar each time it is shown, and every button acts on the
registrar at once. Changes are logged with who made them; an auth code is shown
on the page and never logged.

Once a registrar's API has read the whole account and found a domain there, its
expiry date, registration date and auto-renew setting follow the registrar, and
are locked on the domain's form. A domain the account no longer holds is only
reported, for you to look at: nothing is written to the registrar by a sync.

A registrar without an API, such as Hover, is just a name: domains can be
recorded there, and nothing is synced.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Backups come with it.** Production is backed up every day, and the last four days are kept, ready to restore.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
