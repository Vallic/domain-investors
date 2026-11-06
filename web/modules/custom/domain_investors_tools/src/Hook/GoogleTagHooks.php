<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Reports an offer sent from a domain page to Google Tag, as a lead.
 *
 * Google Tag reports views, Buy now, checkout and purchases itself; an offer
 * is Commerce Domains' own form, so it says nothing. With
 * recipes/portfolio_google_tag, a sent offer becomes a generate_lead event
 * with the amount offered, on the page the buyer lands on next.
 */
final class GoogleTagHooks {

  /**
   * Implements hook_form_FORM_ID_alter() for commerce_domains_offer_form.
   *
   * @phpstan-param array<mixed> $form
   */
  #[Hook('form_commerce_domains_offer_form_alter')]
  public function offerFormAlter(array &$form, FormStateInterface $form_state): void {
    if (\Drupal::hasService('google_tag.event_collector')) {
      $form['#submit'][] = [static::class, 'offerSubmitted'];
    }
  }

  /**
   * Form submit handler: collects the generate_lead event.
   *
   * @phpstan-param array<mixed> $form
   */
  public static function offerSubmitted(array &$form, FormStateInterface $form_state): void {
    \Drupal::service('google_tag.event_collector')->addDelayedEvent('generate_lead', [
      'value' => (string) $form_state->getValue('amount'),
      'currency' => (string) $form_state->get('currency'),
    ]);
  }

}
