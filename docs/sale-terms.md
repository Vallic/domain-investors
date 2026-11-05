[← Domain Investors](../README.md)

# Sale terms

Each domain can set buy now, offers, a minimum offer, a floor price,
lease-to-own, and which marketplaces list it. What a domain leaves empty
comes from the marketplace's own terms on its provider form, then the general
ones at *Commerce > Configuration > Domains > Sale terms*, where you can also
turn off leases worked out from the price. That page shows what each
marketplace comes to; marketplaces get a change with the next full sync.

## On a domain

| Term | What it does | Left empty |
|------|--------------|------------|
| Buy now | sells at the price straight away; without it, the domain only takes offers | on |
| Accept offers | takes offers below the price | on |
| Minimum offer | the lowest offer taken, before any marketplace's price factor | worked out from the price |
| Floor price | the lowest price a marketplace's broker may agree to | worked out from the price |
| Lease-to-own | the longest lease, in monthly payments; 0 for none | worked out from the price |
| List on marketplaces, Not listed on | keep it for sale here but off all or some marketplaces | listed on all |

Each field says what leaving it empty comes to at the domain's price.

## The three levels

Empty terms resolve in order. A domain's own term always wins, even over a
marketplace with leases off.

1. **The domain's own**, on its form.
2. **The marketplace's own**, under *Sale terms* on its provider form: the floor,
   the minimum offer, lease-to-own on or off, and a shorter longest lease.
3. **The general ones**, at *Commerce > Configuration > Domains > Sale terms*. This
   site uses these, whatever the marketplaces do.

As it comes, the floor is 80% of the price, and the minimum offer is 50% of the
price below 10,000 and 25% from there up. The longest lease goes by the price:

| Price | Longest lease |
|-------|---------------|
| Below 2,000 | no lease |
| 2,000 to 8,000 | 6 months |
| 8,000 to 20,000 | 12 months |
| 20,000 to 50,000 | 24 months |
| 50,000 and above | 36 months |

The bands are the same everywhere. A marketplace may allow longer, such as 60
months on Afternic, but only a domain's own term gets there. The Sale terms page
lists what each enabled marketplace does with empty terms; only the Owner can
change it.

Leases on the site itself need the [lease recipe](lease-to-own.md).

## Many domains at once

*Set sale terms* and *Choose marketplaces* on *Commerce > Domains* change a
selection. Amounts and leases can also go back to being worked out from the price.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Made for this site.** Domain Investors comes ready for Vallic Cloud, with the database, cache and search it needs already set out.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
