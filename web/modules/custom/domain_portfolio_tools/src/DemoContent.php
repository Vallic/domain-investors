<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools;

use Drupal\commerce_domains_import\DomainImporter;
use Drupal\commerce_domains_import\ImportOptions;
use Drupal\commerce_domains_import\ImportParser;
use Drupal\commerce_domains_import\ImportReport;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\State\StateInterface;

/**
 * Adds domains to try the site with, and takes them away again.
 *
 * They are real domains, for sale by the people who made this project, so a
 * site can look the way it would with a full catalog. They belong on a copy to
 * try things on, not on a site open to buyers: they are never listed on
 * marketplaces, production environments aren't offered them, and those added
 * are remembered, so removing them can't touch the owner's own.
 */
final class DemoContent {

  // The IDs of the domains added, for removing them.
  private const STATE_KEY = 'domain_portfolio_tools.demo_products';

  // Domains saved in one go.
  private const CHUNK_SIZE = 25;

  public function __construct(
    protected DomainImporter $importer,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected StateInterface $state,
  ) {}

  /**
   * Whether this environment may have them: anything but production on Vallic.
   */
  public static function isAvailable(): bool {
    return getenv('VALLIC_ENVIRONMENT_TYPE') !== 'production';
  }

  /**
   * Returns the domains to add, in chunks to save one at a time.
   *
   * @return \Drupal\commerce_domains_import\ImportRow[][]
   *   The rows.
   */
  public function chunks(): array {
    $file = (string) file_get_contents(dirname(__DIR__) . '/demo/domains.csv');
    return array_chunk(ImportParser::parseCsv($file), self::CHUNK_SIZE);
  }

  /**
   * Adds these domains. Those already in the catalog are left as they are.
   *
   * @param \Drupal\commerce_domains_import\ImportRow[] $rows
   *   The rows.
   *
   * @return int
   *   How many were added.
   */
  public function import(array $rows): int {
    /** @var \Drupal\commerce_store\Entity\StoreInterface $store */
    $store = $this->entityTypeManager->getStorage('commerce_store')->loadDefault();
    $options = new ImportOptions(
      productType: 'domain',
      store: (int) $store->id(),
      // Prices in the store's currency, whichever that is: it is only a demo.
      currency: $store->getDefaultCurrencyCode(),
      // Never on the site owner's marketplace accounts.
      marketplaces: FALSE,
    );
    $report = new ImportReport();
    $this->importer->import($rows, $options, $report);
    if ($report->created) {
      $ids = $this->entityTypeManager->getStorage('commerce_product')->getQuery()
        ->accessCheck(FALSE)
        ->condition('domain', $report->created, 'IN')
        ->execute();
      $this->state->set(self::STATE_KEY, array_merge($this->added(), array_values(array_map('intval', $ids))));
    }
    return count($report->created);
  }

  /**
   * Removes the domains added here, but for any that have an order.
   *
   * @return array{removed: int, kept: int}
   *   How many went, and how many stay because an order points at them.
   */
  public function remove(): array {
    $products = $this->entityTypeManager->getStorage('commerce_product');
    $items = $this->entityTypeManager->getStorage('commerce_order_item');
    $kept = [];
    $removed = 0;
    foreach (array_chunk($this->added(), self::CHUNK_SIZE) as $chunk) {
      foreach ($products->loadMultiple($chunk) as $id => $product) {
        /** @var \Drupal\commerce_product\Entity\ProductInterface $product */
        $variations = $product->getVariationIds();
        $ordered = $variations && $items->getQuery()
          ->accessCheck(FALSE)
          ->condition('purchased_entity', $variations, 'IN')
          ->range(0, 1)
          ->execute();
        if ($ordered) {
          $kept[] = (int) $id;
          continue;
        }
        $product->delete();
        $removed++;
      }
      $products->resetCache($chunk);
    }
    $kept ? $this->state->set(self::STATE_KEY, $kept) : $this->state->delete(self::STATE_KEY);
    return ['removed' => $removed, 'kept' => count($kept)];
  }

  /**
   * Returns the IDs of the domains added and not removed yet.
   *
   * @return int[]
   *   The product IDs.
   */
  protected function added(): array {
    return $this->state->get(self::STATE_KEY, []);
  }

}
