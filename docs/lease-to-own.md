[← Domain Investors](../README.md)

# Lease to own

**Lease to own** (`portfolio_lease`, with Stripe, bank transfer or Wise):
buyers of a domain with Buy now choose at checkout between paying in full
and a lease of 6 to 60 months, up to the domain's *Lease-to-own* term (or
the rule worked out from its price). They pay a down payment (*Lease down
payment* on the domain, else 10%), then the rest monthly:
- **by card** (Stripe): charged on cron to the card saved at checkout;
- **by bank transfer or Wise**: each month the buyer is mailed the amount
  and how to pay it (your bank transfer instructions, or a Wise pay link).
  A payment counts once it is on the order: *Record received* on the lease
  page, *Receive* on the order's Payments tab, or Wise's own notification.

The handover starts with the last payment. A lease needs an account, so
the recipe lets visitors sign up, logged in at once to carry on with
checkout. Missed payments, and requests left unpaid, are retried or
reminded and reported to the store's email; leases are listed under
*Commerce > Domains > Leases*, which only the Owner sees, and set up
under *Commerce > Domains > Settings > Domain leases*. Domain pages show
the longest lease, "or $3,600 down, then $600/mo for 24 months", only
while the site can take one.

## The terms offered

Terms of 6, 12, 24, 36, 48 and 60 months are offered, up to the domain's longest.
That is its own *Lease-to-own* term, or the one worked out from its price: see
[Sale terms](sale-terms.md). The monthly payments split what the down payment
leaves, the last one making up the rounding. Each is due on the same day of the
month from when the order was placed.

## When a payment is missed

A failed payment is tried again every few days (*Try a missed payment again after*,
3), a few times (*Tries before the lease is in default*, 3). The buyer hears about
each failure, and you do too. Past the tries the lease is in default: nothing more
is charged, and you charge it by hand on the lease page, or cancel it, once you
have spoken with the buyer. Cards that need the buyer to approve the payment fail
the same way. A payment asked for by hand that has not arrived after the same days
counts as missed: the buyer is reminded, and the lease goes into default the same
way.

A card charge that ends without a clear answer, as after a timeout, is never tried
again by itself, because the money may have been taken. You are told to check at
the gateway, then say on the lease page whether it was taken. Canceling an order
cancels its lease.

## In your records

The order holds the full price and is placed with the rest to pay, so the domain
goes off sale at once. [Sales and profit](sales-and-profit.md) counts a lease in
full in the year it started.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
