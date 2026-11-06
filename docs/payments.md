[← Domain Investors](../README.md)

# Take payments

Gateways read their credentials from environment variables
(`web/sites/default/settings.portfolio.php`) and never store them: their
forms show where each credential comes from instead of asking for it.

| Gateway        | Variables                                                               |
|----------------|-------------------------------------------------------------------------|
| Stripe         | `STRIPE_PUBLISHABLE_KEY`, `STRIPE_SECRET_KEY`, `STRIPE_WEBHOOK_SIGNING_SECRET` |
| PayPal         | `PAYPAL_CLIENT_ID`, `PAYPAL_CLIENT_SECRET`, `PAYPAL_WEBHOOK_ID`         |
| Wise           | `WISE_TAG`, `WISE_PUBLIC_KEY`                                           |
| Escrow.com     | `ESCROW_API_KEY`, `ESCROW_EMAIL`, `ESCROW_WEBHOOK_SECRET`               |
| Bank transfer  | none: your bank details go in its payment instructions                  |

1. Set the variables, with test keys on development and staging. The
   installer wrote samples for the chosen gateways into `.env` beside `web/`:
   replace them there, or set the variables where the site runs.
2. The installer enabled the gateways you chose, even with samples, so the
   checkout can be tried; a payment fails until the keys are real. Where
   payments are live, a gateway with missing or sample keys is turned off.
   Enable another at *Commerce > Configuration > Payment gateways* (or
   `drush config:set commerce_payment.commerce_payment_gateway.stripe status 1`).
3. Gateways run in test mode, and in live mode on a Vallic Cloud production
   environment. `PAYMENT_MODE=live` or `PAYMENT_MODE=test` says otherwise for
   an environment; the keys have to match the mode.

- **Bank transfer:** replace the placeholders in its payment instructions
  with your bank details first. Buyers see them after placing the order; the
  handover starts when you record the payment on the order's *Payments* tab.
- **Stripe:** cards are entered on the review step. For webhooks, point
  Stripe at `https://YOUR-SITE/payment/notify/stripe` and set
  `STRIPE_WEBHOOK_SIGNING_SECRET`.
- **PayPal:** the buyer pays on a PayPal page through a
  [payment link](https://www.drupal.org/project/commerce_paypal_link), and the
  payment is checked with PayPal when they come back. The client ID and secret
  are a REST app's in the PayPal developer dashboard, the sandbox or the live one
  to match the mode. A buyer who pays and never comes back, or a pending payment
  that clears, reaches the site only through a webhook: add one for
  `https://YOUR-SITE/payment/notify/paypal` with the events in the module's README,
  and set `PAYPAL_WEBHOOK_ID`. PayPal hasn't yet been seen sending these for payment
  links, so check PayPal for payments like that.
- **Wise:** the buyer pays into your Wise business account through a Wise
  Quick Pay link, and the payment is recorded when Wise reports the deposit.
  `WISE_TAG` is your account's Wise tag (*Security & Privacy > Contacts
  discoverability*); `WISE_PUBLIC_KEY` is Wise's webhook public key, the
  sandbox or the production one to match the mode. In your Wise account,
  under *Integration & Tools > Webhooks*, add an *Account deposit events*
  webhook to `https://YOUR-SITE/payment/notify/wise`.
- **Escrow.com:** buy now through Escrow.com, and offers negotiated there,
  for prices in USD, EUR, GBP, AUD or CAD. Set `ESCROW_WEBHOOK_SECRET` to a
  long random value (`openssl rand -hex 32`), then add one webhook in your
  Escrow.com account: *Commerce > Configuration > Payment > Escrow settings*
  shows its URL, `https://YOUR-SITE/payment/webhook/escrow/SECRET`, which both
  gateways share. Keep it as private as the API key. Until the variable is set,
  webhooks are refused; to replace it, change the variable and the webhook. The
  recipe moves orders to the Escrow workflow, so orders paid otherwise wait
  in *Validation* until you complete them. Applying it to a site that
  already has domains? Run `drush domain-investors:escrow-setup` once
  afterwards: it marks those domains in stock (they would count as off sale
  otherwise), keeps the other gateways off Escrow.com offers, and moves open
  orders from *Fulfillment* to *Validation*.

To pay monthly instead, see [Lease to own](lease-to-own.md). Offers can also be
paid through a marketplace's payment link: see [Offers](offers.md).

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: An uptime promise.** 99.9% monthly availability, with service credits when it is missed, and basic WAF and bot management included.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
