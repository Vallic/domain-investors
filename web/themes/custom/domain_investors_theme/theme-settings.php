<?php

/**
 * @file
 * The theme's settings: the site's template and colour scheme.
 */

declare(strict_types=1);

use Drupal\Core\Form\FormStateInterface;
use Drupal\domain_investors_theme\Appearance;
use Drupal\domain_investors_theme\Palette;

/**
 * Implements hook_form_FORM_ID_alter() for system_theme_settings.
 *
 * @phpstan-param array<mixed> $form
 */
function domain_investors_theme_form_system_theme_settings_alter(array &$form, FormStateInterface $form_state): void {
  $form['portfolio'] = [
    '#type' => 'details',
    '#title' => t('Template and colours'),
    '#open' => TRUE,
    '#weight' => -10,
    '#description' => t('How the portfolio and the domain pages look. Each domain can choose its own template and colour scheme on its edit form, Custom included.'),
  ];
  $form['portfolio']['template'] = [
    '#type' => 'radios',
    '#title' => t('Template'),
    '#options' => Appearance::templateOptions(TRUE),
    '#default_value' => Appearance::siteTemplate(),
  ];
  $form['portfolio']['scheme'] = [
    '#type' => 'radios',
    '#title' => t('Colour scheme'),
    '#options' => Appearance::schemeOptions(),
    '#default_value' => Appearance::siteScheme(),
  ];

  // The custom scheme: three colours asked for, the rest worked out from
  // them, each of those open to setting by hand.
  $saved = Appearance::customColours() + Palette::DEFAULTS;
  $resolved = Palette::resolve($saved);
  $form['portfolio']['custom'] = [
    '#type' => 'fieldset',
    '#title' => t('Custom colours'),
    '#description' => t('Your own scheme, for the site or for any domain that chooses Custom. The other colours are worked out from these three; text has to reach a contrast of 4.5:1 to save.'),
    '#tree' => TRUE,
  ];
  foreach (['surface', 'ink', 'accent'] as $token) {
    $form['portfolio']['custom'][$token] = [
      '#type' => 'color',
      // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
      '#title' => t(Palette::TOKENS[$token]),
      '#default_value' => $resolved[$token],
    ];
  }
  $form['portfolio']['custom']['more'] = [
    '#type' => 'details',
    '#title' => t('Every colour'),
    '#description' => t('Empty: worked out from the three above, as the placeholder shows.'),
    '#open' => (bool) array_diff_key(array_filter($saved), Palette::DEFAULTS),
  ];
  foreach (array_slice(Palette::TOKENS, 3, NULL, TRUE) as $token => $label) {
    $form['portfolio']['custom']['more'][$token] = [
      '#type' => 'textfield',
      // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
      '#title' => t($label),
      '#default_value' => $saved[$token] ?? '',
      '#placeholder' => Palette::resolve(array_intersect_key($saved, Palette::DEFAULTS))[$token],
      '#size' => 9,
      '#maxlength' => 7,
      '#parents' => ['custom', $token],
    ];
  }
  $form['portfolio']['custom']['preview'] = [
    '#type' => 'inline_template',
    '#template' => '<p>{{ label }}: {% for token, value in palette %}<span title="{{ token }} {{ value }}" style="display:inline-block;width:20px;height:20px;margin-right:4px;vertical-align:middle;border:1px solid #888;background:{{ value }}"></span>{% endfor %}</p>',
    '#context' => ['label' => t('Saved scheme'), 'palette' => $resolved],
    '#weight' => -1,
  ];
  $form['#validate'][] = 'domain_investors_theme_theme_settings_validate';
}

/**
 * Validates the custom colours, and checks the scheme reads well.
 *
 * @phpstan-param array<mixed> $form
 */
function domain_investors_theme_theme_settings_validate(array &$form, FormStateInterface $form_state): void {
  $custom = [];
  foreach (array_keys(Palette::TOKENS) as $token) {
    $value = strtolower(trim((string) $form_state->getValue(['custom', $token])));
    if ($value !== '' && !Palette::isHex($value)) {
      $form_state->setErrorByName('custom][' . $token, t('Enter colours as # and six hexadecimal digits, such as #0b6656.'));
    }
    $custom[$token] = $value;
  }
  // Only what was set: the worked-out colours follow the three when they
  // change.
  $form_state->setValue('custom', array_filter($custom));

  if ($form_state->getErrors()) {
    return;
  }
  // A scheme that fails contrast is refused when the site uses it, and a
  // warning otherwise, as a domain may.
  foreach (Palette::problems(Palette::resolve($custom)) as $problem) {
    $message = t('@pair reaches @ratio:1, under the @needed:1 that text needs. Change one of the two colours.', [
      // phpcs:ignore Drupal.Semantics.FunctionT.NotLiteralString
      '@pair' => t($problem['label']),
      '@ratio' => $problem['ratio'],
      '@needed' => $problem['needed'],
    ]);
    // The field to mark: the pair's foreground, unless it is worked out and
    // the background was asked for.
    [$fore, $back] = $problem['tokens'];
    $asked = ['surface', 'ink', 'accent'];
    $name = 'custom][' . (($custom[$fore] ?? '') !== '' || !in_array($back, $asked, TRUE) ? $fore : $back);
    if ($form_state->getValue('scheme') === 'custom') {
      $form_state->setErrorByName($name, $message);
    }
    else {
      \Drupal::messenger()->addWarning($message);
    }
  }
}
