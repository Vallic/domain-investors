<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools\Hook;

use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Hook\Order\Order;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Theme\ThemeManagerInterface;

/**
 * Keeps an entity's tabs as tabs on the admin pages.
 *
 * The Navigation module moves the tabs of a domain, an order or a page (View,
 * Edit, Delete and those Commerce Domains adds, such as Prices and
 * Marketplaces) into a "Page actions" menu behind a "⋮" button in its top
 * bar, with an Edit button beside it, and hides the tabs while it is there.
 *
 * On the site, where the owner looks at a domain's page as buyers see it,
 * that Edit button is the way in, and the theme has no tabs to show. In the
 * admin theme the tabs stay in view, as on every other admin page, and the
 * menu that would repeat them goes.
 *
 * The Edit button is only made for a tab named after the edit route, as
 * core's entities name it. Commerce's tabs come from the Entity API module
 * ("entity.entity_tasks:entity.commerce_product.edit_form"), so a domain's
 * or an order's Edit would stay in the menu; it is brought out the same way.
 */
final class NavigationHooks {

  public function __construct(
    protected ThemeManagerInterface $themeManager,
    protected ConfigFactoryInterface $configFactory,
    protected RouteMatchInterface $routeMatch,
  ) {}

  /**
   * Implements hook_block_build_BASE_BLOCK_ID_alter() for local_tasks_block.
   *
   * After the Navigation module's, which hides the tabs.
   *
   * @phpstan-param array<mixed> $build
   */
  #[Hook('block_build_local_tasks_block_alter', order: Order::Last)]
  public function localTasksBlockAlter(array &$build, BlockPluginInterface $block): void {
    // Taken off rather than set: the block builds lazily, and a lazy builder
    // takes no other properties.
    if ($this->inAdminTheme() && ($build['#access'] ?? TRUE) === FALSE) {
      unset($build['#access']);
    }
  }

  /**
   * Implements hook_preprocess_HOOK() for top_bar.
   *
   * @phpstan-param array<mixed> $variables
   */
  #[Hook('preprocess_top_bar')]
  public function preprocessTopBar(array &$variables): void {
    if ($this->inAdminTheme()) {
      unset($variables['actions']['page_actions']);
      return;
    }
    if (!isset($variables['actions']['page_actions']['#page_actions'])) {
      return;
    }
    $actions = &$variables['actions']['page_actions'];
    $route = (string) $this->routeMatch->getRouteName();
    if (!empty($actions['#featured_page_actions']) || !preg_match('/^entity\.(.+)\.canonical$/', $route, $matches)) {
      return;
    }
    $edit = 'entity.entity_tasks:entity.' . $matches[1] . '.edit_form';
    $action = $actions['#page_actions'][$edit] ?? NULL;
    if ($action && ($action['#access'] ?? NULL)?->isAllowed()) {
      $actions['#featured_page_actions'][$edit] = [
        'page_action' => $action,
        'icon' => ['icon_id' => 'pencil'],
      ];
      unset($actions['#page_actions'][$edit]);
    }
  }

  /**
   * Whether the page is drawn in the admin theme.
   */
  protected function inAdminTheme(): bool {
    $admin = (string) $this->configFactory->get('system.theme')->get('admin');
    return $admin !== '' && $this->themeManager->getActiveTheme()->getName() === $admin;
  }

}
