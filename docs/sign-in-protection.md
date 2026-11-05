[← Domain Investors](../README.md)

# Sign-in protection

Two-factor authentication (an authenticator app,
with recovery codes) required for the Owner and the Assistant, who can
move domains at your registrars, once `TFA_ENCRYPTION_KEY` is set; Antibot
on the offer, contact, login, password and sign-up forms; and an image
CAPTCHA on sign-up, made on the site with no outside service.

## Two-factor authentication

Generate the key once, and keep it for as long as the site keeps its users:

```sh
openssl rand -base64 32
```

Set the result as `TFA_ENCRYPTION_KEY`, marked secret: see
[Variables](variables.md). A new key makes every set-up authenticator unreadable.
Unset, or not 32 bytes, two-factor authentication stays off, rather than locking you
out, and the installer says so.

Both roles set it up under *My account > TFA*. The first three logins without it are
allowed, to set it up; after that, the login is refused. Keep the recovery codes it
offers. If an owner loses both:

```sh
drush tfa:reset-user --name=NAME
```

Moving a database to another site with a different key? Each person sets it up
again: clear theirs with the same command.

## Antibot

Antibot checks that a real browser is filling in the form, on the offer, contact,
login, password and sign-up forms. It works on cached pages and shows no puzzle. A
visitor without JavaScript sees a page saying why they were stopped. The offer form
also takes five offers an hour from one visitor.

## CAPTCHA on sign-up

An image CAPTCHA, made on the site, so no visitor's data goes to an outside service.
Visitors can only sign up where leases are on: otherwise accounts come from checkout.

## On Vallic Cloud: keep the admin pages to your addresses

Vallic Cloud can refuse the admin pages to everyone but your own addresses, before
a request reaches the site. It is included with every project, under **Restricted
access** on the environment's *Routing* tab in the console. List the paths, and the
addresses that may reach them:

```text
# Restricted paths
/admin
/product

# Addresses for restricted paths: your office, your VPN
203.0.113.0/24
198.51.100.14
```

Everyone else gets a 403 on those paths, and only on those. `/admin` holds the admin
pages, and `/product` the forms that edit a domain, so buyers aren't affected: they
see domains at `/domain/example.com`, and `/product/1` only redirects there.

- **Keep `/user/login` open.** Buyers sign in there too. Two-factor authentication
  and Antibot protect it.
- **Your address has to stay on the list.** If yours changes, you are refused until
  you add the new one in the console, so a VPN or a fixed address suits this best.
- **Your own machines only.** These rules don't apply on a machine the platform
  shares between customers: the *Routing* tab says so when that is the case.
- **Other rules, on the project's *Edge* tab:** blocked addresses, a rate limit, and
  probe rules for paths nobody should ask for. Under *Allowed addresses* on the
  *Routing* tab, a staging copy can be kept entirely to your addresses.

The [handbook](https://docs.vallic.com/edge#restricted-paths) has the details.

See [Roles](roles.md) for who must use two-factor authentication, and [Keeping
secrets out of configuration](secrets.md) for credentials.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
