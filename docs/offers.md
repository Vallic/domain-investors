[← Domain Investors](../README.md)

# Offers

Answer, counter or decline at *Commerce > Domain offers*; an
accepted offer becomes an order the buyer pays from their email. Or the
buyer pays through a marketplace's payment link instead: Spaceship's
SafePay, made for the agreed price (*Accepted offers are paid through* at
*Commerce > Configuration > Domains > Domain offers*), or one you paste
from Atom, Dynadot, Afternic and the like when accepting. The marketplace
takes the payment and hands the domain over; the offer is marked paid
once it reports the payment, or when you mark it paid.

## How an offer goes

1. The form shows on domains that are for sale and whose sale terms accept offers,
   and offers start at the domain's [minimum offer](sale-terms.md), in the currency
   the buyer sees.
2. Answer at *Commerce > Domain offers*: accept, decline or counter. The buyer gets
   an email with a link to answer a counter-offer, no account needed.
3. Accepting creates an order for the domain at the agreed amount, which the buyer
   pays through checkout from the link in their email. Placing it marks the offer
   purchased.

Offers nobody answers or pays expire after 14 days. Change that at *Commerce >
Configuration > Domains > Domain offers*, along with the least price that takes
offers, whether Atom premium names take them, and whether buyers must give a phone
number.

## Payment links

**Made for you, with Spaceship's SafePay:** set *Accepted offers are paid through*
on the offer settings page. Accepting opens the transaction for the agreed price,
the buyer pays there, and cron reads the link: once paid, the offer is purchased,
the domain goes off sale, you are mailed and the sale is recorded. Spaceship takes
US dollars only, and needs the domain listed in SellerHub. When a link can't be
made, the offer gets a checkout order instead.

**Pasted, from Atom, Dynadot, Afternic and the like:** choose *With a payment link I
paste from a marketplace* when accepting. The site can't read these links, so the
offer page has *Paid*, which purchases the offer, takes the domain off sale and
records the sale, and *Not paid: end the offer*.

Either can be a lease to own, with its number of monthly payments. The [Commerce
Domains README](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#offers) has the details.

## On Escrow.com

With `portfolio_escrow`, the form adds *Negotiate on Escrow.com*, for prices in USD,
EUR, GBP, AUD or CAD. The negotiation, payment and transfer run there, and the offer
on the site follows. The offer appears here, and you are told about it, once the
buyer has made it on Escrow.com. Starts they never finish are deleted after 30 days.
The domain goes off sale when the offer is accepted or a payment shows the price is
settled. See [Take payments](payments.md).

## Spam

The form takes five offers an hour from one visitor, and Antibot checks it too:
see [Sign-in protection](sign-in-protection.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
