<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\search_api\IndexInterface;

/**
 * Starts an index afresh on Meilisearch.
 *
 * Meilisearch keeps its indexes when the site's database goes: after a
 * reinstall, or a database brought from elsewhere, the old index is still
 * there. Search API Meilisearch then fails to create it ("Index `domains`
 * already exists"), logs an error, and leaves the old index's settings and
 * documents as they were. So before an index is put on a Meilisearch server,
 * whatever Meilisearch holds under its name is removed; Search API indexes
 * everything again right after.
 */
final class MeilisearchHooks {

  // The Search API Meilisearch backend.
  private const BACKEND = 'search_api_meilisearch';

  /**
   * Implements hook_ENTITY_TYPE_presave() for search_api_index.
   */
  #[Hook('search_api_index_presave')]
  public function indexPresave(IndexInterface $index): void {
    if (!$this->willBeAdded($index)) {
      return;
    }
    $server = $index->getServerInstanceIfAvailable();
    if ($server && $server->status() && $server->getBackendId() === self::BACKEND) {
      // Nothing there is fine: the backend then removes nothing, quietly.
      $server->getBackend()->removeIndex($index);
    }
  }

  /**
   * Checks whether saving will add the index to its server.
   *
   * As Search API does: a new enabled index, one enabled again, or an enabled
   * one moved to another server.
   */
  private function willBeAdded(IndexInterface $index): bool {
    if (!$index->status() || !$index->getServerId()) {
      return FALSE;
    }
    if ($index->isNew()) {
      return TRUE;
    }
    $original = $index->getOriginal();
    return !$original instanceof IndexInterface
      || !$original->status()
      || $original->getServerId() !== $index->getServerId();
  }

}
