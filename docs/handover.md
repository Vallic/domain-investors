[← Domain Investors](../README.md)

# Handing a domain over

Once an order is paid, the order's *Handover* tab walks you
through unlocking the domain and emailing the auth code, or recording the
push, and emails the buyer at each step.

## When it starts

A handover starts when the order is paid in full, so nothing that can't be undone
happens before the money arrives, and emails the buyer what happens next. An order
paid by bank transfer starts when its payment is recorded on the *Payments* tab.
Orders paid on Escrow.com are left to Escrow.com, which holds the money until the
buyer confirms they have the domain. *Start the handover* on the *Handover* tab
starts any other placed order by hand. Canceling the order cancels its handovers.

Marking the domain transferred completes the order, if it is paid in full. If you
handed a domain over before the money arrived, complete the order yourself once it
has.

## The steps

The order's *Handover* tab shows each domain's state, where the buyer wants it, the
registrar and what to do next, with a button for each step open now. Each step asks
first.

- **Unlock:** turns the transfer lock off through the registrar's API.
- **Email the auth code:** gets the code from the registrar's API and emails it to
  the buyer alone, with how to start the transfer. Where the API can't give it,
  enter it yourself and it goes the same way. The code is never shown, kept or
  logged.
- **Mark pushed:** once you have pushed the domain to the buyer's account at the
  registrar, which you do there. The buyer is told it is on its way, and to accept
  it if their account asks.
- **Mark transferred:** once the domain has left your account. The buyer is emailed
  that it is theirs, and the domain no longer names your registrar. A sync that
  still finds it in the account records it there again, so the form warns while the
  account holds it.
- **Cancel the handover:** emails nobody, and leaves the domain off sale.

What each registrar's API can do differs: see
[Domain management](https://git.drupalcode.org/project/commerce_domains/-/blob/1.x/README.md#domain-management).

*Commerce > Domain handovers* lists the open handovers, longest waiting first. The
Owner and the Assistant can both take these steps: see [Roles](roles.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Backups come with it.** Production is backed up every day, and the last four days are kept, ready to restore.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
