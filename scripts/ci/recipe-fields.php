<?php

/**
 * @file
 * Checks the recipe's domain fields against Commerce Domains.
 *
 * Installing a recipe's product type does not add the fields of its entity
 * traits: portfolio_base carries a copy of them as configuration. When the
 * module adds a field or rewords one, the copy goes stale without any error.
 * This compares the two, and that each field is placed on the form and view
 * displays, so it is shown or hidden on purpose.
 *
 * Run with drush php:script on an installed site.
 */

declare(strict_types=1);

use Drupal\field\Entity\FieldConfig;

$entity_types = [
  'commerce_product' => 'commerce_product_type',
  'commerce_product_variation' => 'commerce_product_variation_type',
];
$traits = \Drupal::service('plugin.manager.commerce_entity_trait');
$repository = \Drupal::service('entity_display.repository');
$problems = [];
$checked = 0;

foreach ($entity_types as $entity_type => $bundle_type) {
  $type = \Drupal::entityTypeManager()->getStorage($bundle_type)->load('domain');
  $displays = [
    'form' => $repository->getFormDisplay($entity_type, 'domain'),
    'view' => $repository->getViewDisplay($entity_type, 'domain'),
  ];
  foreach ($type->getTraits() as $trait_id) {
    // Other modules' traits, like Escrow.com's, have fields of their own.
    if (!str_starts_with($traits->getDefinition($trait_id)['provider'], 'commerce_domains')) {
      continue;
    }
    foreach ($traits->createInstance($trait_id)->buildFieldDefinitions() as $name => $wanted) {
      $checked++;
      $where = "$entity_type.$name ($trait_id)";
      $field = FieldConfig::loadByName($entity_type, 'domain', $name);
      if (!$field) {
        $problems[] = "$where: not in the recipe";
        continue;
      }
      if ($field->getType() !== $wanted->getType()) {
        $problems[] = "$where: type is {$field->getType()}, the module has {$wanted->getType()}";
      }
      foreach (['getLabel' => 'label', 'getDescription' => 'description'] as $get => $property) {
        if ((string) $field->$get() !== (string) $wanted->$get()) {
          $problems[] = "$where: $property is \"{$field->$get()}\", the module has \"{$wanted->$get()}\"";
        }
      }
      if ($field->isRequired() !== $wanted->isRequired()) {
        $problems[] = "$where: required differs from the module";
      }
      // Only what the module sets: the rest are the field type's defaults.
      foreach ($wanted->getSettings() as $setting => $value) {
        if ($field->getSetting($setting) != $value) {
          $problems[] = "$where: setting $setting differs from the module";
        }
      }
      foreach ($displays as $kind => $display) {
        $hidden = array_key_exists($name, $display->toArray()['hidden'] ?? []);
        if (!$display->getComponent($name) && !$hidden) {
          $problems[] = "$where: not placed on the $kind display";
        }
      }
    }
  }
}

if ($problems) {
  throw new \RuntimeException(count($problems) . " differences from Commerce Domains:\n- " . implode("\n- ", $problems));
}
echo "$checked domain fields match Commerce Domains.\n";
