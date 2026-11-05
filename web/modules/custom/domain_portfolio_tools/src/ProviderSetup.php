<?php

declare(strict_types=1);

namespace Drupal\domain_portfolio_tools;

use Drupal\commerce_domains\Entity\ProviderInterface;
use Drupal\commerce_domains\ProviderPluginManager;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\key\KeyInterface;

/**
 * Adds registrars and marketplaces whose credentials come from the environment.
 *
 * A provider with an API plugin gets one key (Key module) per credential the
 * plugin reads, each with the Environment provider and the variable the
 * plugin names, such as DYNADOT_API_KEY. Neither the provider nor the keys
 * hold a value, so both can be exported with the rest of the configuration;
 * setting the variable is all that connects the API.
 */
final class ProviderSetup {

  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
    protected ProviderPluginManager $providerPluginManager,
    protected TypedConfigManagerInterface $typedConfigManager,
  ) {}

  /**
   * Adds a provider for an API plugin, with keys for its credentials.
   *
   * An existing provider of that ID is kept as it is, apart from keys it is
   * missing.
   *
   * @param string $plugin_id
   *   The provider plugin, such as 'dynadot' or 'afternic'. Also the
   *   provider's ID.
   * @param bool $status
   *   Whether a new provider starts enabled.
   *
   * @return \Drupal\commerce_domains\Entity\ProviderInterface
   *   The provider.
   */
  public function addPluginProvider(string $plugin_id, bool $status = TRUE): ProviderInterface {
    $definition = $this->providerPluginManager->getDefinition($plugin_id);
    /** @var \Drupal\Core\Config\Entity\ConfigEntityStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage('commerce_domains_provider');
    // Without settings.php overrides: this may save the provider, and an
    // override saved with it would end up in the configuration.
    /** @var \Drupal\commerce_domains\Entity\ProviderInterface|null $provider */
    $provider = $storage->loadOverrideFree($plugin_id);
    if (!$provider) {
      /** @var \Drupal\commerce_domains\Entity\ProviderInterface $provider */
      $provider = $storage->create([
        'id' => $plugin_id,
        'label' => (string) $definition['label'],
        'plugin' => $plugin_id,
        'status' => $status,
      ]);
      $provider->save();
    }
    if ($provider->getPluginId() !== $plugin_id) {
      return $provider;
    }

    $keys = (array) $provider->get('credential_keys');
    foreach ($provider->getPlugin()->getCredentialSources() as $setting => $source) {
      if (!empty($keys[$setting])) {
        continue;
      }
      $keys[$setting] = $this->environmentKey(
        $plugin_id . '_' . $setting,
        sprintf('%s: %s', $definition['label'], $this->settingLabel($plugin_id, $setting)),
        $source['variable'],
      )->id();
    }
    ksort($keys);
    if ($keys !== (array) $provider->get('credential_keys')) {
      $provider->set('credential_keys', $keys)->save();
    }
    return $provider;
  }

  /**
   * Adds a registrar without an API, such as Hover.
   *
   * @param string $name
   *   The registrar's name.
   *
   * @return \Drupal\commerce_domains\Entity\ProviderInterface
   *   The provider, new or the one of that name already there.
   */
  public function addRegistrar(string $name): ProviderInterface {
    return $this->addProviderWithoutApi($name, FALSE);
  }

  /**
   * Adds a marketplace without an API, where domains are listed by hand.
   *
   * Commerce Domains then counts domains listed there and records sales
   * reported from it, though nothing is pushed to it.
   *
   * @param string $name
   *   The marketplace's name.
   *
   * @return \Drupal\commerce_domains\Entity\ProviderInterface
   *   The provider, new or the one of that name already there, marked as a
   *   marketplace.
   */
  public function addMarketplace(string $name): ProviderInterface {
    return $this->addProviderWithoutApi($name, TRUE);
  }

  /**
   * Adds a provider without a plugin, or finds the one of that name.
   */
  protected function addProviderWithoutApi(string $name, bool $marketplace): ProviderInterface {
    $storage = $this->entityTypeManager->getStorage('commerce_domains_provider');
    foreach ($storage->loadMultiple() as $provider) {
      if (mb_strtolower((string) $provider->label()) === mb_strtolower($name)) {
        /** @var \Drupal\commerce_domains\Entity\ProviderInterface $provider */
        if ($marketplace && !$provider->isMarketplace()) {
          $provider->setMarketplace(TRUE)->save();
        }
        return $provider;
      }
    }
    $id = $base = trim(preg_replace('/[^a-z0-9]+/', '_', mb_strtolower($name)), '_') ?: 'registrar';
    for ($suffix = 2; $storage->load($id); $suffix++) {
      $id = $base . '_' . $suffix;
    }
    /** @var \Drupal\commerce_domains\Entity\ProviderInterface $provider */
    $provider = $storage->create([
      'id' => $id,
      'label' => $name,
      'plugin' => NULL,
      'marketplace' => $marketplace,
      'status' => TRUE,
    ]);
    $provider->save();
    return $provider;
  }

  /**
   * Lists the variables a provider plugin reads its credentials from.
   *
   * @return array<string, array{variable: string, required: bool}>
   *   Keyed by setting.
   */
  public function variables(string $plugin_id): array {
    $plugin = $this->providerPluginManager->createInstance($plugin_id);
    $variables = [];
    foreach ($plugin->getCredentialSources() as $setting => $source) {
      $variables[$setting] = [
        'variable' => $source['variable'],
        'required' => $source['required'],
      ];
    }
    return $variables;
  }

  /**
   * Returns a key that reads an environment variable, adding it if needed.
   */
  protected function environmentKey(string $id, string $label, string $variable): KeyInterface {
    $storage = $this->entityTypeManager->getStorage('key');
    /** @var \Drupal\key\KeyInterface|null $key */
    $key = $storage->load($id);
    if ($key) {
      return $key;
    }
    /** @var \Drupal\key\KeyInterface $key */
    $key = $storage->create([
      'id' => $id,
      'label' => $label,
      'description' => sprintf('Read from the %s environment variable. Never holds a value of its own.', $variable),
      'key_type' => 'authentication',
      'key_type_settings' => [],
      'key_provider' => 'env',
      'key_provider_settings' => [
        'env_variable' => $variable,
        'base64_encoded' => FALSE,
        'strip_line_breaks' => TRUE,
      ],
      'key_input' => 'none',
      'key_input_settings' => [],
    ]);
    $key->save();
    return $key;
  }

  /**
   * Returns a credential's label from the plugin's config schema.
   */
  protected function settingLabel(string $plugin_id, string $setting): string {
    $definition = $this->typedConfigManager->getDefinition('commerce_domains.provider_plugin.' . $plugin_id);
    $label = $definition['mapping'][$setting]['label'] ?? NULL;
    return $label ? (string) $label : str_replace('_', ' ', $setting);
  }

}
