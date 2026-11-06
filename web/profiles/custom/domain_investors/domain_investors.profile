<?php

/**
 * @file
 * The Domain Investors installer: a few questions, then the recipes.
 *
 * After Drupal's own steps (database, site name, administrator), it applies
 * recipes/portfolio_base, asks about the store, payments, registrars and
 * marketplaces, applies the recipes the answers call for, and sets up the
 * store and the providers. Everything it sets up can be changed afterwards
 * in the admin pages, and every optional recipe can be applied later with
 * `drush recipe`; see docs/install.md.
 */

declare(strict_types=1);

use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Recipe\Recipe;
use Drupal\Core\Recipe\RecipeRunner;
use Drupal\domain_investors\Installer\Finisher;
use Drupal\domain_investors\Installer\SetupForm;
use Drupal\domain_investors_tools\DemoContent;

/**
 * Implements hook_install_tasks().
 *
 * @phpstan-param array<mixed> $install_state
 * @phpstan-return array<string, array<string, mixed>>
 */
function domain_investors_install_tasks(array &$install_state): array {
  // Installing from exported configuration (site:install --existing-config):
  // the configuration already holds what the recipes and answers set up.
  if (!empty($install_state['config_install_path'])) {
    return [];
  }
  return [
    'domain_investors_apply_base' => [
      'display_name' => t('Set up the portfolio'),
      'type' => 'batch',
    ],
    'domain_investors_setup_form' => [
      'display_name' => t('Store, payments and connections'),
      'type' => 'form',
      'function' => SetupForm::class,
    ],
    'domain_investors_apply_options' => [
      'display_name' => t('Add payments and connections'),
      'type' => 'batch',
    ],
    'domain_investors_finish' => [
      'display_name' => t('Finish the setup'),
    ],
    // Not listed among the steps, as most installs have none to add.
    'domain_investors_add_demo' => [
      'type' => 'batch',
    ],
  ];
}

/**
 * Install task: applies recipes/portfolio_base.
 *
 * @phpstan-param array<mixed> $install_state
 * @phpstan-return array<mixed>
 */
function domain_investors_apply_base(array &$install_state): array {
  return _domain_investors_recipe_batch(['portfolio_base'], t('Setting up the portfolio'));
}

/**
 * Install task: applies the optional recipes the answers asked for.
 *
 * @phpstan-param array<mixed> $install_state
 * @phpstan-return array<mixed>
 */
function domain_investors_apply_options(array &$install_state): array {
  $recipes = SetupForm::answers()['recipes'] ?? [];
  return $recipes ? _domain_investors_recipe_batch($recipes, t('Adding payments and connections')) : [];
}

/**
 * Install task: the store, currency, providers and payment gateways.
 *
 * @phpstan-param array<mixed> $install_state
 */
function domain_investors_finish(array &$install_state): void {
  \Drupal::classResolver(Finisher::class)->finish(SetupForm::answers());
}

/**
 * Install task: adds the demo domains, when asked for.
 *
 * After the store exists, which they are sold in.
 *
 * @phpstan-param array<mixed> $install_state
 * @phpstan-return array<mixed>
 */
function domain_investors_add_demo(array &$install_state): array {
  $extras = SetupForm::answers()['extras'] ?? [];
  // The last task: the answers are only needed while installing.
  \Drupal::state()->delete(SetupForm::STATE_KEY);
  if (!in_array('demo', $extras, TRUE)) {
    return [];
  }
  $batch = (new BatchBuilder())
    ->setTitle(t('Adding demo domains'))
    ->setInitMessage(t('Starting.'))
    ->setErrorMessage(t('The demo domains could not be added.'));
  foreach (\Drupal::service(DemoContent::class)->chunks() as $rows) {
    $batch->addOperation('_domain_investors_demo_import', [$rows]);
  }
  return $batch->toArray();
}

/**
 * Batch operation: adds one chunk of demo domains.
 *
 * @param \Drupal\commerce_domains_import\ImportRow[] $rows
 *   The rows.
 * @param array|\ArrayAccess $context
 *   The batch context.
 */
function _domain_investors_demo_import(array $rows, &$context): void {
  \Drupal::service(DemoContent::class)->import($rows);
}

/**
 * Builds a batch that applies recipes from the project's recipes directory.
 *
 * @param string[] $names
 *   The recipes' directory names.
 * @param \Stringable|string $title
 *   The batch title.
 *
 * @phpstan-return array<mixed>
 */
function _domain_investors_recipe_batch(array $names, \Stringable|string $title): array {
  $batch = (new BatchBuilder())
    ->setTitle($title)
    ->setInitMessage(t('Starting.'))
    ->setErrorMessage(t('A recipe could not be applied.'));
  $operations = [];
  foreach ($names as $name) {
    $recipe = Recipe::createFromDirectory(domain_investors_recipes_directory() . '/' . $name);
    foreach (RecipeRunner::toBatchOperations($recipe) as $operation) {
      // A recipe that another one includes is applied once.
      if (!in_array($operation, $operations, TRUE)) {
        $operations[] = $operation;
      }
    }
  }
  foreach ($operations as [$callable, $arguments]) {
    $batch->addOperation($callable, $arguments);
  }
  return $batch->toArray();
}

/**
 * Returns the project's recipes directory, beside the document root.
 */
function domain_investors_recipes_directory(): string {
  return dirname(DRUPAL_ROOT) . '/recipes';
}
