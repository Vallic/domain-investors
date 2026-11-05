[← Domain Investors](../README.md)

# The storefront

The storefront is the Portfolio theme, built from the Domain Investors
design system: the domains for sale on the front page with a search by
name and by ending, a page per domain at `/domain/example.com` with its
price, Buy now and the offer form, and the pages How it works, About, Contact
(with the contact form), Privacy policy and Terms and conditions, as basic
pages you edit under *Content*; replace the example text before you open. Four
templates (Minimal, Standard, Showcase, Catalog) and six colour schemes:
one for the site under Appearance, and any domain can have its own on its
edit form, under Page.

## Pages

The site comes with these, under *Content*:

| Page | Address | |
|------|---------|---|
| How it works | `/how-it-works` | in the header and footer |
| About | `/about` | in the header and footer; example text to replace |
| Contact | `/contact` | in the header and footer; your text, with the contact form below it |
| Privacy policy | `/privacy-policy` | in the footer; example text to replace |
| Terms and conditions | `/terms` | in the footer; example text to replace; buyers agree to it at checkout |

Replace the example text before you open: those three pages say so at the top, to
visitors too, until you do. The header links show while a page keeps its address.
Contact messages go to the store's email, and Antibot keeps bots off the form.

## Templates and colours

The Portfolio theme draws the Domain Investors design system: its tokens are
`css/tokens.css`, its components `css/components.css` and one Single
Directory Component each under `components/` (domain name, price, sale
terms, card, list, table, buy box, transfer steps, header).

| Template | Front page | Domain page | Suits |
| --- | --- | --- | --- |
| Minimal | a price list, A to Z | one column: price, Buy now, offer | up to about 50 names |
| Standard | search, endings and cards | buy box beside the story and facts | most portfolios |
| Showcase | featured names with their stories | the name on a band, the story in a serif | a few strong names |
| Catalog | a table sorted by any column | facts table, similar names in a table | 500 names and more |

The site's template and colour scheme are set under Appearance › Settings ›
Portfolio. Beside the six schemes there is Custom, your own: a background, a
text and an accent colour, from which the other eight (raised and sunken
backgrounds, lines, muted text, the accent's hover and soft shades, the text
on buttons) are worked out the way the presets are built. Any of those can be
set by hand under "Every colour". A dark background makes it a night scheme.
Text has to reach WCAG AA contrast (4.5:1) against its background, links and
button text included, or the settings do not save while the site uses it. Each domain can override the
template and the scheme on its edit form, under Page, so one name can have a
Showcase page in Brass Night while the rest follow the site. "Featured" puts
a domain first in the lists, and on Showcase's front page with its story:
the description's summary, or its first paragraph.

Pages are Twig templates, chosen by a theme suggestion per template
(`commerce-product--domain--full--showcase.html.twig`,
`views-view--portfolio-domains--catalog.html.twig`), not Layout Builder: a
domain page is the same facts in one of four arrangements, which is a field,
not a layout to build per domain. Layout Builder stays installed for the
owner dashboard.

---

> <picture>
>   <source media="(prefers-color-scheme: dark)" srcset="images/logo-vallic-reversed.svg">
>   <img src="images/logo-vallic.svg" alt="Vallic" height="32">
> </picture>
>
> **Vallic Cloud hosting: Deploys your visitors never notice.** A new release goes live in one step, with no maintenance page, and you can go back to an earlier one.
>
> [See Vallic Cloud](https://vallic.com/cloud) · [Prices](https://vallic.com/cloud/pricing)
