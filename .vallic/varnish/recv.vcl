# Added to vcl_recv, before the platform's defaults and its Drupal rules,
# which is why the currency cookie can still be read here: the Drupal rules
# then strip every cookie Drupal does not need.
sub vcl_recv {
    # The currency a visitor chose with the currency switcher (Commerce
    # Currency Resolver's cookie submodule) reaches Drupal as a header, which
    # that submodule reads, and keys the cache (hash.vcl). Only ever a
    # three-letter code, so the cache cannot be split by arbitrary values.
    unset req.http.X-Commerce-Currency;
    if (req.http.Cookie ~ "(^|;\s*)commerce_currency=[A-Z]{3}(;|$)") {
        set req.http.X-Commerce-Currency = regsub(req.http.Cookie, "^.*?commerce_currency=([A-Z]{3}).*$", "\1");
    }
    # To price by the visitor's country before they choose, map
    # X-Country-Code to the currencies the site has, for example:
    #
    # elsif (req.http.X-Country-Code ~ "^(AT|BE|DE|ES|FR|HR|IE|IT|NL|PT)$") {
    #     set req.http.X-Commerce-Currency = "EUR";
    # }
    # elsif (req.http.X-Country-Code == "GB") {
    #     set req.http.X-Commerce-Currency = "GBP";
    # }

    # Batch runs long and streams; hand it straight through.
    if (req.url ~ "^/batch") {
        return (pipe);
    }

    # Per-visitor pages: admin, cart, checkout and payment returns, accounts,
    # AJAX, buyers' offer links, and the lander, which redirects by the
    # Referer and the domain parameter.
    if (req.url ~ "^(/update\.php|/admin(/.*)?|/system/.*|/batch.*|/cart.*|/checkout/.*|/payment/.*|/user.*|/domain-lander.*|/domain-offer/.*|.*/ajax/.*)$") {
        return (pass);
    }
}
