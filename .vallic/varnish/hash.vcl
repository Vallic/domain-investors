# Added to vcl_hash, before the platform adds the URL and the host.
sub vcl_hash {
    # Prices differ by currency, so a page is cached once per currency a
    # visitor chose (recv.vcl). Without the currency switcher the header is
    # never set, and every visitor shares one copy.
    hash_data(req.http.X-Commerce-Currency);
}
