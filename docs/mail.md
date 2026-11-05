[← Domain Investors](../README.md)

# Mail

The site sends mail through Symfony Mailer, which on Vallic Cloud goes through the
platform's relay.

## Where mail goes out

- **On Vallic Cloud:** through the relay beside the site, which sends it on through
  your mail provider. Set the provider first: without a relay, mail never leaves the
  platform, and you would never hear about offers or sales. See
  [Deploy on Vallic Cloud](vallic-cloud.md).
- **Locally, on DDEV:** DDEV catches every message, and shows it with `ddev mailpit`.
  Nothing reaches the buyer's address.
- **Elsewhere:** Sendmail, unless you add a transport at *Configuration > System >
  Mailer > Transport*.

## What is sent

To buyers, from the store's address:

- their order receipt, and a note about the account checkout made for them;
- each [handover](handover.md) step, including the auth code, to them alone;
- answers to their [offers](offers.md), with a link to answer a counter-offer;
- with [leases](lease-to-own.md), each payment and how to pay it.

To you, at the address at *Commerce > Configuration > Domains > Domain portfolio*
(the store's email until then), each of which can be turned off there:

- a domain sold on the site, with where the buyer wants it;
- a domain a marketplace sold;
- a failing marketplace sync, at most once a day for each marketplace;
- a daily digest of the expiring domains that need a decision or a renewal by hand,
  or that you let expire but still renew automatically;
- domains a registrar's account no longer holds, other than sold ones;
- offers that expired.

The offers module sends its own mails, to the same address unless its settings name
another. No mail to you carries a credential, an auth code or a buyer's offer link.
Contact form messages go to the store's email too.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
