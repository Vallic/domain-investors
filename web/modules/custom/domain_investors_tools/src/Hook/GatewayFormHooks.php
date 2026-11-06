<?php

declare(strict_types=1);

namespace Drupal\domain_investors_tools\Hook;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\EntityFormInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Render\Element;
use Drupal\Core\Site\Settings;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Keeps payment gateway credentials out of the gateway forms.
 *
 * The file settings.portfolio.php reads gateway credentials from environment
 * variables, and lists them in a setting,
 * domain_investors_gateway_credentials. Stripe's, PayPal's and Escrow.com's
 * forms would ask for those credentials and save them into configuration,
 * which is exported. For the gateways listed there, each credential field is
 * replaced by a line saying which variable it comes from and whether that is
 * set, and is saved empty, so the form saves everything else and the
 * variable keeps working.
 */
final class GatewayFormHooks {

  /**
   * Implements hook_form_BASE_FORM_ID_alter() for commerce_payment_gateway_form.
   *
   * @phpstan-param array<mixed> $form
   */
  #[Hook('form_commerce_payment_gateway_form_alter')]
  public function gatewayFormAlter(array &$form, FormStateInterface $form_state): void {
    $form_object = $form_state->getFormObject();
    if (!$form_object instanceof EntityFormInterface || !isset($form['configuration'])) {
      return;
    }
    $id = $form_object->getEntity()->id();
    $credentials = Settings::get('domain_investors_gateway_credentials', [])[$id] ?? [];
    if (!$credentials) {
      return;
    }
    // The plugin builds its form while the element is processed; this runs
    // once it has.
    $form['configuration']['#domain_investors_credentials'] = $credentials;
    $form['configuration']['#after_build'][] = [self::class, 'afterBuildConfiguration'];
  }

  /**
   * Replaces the credential fields of the gateway's plugin form.
   *
   * @phpstan-param array<mixed> $element
   * @phpstan-return array<mixed>
   */
  public static function afterBuildConfiguration(array $element, FormStateInterface $form_state): array {
    foreach ($element['#domain_investors_credentials'] as $setting => $variable) {
      $path = self::findChild($element, $setting);
      if ($path === NULL) {
        continue;
      }
      $child = &NestedArray::getValue($element, $path);
      $title = $child['#title'] ?? $setting;
      $child['#required'] = FALSE;
      $child['#access'] = FALSE;
      // Saved empty, whatever was there: the variable fills it at runtime.
      $form_state->setValueForElement($child, '');
      $value = getenv($variable);
      $set = is_string($value) && $value !== '';

      $parent_path = array_slice($path, 0, -1);
      $parent = &NestedArray::getValue($element, $parent_path);
      $parent['domain_investors_' . $setting] = [
        '#type' => 'item',
        '#title' => $title,
        '#markup' => $set
          ? new TranslatableMarkup('From the <code>@variable</code> environment variable, which is set.', ['@variable' => $variable])
          : new TranslatableMarkup('From the <code>@variable</code> environment variable, which is not set yet. Set it where the site runs; a value typed into this form would be saved into the exported configuration.', ['@variable' => $variable]),
        '#weight' => $child['#weight'] ?? 0,
      ];
      unset($child, $parent);
    }

    // Stripe would check the secret key against Stripe on saving; the key is
    // not in the form to check.
    if ($path = self::findChild($element, 'validate_api_keys')) {
      $checkbox = &NestedArray::getValue($element, $path);
      $checkbox['#access'] = FALSE;
      $form_state->setValueForElement($checkbox, 0);
      unset($checkbox);
    }
    return $element;
  }

  /**
   * Finds a child element by its key, at any depth.
   *
   * @phpstan-param array<mixed> $element
   *
   * @return string[]|null
   *   The keys leading to it, or NULL.
   */
  protected static function findChild(array $element, string $key): ?array {
    foreach (Element::children($element) as $child) {
      if ($child === $key) {
        return [$child];
      }
      if (is_array($element[$child]) && ($path = self::findChild($element[$child], $key)) !== NULL) {
        return [$child, ...$path];
      }
    }
    return NULL;
  }

}
