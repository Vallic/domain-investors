<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools\Hook;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;

/**
 * Leaves personal contact forms off the account forms.
 *
 * The base recipe turns personal contact forms off: buyers write through the
 * site's contact form. The setting to open one would still be offered on
 * sign-up and on the account form, so it is taken away.
 */
final class AccountFormHooks {

  /**
   * Implements hook_form_FORM_ID_alter() for user_register_form.
   *
   * @phpstan-param array<mixed> $form
   */
  #[Hook('form_user_register_form_alter')]
  public function registerFormAlter(array &$form, FormStateInterface $form_state): void {
    $this->hideContact($form);
  }

  /**
   * Implements hook_form_FORM_ID_alter() for user_form.
   *
   * @phpstan-param array<mixed> $form
   */
  #[Hook('form_user_form_alter')]
  public function accountFormAlter(array &$form, FormStateInterface $form_state): void {
    $this->hideContact($form);
  }

  /**
   * Hides the personal contact form setting.
   *
   * @phpstan-param array<mixed> $form
   */
  protected function hideContact(array &$form): void {
    if (isset($form['contact'])) {
      $form['contact']['#access'] = FALSE;
    }
  }

}
