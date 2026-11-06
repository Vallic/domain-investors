<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools\Plugin\Block;

use Drupal\commerce_domains_owner\Controller\DashboardController;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Access\AccessResultInterface;
use Drupal\Core\Block\Attribute\Block;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\DependencyInjection\ClassResolverInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * The owner's domain dashboard, as a block for the admin landing page.
 *
 * Commerce Domains Owner builds the dashboard as a page (Commerce > Domains >
 * Dashboard); this puts the same sections on the Dashboard module's page,
 * which is where people land after logging in.
 */
#[Block(
  id: 'domain_investors_owner_dashboard',
  admin_label: new TranslatableMarkup('Domain portfolio overview'),
  category: new TranslatableMarkup('Domain Investors'),
)]
final class OwnerDashboardBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The class resolver.
   */
  protected ClassResolverInterface $classResolver;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition): static {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->classResolver = $container->get('class_resolver');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function build(): array {
    /** @var \Drupal\commerce_domains_owner\Controller\DashboardController $dashboard */
    $dashboard = $this->classResolver->getInstanceFromDefinition(DashboardController::class);
    return $dashboard->build();
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account): AccessResultInterface {
    // The same as the dashboard page asks.
    return AccessResult::allowedIfHasPermissions($account, ['view domain portfolio', 'manage domain portfolio'], 'OR');
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge(): int {
    // Sync records live in State, which has no cache tags.
    return 0;
  }

}
