[← Domain Investors](../README.md)

# Every day

- **The dashboard** (where you land after logging in): offers waiting,
  recent sales and profit, expiring domains, sync health, next steps.
- **Offers:** *Commerce > Domain offers*. **Orders:** *Commerce > Orders*,
  with each order's *Handover* tab. **Handovers waiting:** *Commerce > Domain
  handovers*.
- **Expiring soon:** keep or let expire; a domain left to expire goes off
  sale 14 days before its date.
- **Sales and profit**, **Expenses** and **Renewal costs**, under *Commerce >
  Domains*: what sold and what it made, what the domains cost you each year,
  and what renewing them costs.
- **Notifications** go to the store email (*Commerce > Configuration >
  Domains > Domain portfolio*): sales here and on marketplaces, failing
  syncs, expiring domains, domains a registrar no longer holds, expired
  offers.
- **Cron** runs every 15 minutes on Vallic Cloud. Elsewhere, run `drush cron`
  as often from your scheduler.
- **An assistant:** `drush user:create ana --mail=ana@example.com` then
  `drush user:role:add assistant ana`. See [Roles](roles.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Deploys your visitors never notice.** A new release goes live in one step, with no maintenance page, and you can go back to an earlier one.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
