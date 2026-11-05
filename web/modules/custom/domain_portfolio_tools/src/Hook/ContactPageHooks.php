<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools\Hook;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityFormBuilderInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;

/**
 * Puts the contact form on the Contact page.
 *
 * The page is a basic page, so the owner can edit its text, with the alias
 * /contact, which takes over from the form's own page there. So the form is
 * added below the text. Without the page, /contact is the form's own again.
 */
final class ContactPageHooks {

  // The default content recipes/portfolio_base ships for the page.
  public const PAGE_UUID = 'b174bbb7-7beb-4d00-a76b-4d84cae9e22d';

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityFormBuilderInterface $entityFormBuilder,
    protected ConfigFactoryInterface $configFactory,
    protected AccountInterface $currentUser,
  ) {}

  /**
   * Implements hook_ENTITY_TYPE_view() for node.
   *
   * @phpstan-param array<mixed> $build
   */
  #[Hook('node_view')]
  public function nodeView(array &$build, NodeInterface $node, EntityViewDisplayInterface $display, string $view_mode): void {
    if ($view_mode !== 'full' || $node->uuid() !== self::PAGE_UUID) {
      return;
    }
    $settings = $this->configFactory->get('contact.settings');
    $form_id = (string) $settings->get('default_form');
    $contact_form = $form_id ? $this->entityTypeManager->getStorage('contact_form')->load($form_id) : NULL;
    $cache = CacheableMetadata::createFromRenderArray($build)
      ->addCacheableDependency($settings)
      ->addCacheContexts(['user.permissions']);
    if ($contact_form && $this->currentUser->hasPermission('access site-wide contact form')) {
      $cache->addCacheableDependency($contact_form);
      $message = $this->entityTypeManager->getStorage('contact_message')->create(['contact_form' => $form_id]);
      $build['contact_form'] = $this->entityFormBuilder->getForm($message);
      $build['contact_form']['#weight'] = 100;
    }
    $cache->applyTo($build);
  }

}
