<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * What recipes/portfolio_escrow cannot do as configuration of its own.
 *
 * - The other payment gateways have to stay off orders whose offer is
 *   negotiated on Escrow.com, which pay through Escrow Offer only. Gateways
 *   are changed here as plain configuration: loading one as an entity would
 *   bring settings.portfolio.php's credentials along, and saving it would
 *   write them into the configuration. A recipe's config actions do load
 *   entities that way, which is why the recipe leaves this to code.
 * - Commerce Escrow's "In stock" field is empty on domains added before it,
 *   and Commerce Domains counts those as off sale.
 * - The recipe changes the order workflow, which has no "Fulfillment" state.
 *   Open orders move to "Validation", where the Escrow workflow keeps them.
 */
final class EscrowSetup {

  // The gateways of recipes/portfolio_escrow, which are not to be excluded.
  public const ESCROW_GATEWAYS = ['escrow_pay', 'escrow_offer'];

  // The condition that keeps a gateway off Escrow.com offers.
  public const CONDITION = 'commerce_domains_order_escrow_offer';

  public function __construct(
    protected ConfigFactoryInterface $configFactory,
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {}

  /**
   * Keeps the other gateways off orders of offers negotiated on Escrow.com.
   *
   * @param bool $dry_run
   *   Whether to only report.
   *
   * @return string[]
   *   The IDs of the gateways that get (or would get) the condition.
   */
  public function excludeOffersFromOtherGateways(bool $dry_run = FALSE): array {
    $changed = [];
    foreach ($this->configFactory->listAll('commerce_payment.commerce_payment_gateway.') as $name) {
      $gateway = $this->configFactory->getEditable($name);
      $id = (string) $gateway->get('id');
      if (in_array($id, self::ESCROW_GATEWAYS, TRUE)) {
        continue;
      }
      $conditions = (array) $gateway->get('conditions');
      if (in_array(self::CONDITION, array_column($conditions, 'plugin'), TRUE)) {
        continue;
      }
      $changed[] = $id;
      if (!$dry_run) {
        $conditions[] = [
          'plugin' => self::CONDITION,
          'configuration' => ['escrow_offer' => FALSE],
        ];
        $gateway->set('conditions', $conditions)->save();
      }
    }
    return $changed;
  }

  /**
   * Fills in "In stock" on domains that have it empty.
   *
   * @param bool $dry_run
   *   Whether to only count.
   *
   * @return array{0: int, 1: int}
   *   How many were (or would be) marked in stock, and out of stock.
   */
  public function fillInStock(bool $dry_run = FALSE): array {
    $storage = $this->entityTypeManager->getStorage('commerce_product_variation');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('type', 'domain')
      ->notExists('in_stock')
      ->execute();
    $in_stock = $out = 0;
    foreach (array_chunk($ids, 100) as $chunk) {
      /** @var \Drupal\commerce_product\Entity\ProductVariationInterface $variation */
      foreach ($storage->loadMultiple($chunk) as $variation) {
        if (!$variation->hasField('in_stock')) {
          continue;
        }
        // Without the field, a published variation was for sale.
        $variation->isPublished() ? $in_stock++ : $out++;
        if (!$dry_run) {
          $variation->set('in_stock', $variation->isPublished())->save();
        }
      }
      $storage->resetCache($chunk);
    }
    return [$in_stock, $out];
  }

  /**
   * Moves open orders into the state the Escrow workflow keeps them in.
   *
   * No transition leads there, so nothing else reacts: the orders were placed
   * already and stay so.
   *
   * @param bool $dry_run
   *   Whether to only count.
   *
   * @return int
   *   How many orders moved (or would move).
   */
  public function moveOpenOrders(bool $dry_run = FALSE): int {
    $storage = $this->entityTypeManager->getStorage('commerce_order');
    $ids = $storage->getQuery()
      ->accessCheck(FALSE)
      ->condition('state', 'fulfillment')
      ->execute();
    if (!$dry_run) {
      foreach (array_chunk($ids, 100) as $chunk) {
        foreach ($storage->loadMultiple($chunk) as $order) {
          $order->set('state', 'validation')->save();
        }
        $storage->resetCache($chunk);
      }
    }
    return count($ids);
  }

}
