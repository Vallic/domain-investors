[← Domain Investors](../README.md)

# Roles

- **Owner:** everything. The installer gives it to your account.
- **Assistant:** domains (add, edit, import, bulk edit), their costs, sales,
  renewals and valuations, with the imports and renewal costs that go with
  them and the Domain portfolio settings; offers, orders and handovers,
  pages, and the dashboard. Not providers, keys, registrar management,
  payment gateways or payments, pricing rules, renewal decisions, people or
  site configuration.

To add an assistant, see [Every day](every-day.md).

Both roles must set up two-factor authentication (*My account > TFA*, with
an authenticator app such as Aegis, 2FAS or Google Authenticator), once
`TFA_ENCRYPTION_KEY` is set: the first three logins without it are allowed,
to set it up; after that, the login is refused. Keep the recovery codes it
offers. If an owner loses both, `drush tfa:reset-user --name=NAME`
clears theirs. Buyers, with leases, are not asked to.

More in [Sign-in protection](sign-in-protection.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
