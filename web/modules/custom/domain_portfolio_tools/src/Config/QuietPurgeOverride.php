<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools\Config;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryOverrideInterface;
use Drupal\Core\Config\StorageInterface;

/**
 * Keeps Purge quiet where there is nothing to purge.
 *
 * The recipe portfolio_vallic_cloud purges Varnish inside the Vallic Cloud
 * stack and the Vallic CDN, and its configuration travels with the site to
 * every environment. Elsewhere the Varnish purgers would log a failed
 * connection for every change saved. So where neither VARNISH_HOST (Vallic
 * Cloud, or DDEV's Varnish add-on with VARNISH_HOST=varnish) nor the CDN's
 * purge socket is set, Purge's queuers and processors are switched off here:
 * nothing is queued, and nothing is sent. The exported configuration keeps
 * them on.
 */
final class QuietPurgeOverride implements ConfigFactoryOverrideInterface {

  public function __construct(
    protected StorageInterface $storage,
  ) {}

  /**
   * Whether this environment has a cache to purge.
   */
  public static function hasCache(): bool {
    return (string) getenv('VARNISH_HOST') !== '' || (string) getenv('VALLIC_CDN_PURGE_SOCKET') !== '';
  }

  /**
   * {@inheritdoc}
   */
  public function loadOverrides($names): array {
    if (!in_array('purge.plugins', $names, TRUE) || self::hasCache()) {
      return [];
    }
    // Read without the config factory, which is what is asking.
    $plugins = $this->storage->read('purge.plugins');
    if (!is_array($plugins)) {
      return [];
    }
    $override = [];
    foreach (['queuers', 'processors'] as $type) {
      foreach (array_keys($plugins[$type] ?? []) as $delta) {
        $override[$type][$delta]['status'] = FALSE;
      }
    }
    return $override ? ['purge.plugins' => $override] : [];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheSuffix(): string {
    return 'domain_portfolio_purge_' . (self::hasCache() ? 'on' : 'off');
  }

  /**
   * {@inheritdoc}
   */
  public function createConfigObject($name, $collection = StorageInterface::DEFAULT_COLLECTION) {
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheableMetadata($name): CacheableMetadata {
    return new CacheableMetadata();
  }

}
