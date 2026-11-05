<?php

/**
 * @file
 * Adds a few domains for the smoke test, one per page template.
 *
 * Run with drush php:script after installing. Not for a real site.
 */

declare(strict_types=1);

use Drupal\commerce_price\Price;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductVariation;

$store = \Drupal::entityTypeManager()->getStorage('commerce_store')->loadDefault();
$currency = $store->getDefaultCurrencyCode();
$domains = [
  // Domain, price, its own template and scheme (empty: the site's), featured.
  ['orbel.com', 18000, '', '', TRUE],
  ['paloma.ai', 15500, 'showcase', 'brass-night', TRUE],
  ['quillwork.com', 8900, 'minimal', 'mono', FALSE],
  ['calyx.io', 7300, 'catalog', 'cobalt', FALSE],
  ['kintsu.co', 2200, 'standard', 'custom', FALSE],
];
foreach ($domains as [$name, $price, $template, $scheme, $featured]) {
  $variation = ProductVariation::create([
    'type' => 'domain',
    'sku' => $name,
    'price' => new Price((string) $price, $currency),
    'sale_lease_months' => 12,
    'status' => 1,
  ]);
  $variation->save();
  Product::create([
    'type' => 'domain',
    'title' => $name,
    'domain' => $name,
    'stores' => [$store],
    'variations' => [$variation],
    'status' => 1,
    'page_template' => $template ?: NULL,
    'page_scheme' => $scheme ?: NULL,
    'page_featured' => $featured,
    'body' => ['value' => "<p>Why $name works.</p>", 'format' => 'basic_html'],
  ])->save();
}
echo count($domains) . " domains added.\n";
