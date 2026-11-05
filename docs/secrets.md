[← Domain Investors](../README.md)

# Keeping secrets out of configuration

Configuration is exported and committed, so no credential goes into it:

- Registrars and marketplaces read credentials through keys that hold only
  a variable name. Commerce Domains refuses keys that keep their value in
  configuration, and never saves a key's value with the provider.
- Payment gateways get theirs from `settings.portfolio.php` as overrides,
  only where the variable is set, and never during installation. Their forms
  show where each comes from, so saving a form keeps them empty.
- Don't load and save a payment gateway from code, such as a Drush script
  or a recipe's config action: an entity loaded with overrides writes them
  into the configuration when saved. Change gateways in their forms, with
  `drush config:set`, or in the exported YAML.
- So `drush config:export` writes no credential: keys hold variable names,
  and gateways' credential settings stay empty.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
