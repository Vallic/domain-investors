[← Domain Investors](../README.md)

# The domain checkout

A checkout for one domain at a time: your details, then where the domain goes (a push
to the buyer's account at the registrar, or a transfer elsewhere) and
payment, then review. Placing the order takes the domain off sale here and
on the marketplaces.

*Buy now* empties the cart and opens checkout, and is left out for domains whose
sale terms take offers only. There is no cart page to linger on: it goes to the
open checkout.

## The steps

1. **Your details:** the buyer's email address, entered twice, and billing details.
2. **Domain transfer and payment:** whether to push the domain to their account at
   its registrar or transfer it elsewhere, and how to pay. With
   [leases](lease-to-own.md), whether to pay in full or lease.
3. **Review:** the order, and agreeing to the [terms and
   conditions](storefront.md#pages). With Stripe, cards are entered here.
4. **Payment**, where the gateway needs a step of its own.
5. **Complete:** a thank-you with what happens next.

The answer about the transfer stays on the order, and the
[handover](handover.md) follows it.

## Accounts

Buyers check out as guests. Checkout then makes their account and emails them, so
they can see their order and its handover later: see
[Buyers' accounts](buyer-accounts.md).

## After the order

Placing the order takes the domain off sale here and, with the next sync, on the
marketplaces. An order is only paid once the money has arrived: a bank transfer's
order waits until you record the payment. See [Take payments](payments.md).

An unpaid order holds its domain for 7 days. The buyer is reminded two days before
the end, then the order is canceled and the domain goes back on sale. Orders paid
through Escrow.com, and orders with money on them, are left alone. If a payment
arrives after the order is canceled, you get an email and decide what to do,
because the domain may have sold again. Change the number of days with
`commerce_domains_checkout.settings:unpaid_hold_days` (0 for no limit).

A placed order stays in *Fulfillment* (*Validation* with the
[Escrow.com recipe](payments.md)), so that it can still be canceled. It completes
by itself once it is paid and its domain has been [handed over](handover.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
