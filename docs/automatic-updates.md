[← Domain Investors](../README.md)

# Automatic updates

GitHub Actions can check for Drupal updates and open a pull request with each set,
using [valicm/drupal-update](https://github.com/valicm/drupal-update). Every pull
request runs [the checks](checks-and-tests.md): coding standards and full installs
of the site. You read the result and merge it.

| Workflow | When | What |
|----------|------|------|
| `security-updates.yml` | each day | security updates, including core's exact pins |
| `minor-updates.yml` | each Friday | minor and patch updates |
| `core-major-updates.yml` | Fridays in June, August and December of even years | a new major version of core |
| `general-updates.yml` | by hand, from the *Actions* tab | any kind, for all, core, contributed or named packages |

## Turn them on

They are off until you do this, so a fresh copy of the project stays quiet.

1. **Make a token.** A fine-grained personal access token for this repository, with
   *Contents* and *Pull requests* read and write. A pull request opened with
   GitHub's own token wouldn't run the checks.
2. **Save it** as the repository secret `DRUPAL_UPDATE_TOKEN`, under *Settings >
   Secrets and variables > Actions*.
3. **Switch on the schedules:** on the *Variables* tab there, add the variable
   `DRUPAL_UPDATES` with the value `on`.

Run any of them by hand from the *Actions* tab to try it. That only needs the token.

## Merging security updates by themselves

Optional. Security pull requests can merge as soon as their checks pass:

1. Under *Settings > General*, allow auto-merge.
2. Protect the default branch, and require the checks of `tests.yml`. Without that,
   nothing would wait for them.
3. Add the variable `DRUPAL_UPDATES_AUTOMERGE` with the value `on`.

This approves no reviews and passes no branch protection. Other updates always wait
for you.

## After merging

Merging changes the code in your repository, not your site. On Vallic Cloud, a push
deploys only where *Deploy on push* is on for the environment: otherwise deploy from
the console or with `vallic deploy production --wait`. Each deploy runs the database
updates: see [Deploy on Vallic Cloud](vallic-cloud.md).

With [configuration in git](configuration.md), an update that changes configuration
needs exporting too, on a copy of the site. The action's
[examples](https://github.com/valicm/drupal-update/tree/v6.0.0/examples) include a
workflow for that.

## Commerce Domains

It uses the module's stable releases, and updates like any other module. The Monday
run of `tests.yml` installs the site with the newest release, so what an update will
need shows up there first.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Backups come with it.** Production is backed up every day, and the last four days are kept, ready to restore.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
